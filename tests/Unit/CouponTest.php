<?php declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Coupon;
use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponTest extends TestCase
{
    use RefreshDatabase;

    public function test_coupon_can_be_created(): void
    {
        $coupon = Coupon::factory()->create([
            'code' => 'SAVE20',
            'name' => '20% Off Coupon',
            'type' => 'percentage',
            'value' => 20.00,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('coupons', [
            'code' => 'SAVE20',
            'name' => '20% Off Coupon',
            'type' => 'percentage',
            'value' => 20.00,
            'is_active' => true,
        ]);
    }

    public function test_coupon_casts_work_correctly(): void
    {
        $coupon = Coupon::factory()->create([
            'value' => 15.50,
            'is_active' => true,
            'starts_at' => now(),
            'expires_at' => now()->addDays(30),
        ]);

        $this->assertIsNumeric($coupon->value);
        $this->assertIsBool($coupon->is_active);
        $this->assertInstanceOf(\Carbon\Carbon::class, $coupon->starts_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $coupon->expires_at);
    }

    public function test_coupon_fillable_attributes(): void
    {
        $coupon = new Coupon();
        $fillable = $coupon->getFillable();

        $this->assertContains('code', $fillable);
        $this->assertContains('name', $fillable);
        $this->assertContains('type', $fillable);
        $this->assertContains('value', $fillable);
        $this->assertContains('is_active', $fillable);
    }

    public function test_coupon_scope_active(): void
    {
        $activeCoupon = Coupon::factory()->create(['is_active' => true]);
        $inactiveCoupon = Coupon::factory()->create(['is_active' => false]);

        $activeCoupons = Coupon::active()->get();

        $this->assertTrue($activeCoupons->contains($activeCoupon));
        $this->assertFalse($activeCoupons->contains($inactiveCoupon));
    }


    public function test_coupon_scope_valid(): void
    {
        $validCoupon = Coupon::factory()->create([
            'is_active' => true,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
        ]);

        $expiredCoupon = Coupon::factory()->create([
            'is_active' => true,
            'starts_at' => now()->subDays(2),
            'ends_at' => now()->subDay(),
        ]);

        $futureCoupon = Coupon::factory()->create([
            'is_active' => true,
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDays(2),
        ]);

        $validCoupons = Coupon::valid()->get();

        $this->assertTrue($validCoupons->contains($validCoupon));
        $this->assertFalse($validCoupons->contains($expiredCoupon));
        $this->assertFalse($validCoupons->contains($futureCoupon));
    }

    public function test_coupon_scope_by_code(): void
    {
        $coupon1 = Coupon::factory()->create(['code' => 'SAVE20']);
        $coupon2 = Coupon::factory()->create(['code' => 'SAVE10']);

        $save20Coupons = Coupon::byCode('SAVE20')->get();

        $this->assertTrue($save20Coupons->contains($coupon1));
        $this->assertFalse($save20Coupons->contains($coupon2));
    }

    public function test_coupon_scope_by_type(): void
    {
        $percentageCoupon = Coupon::factory()->create(['type' => 'percentage']);
        $fixedCoupon = Coupon::factory()->create(['type' => 'fixed']);

        $percentageCoupons = Coupon::byType('percentage')->get();

        $this->assertTrue($percentageCoupons->contains($percentageCoupon));
        $this->assertFalse($percentageCoupons->contains($fixedCoupon));
    }

    public function test_coupon_can_have_usage_limit(): void
    {
        $coupon = Coupon::factory()->create([
            'usage_limit' => 100,
            'usage_count' => 50,
        ]);

        $this->assertEquals(100, $coupon->usage_limit);
        $this->assertEquals(50, $coupon->usage_count);
        $this->assertTrue($coupon->hasUsageLimit());
        $this->assertTrue($coupon->canBeUsed());
    }

    public function test_coupon_can_have_user_limit(): void
    {
        $coupon = Coupon::factory()->create([
            'user_limit' => 1,
        ]);

        $this->assertEquals(1, $coupon->user_limit);
    }

    public function test_coupon_can_have_minimum_amount(): void
    {
        $coupon = Coupon::factory()->create([
            'minimum_amount' => 50.00,
        ]);

        $this->assertEquals(50.00, $coupon->minimum_amount);
    }

    public function test_coupon_can_have_maximum_discount(): void
    {
        $coupon = Coupon::factory()->create([
            'maximum_discount' => 25.00,
        ]);

        $this->assertEquals(25.00, $coupon->maximum_discount);
    }

    public function test_coupon_can_have_products(): void
    {
        $coupon = Coupon::factory()->create();
        $products = Product::factory()->count(3)->create();

        $coupon->products()->attach($products->pluck('id'));

        $this->assertCount(3, $coupon->products);
        $this->assertInstanceOf(Product::class, $coupon->products->first());
    }

    public function test_coupon_can_have_users(): void
    {
        $coupon = Coupon::factory()->create();
        $users = User::factory()->count(2)->create();

        $coupon->users()->attach($users->pluck('id'));

        $this->assertCount(2, $coupon->users);
        $this->assertInstanceOf(User::class, $coupon->users->first());
    }

    public function test_coupon_can_calculate_discount(): void
    {
        $percentageCoupon = Coupon::factory()->create([
            'type' => 'percentage',
            'value' => 20.00,
        ]);

        $fixedCoupon = Coupon::factory()->create([
            'type' => 'fixed',
            'value' => 15.00,
        ]);

        $this->assertEquals(20.00, $percentageCoupon->calculateDiscount(100.00));
        $this->assertEquals(15.00, $fixedCoupon->calculateDiscount(100.00));
    }

    public function test_coupon_can_check_validity(): void
    {
        $validCoupon = Coupon::factory()->create([
            'is_active' => true,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
        ]);

        $invalidCoupon = Coupon::factory()->create([
            'is_active' => false,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
        ]);

        $this->assertTrue($validCoupon->isValid());
        $this->assertFalse($invalidCoupon->isValid());
    }

    public function test_coupon_can_check_expiration(): void
    {
        $expiredCoupon = Coupon::factory()->create([
            'ends_at' => now()->subDay(),
        ]);

        $activeCoupon = Coupon::factory()->create([
            'ends_at' => now()->addDay(),
        ]);

        $this->assertTrue($expiredCoupon->isExpired());
        $this->assertFalse($activeCoupon->isExpired());
    }

    public function test_coupon_can_check_if_not_started(): void
    {
        $futureCoupon = Coupon::factory()->create([
            'starts_at' => now()->addDay(),
        ]);

        $activeCoupon = Coupon::factory()->create([
            'starts_at' => now()->subDay(),
        ]);

        $this->assertTrue($futureCoupon->isNotStarted());
        $this->assertFalse($activeCoupon->isNotStarted());
    }

    public function test_coupon_can_have_description(): void
    {
        $coupon = Coupon::factory()->create([
            'description' => 'Save 20% on your next purchase',
        ]);

        $this->assertEquals('Save 20% on your next purchase', $coupon->description);
    }

    public function test_coupon_can_have_terms_and_conditions(): void
    {
        $coupon = Coupon::factory()->create([
            'terms_and_conditions' => 'Valid for new customers only. Cannot be combined with other offers.',
        ]);

        $this->assertEquals('Valid for new customers only. Cannot be combined with other offers.', $coupon->terms_and_conditions);
    }

    public function test_coupon_can_have_metadata(): void
    {
        $coupon = Coupon::factory()->create([
            'metadata' => [
                'campaign' => 'summer_sale',
                'source' => 'email',
                'priority' => 'high',
            ],
        ]);

        $this->assertIsArray($coupon->metadata);
        $this->assertEquals('summer_sale', $coupon->metadata['campaign']);
        $this->assertEquals('email', $coupon->metadata['source']);
        $this->assertEquals('high', $coupon->metadata['priority']);
    }
}
