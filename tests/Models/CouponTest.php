<?php

declare(strict_types=1);

namespace Tests\Models;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\CustomerGroup;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

final class CouponTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Freeze time so date-based scope assertions remain deterministic across environments.
        Carbon::setTestNow(Carbon::create(2024, 1, 1, 12));
    }

    protected function tearDown(): void
    {
        // Release the custom clock after each test case to avoid leaking state.
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_coupon_has_expected_fillable_attributes(): void
    {
        // Validate that the most critical attributes can be mass assigned via fillable.
        $coupon = new Coupon;

        $this->assertContains('code', $coupon->getFillable());
        $this->assertContains('name', $coupon->getFillable());
        $this->assertContains('type', $coupon->getFillable());
        $this->assertContains('value', $coupon->getFillable());
        $this->assertContains('starts_at', $coupon->getFillable());
        $this->assertContains('expires_at', $coupon->getFillable());
    }

    public function test_coupon_casts_attributes_correctly(): void
    {
        // Create a coupon with explicit attribute values so casting assertions are meaningful.
        $coupon = Coupon::factory()->create([
            'value'                 => '15.50',
            'minimum_amount'        => '100.00',
            'maximum_discount'      => '25.00',
            'usage_limit'           => '5',
            'usage_limit_per_user'  => '2',
            'used_count'            => '1',
            'is_active'             => true,
            'is_public'             => false,
            'is_auto_apply'         => true,
            'is_stackable'          => false,
            'is_first_time_only'    => true,
            'starts_at'             => Carbon::now()->subDay(),
            'expires_at'            => Carbon::now()->addDays(10),
            'applicable_products'   => ['123'],
            'applicable_categories' => ['456'],
        ]);

        $coupon->refresh();

        // decimal:2 casts return strings in Laravel 12.
        $this->assertSame('15.50', $coupon->value);
        $this->assertSame('100.00', $coupon->minimum_amount);
        $this->assertSame('25.00', $coupon->maximum_discount);
        $this->assertSame(5, $coupon->usage_limit);
        $this->assertSame(2, $coupon->usage_limit_per_user);
        $this->assertSame(1, $coupon->used_count);
        $this->assertTrue($coupon->is_active);
        $this->assertFalse($coupon->is_public);
        $this->assertTrue($coupon->is_auto_apply);
        $this->assertFalse($coupon->is_stackable);
        $this->assertTrue($coupon->is_first_time_only);
        $this->assertInstanceOf(Carbon::class, $coupon->starts_at);
        $this->assertInstanceOf(Carbon::class, $coupon->expires_at);
        $this->assertSame(['123'], $coupon->applicable_products);
        $this->assertSame(['456'], $coupon->applicable_categories);
    }

    public function test_coupon_relationships_resolve_models(): void
    {
        // Persist related records to confirm each defined relationship returns the expected models.
        $group = CustomerGroup::factory()->create();
        $coupon = Coupon::factory()->create([
            'customer_group_id' => $group->id,
            'starts_at'         => Carbon::now()->subDay(),
            'expires_at'        => Carbon::now()->addDay(),
        ]);

        $product = Product::factory()->create();
        $category = Category::factory()->create();

        $coupon->products()->attach($product->id);
        $coupon->categories()->attach($category->id);

        $order = Order::factory()->create([
            'coupon_id' => $coupon->id,
            'status'    => 'completed',
        ]);

        $user = User::factory()->create();
        $usage = CouponUsage::factory()->create([
            'coupon_id'       => $coupon->id,
            'order_id'        => $order->id,
            'user_id'         => $user->id,
            'discount_amount' => 10.0,
        ]);

        $coupon->refresh();

        $this->assertTrue($coupon->products->contains($product));
        $this->assertTrue($coupon->categories->contains($category));
        $this->assertTrue($coupon->orders->contains($order));
        $this->assertTrue($coupon->usages->contains($usage));
        $customerGroup = $coupon->customerGroup;
        $this->assertInstanceOf(CustomerGroup::class, $customerGroup);
        $this->assertTrue($customerGroup->is($group));
    }

    public function test_scope_active_filters_inactive_records(): void
    {
        // Build both active and inactive coupons to ensure the scope honours the flag.
        $activeCoupon = Coupon::factory()->create(['is_active' => true]);
        Coupon::factory()->create(['is_active' => false]);

        $scoped = Coupon::withoutGlobalScopes()->active()->pluck('id')->all();

        $this->assertSame([$activeCoupon->id], $scoped);
    }

    public function test_scope_valid_excludes_unusable_coupons(): void
    {
        // Compose a mixture of coupons that fail different validity checks.
        $validCoupon = Coupon::factory()->create([
            'is_active'   => true,
            'starts_at'   => Carbon::now()->subDay(),
            'expires_at'  => Carbon::now()->addDays(5),
            'usage_limit' => 10,
            'used_count'  => 3,
        ]);

        Coupon::factory()->create([
            'is_active' => true,
            'starts_at' => Carbon::now()->addDay(),
        ]);

        Coupon::factory()->create([
            'is_active'  => true,
            'expires_at' => Carbon::now()->subDay(),
        ]);

        Coupon::factory()->create([
            'is_active'   => true,
            'usage_limit' => 5,
            'used_count'  => 5,
        ]);

        Coupon::factory()->create([
            'is_active' => false,
        ]);

        $scoped = Coupon::withoutGlobalScopes()->valid()->pluck('id')->all();

        $this->assertSame([$validCoupon->id], $scoped);
    }

    public function test_scope_ordered_by_name_sorts_alphabetically(): void
    {
        // Create coupons out of order and confirm the helper enforces alphabetical sorting.
        Coupon::factory()->create(['name' => 'Gamma Coupon']);
        Coupon::factory()->create(['name' => 'Alpha Coupon']);
        Coupon::factory()->create(['name' => 'Beta Coupon']);

        $names = Coupon::withoutGlobalScopes()->orderedByName()->pluck('name')->values();

        $this->assertSame(['Alpha Coupon', 'Beta Coupon', 'Gamma Coupon'], $names->all());
    }

    public function test_calculate_discount_respects_caps_and_types(): void
    {
        // Percentage coupon should honour the maximum discount ceiling.
        $percentageCoupon = Coupon::factory()->create([
            'type'             => 'percentage',
            'value'            => 20,
            'maximum_discount' => '15.00',
            'minimum_amount'   => '10.00',
            'usage_limit'      => 100,
            'used_count'       => 0,
            'starts_at'        => Carbon::now()->subDay(),
            'expires_at'       => Carbon::now()->addDays(30),
        ]);

        $this->assertSame(15.0, $percentageCoupon->calculateDiscount(200));

        // Fixed coupon should never exceed the order total.
        $fixedCoupon = Coupon::factory()->create([
            'type'             => 'fixed',
            'value'            => 50,
            'maximum_discount' => null,
            'minimum_amount'   => null,
            'usage_limit'      => 100,
            'used_count'       => 0,
            'starts_at'        => Carbon::now()->subDay(),
            'expires_at'       => Carbon::now()->addDays(30),
        ]);

        $this->assertSame(40.0, $fixedCoupon->calculateDiscount(40));
    }

    public function test_remaining_uses_attribute_handles_null_limits(): void
    {
        // Unlimited coupons should surface null while limited ones return a non-negative remainder.
        $unlimited = Coupon::factory()->create([
            'usage_limit' => null,
            'used_count'  => 0,
        ]);

        $limited = Coupon::factory()->create([
            'usage_limit' => 10,
            'used_count'  => 4,
        ]);

        $this->assertNull($unlimited->remaining_uses);
        $this->assertSame(6, $limited->remaining_uses);
    }

    public function test_can_be_used_enforces_minimum_amount(): void
    {
        // Ensure the minimum amount restriction is respected when validating redemption eligibility.
        $coupon = Coupon::factory()->create([
            'minimum_amount' => '50.00',
            'usage_limit'    => 100,
            'used_count'     => 0,
            'starts_at'      => Carbon::now()->subDay(),
            'expires_at'     => Carbon::now()->addDays(10),
        ]);

        $this->assertFalse($coupon->canBeUsed(40));
        $this->assertTrue($coupon->canBeUsed(60));
    }

    public function test_is_valid_checks_usage_limits(): void
    {
        // Validate that coupons exceeding their usage limit are treated as invalid.
        $coupon = Coupon::factory()->create([
            'usage_limit' => 5,
            'used_count'  => 5,
            'starts_at'   => Carbon::now()->subDay(),
            'expires_at'  => Carbon::now()->addDay(),
        ]);

        $this->assertFalse($coupon->isValid());

        $coupon->used_count = 4;

        $this->assertTrue($coupon->isValid());
    }
}
