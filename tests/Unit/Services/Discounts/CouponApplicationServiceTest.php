<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Discounts;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\CustomerGroup;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\Discounts\CouponApplicationService;
use App\Services\Discounts\DiscountEngine;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Session\Session;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class CouponApplicationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure session interactions in the service have a backing session driver.
        $this->startSession();
    }

    public function test_get_available_coupons_respects_segment_and_product_filters(): void
    {
        $user = User::factory()->create();
        $group = CustomerGroup::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create();
        $product->categories()->attach($category->id);

        $coupon = Coupon::factory()->create([
            'is_public'             => true,
            'customer_group_id'     => $group->id,
            'applicable_products'   => [$product->id],
            'applicable_categories' => [$category->id],
        ]);

        $context = [
            'user_id'   => $user->id,
            'group_ids' => [$group->id],
            'cart'      => [
                'subtotal' => 150.00,
                'items'    => [
                    ['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 150.00],
                ],
            ],
        ];

        $service = $this->resolveService();
        $available = $service->getAvailableCoupons($context);

        $this->assertCount(1, $available);
        $this->assertSame($coupon->code, Arr::get($available[0], 'code'));

        // When the shopper lacks the required customer group the coupon should disappear.
        $context['group_ids'] = [];
        $this->assertSame([], $service->getAvailableCoupons($context));
    }

    public function test_apply_blocks_coupon_when_first_time_requirement_not_met(): void
    {
        $user = User::factory()->create();
        Order::factory()->create(['user_id' => $user->id]);

        $coupon = Coupon::factory()->create([
            'is_public'           => true,
            'is_first_time_only'  => true,
            'usage_limit_per_user'=> null,
        ]);

        $context = [
            'user_id' => $user->id,
            'cart'    => [
                'subtotal' => 120.00,
                'items'    => [
                    ['product_id' => null, 'quantity' => 1, 'unit_price' => 120.00],
                ],
            ],
        ];

        $service = $this->resolveService();
        $result = $service->apply($coupon->code, $context);

        $this->assertFalse($result['success']);
        $this->assertSame('This coupon is only available for your first order.', $result['message']);
    }

    public function test_apply_blocks_coupon_when_personal_limit_reached(): void
    {
        $user = User::factory()->create();
        $coupon = Coupon::factory()->create([
            'is_public'            => true,
            'usage_limit_per_user' => 1,
        ]);

        CouponUsage::factory()->create([
            'coupon_id' => $coupon->id,
            'user_id'   => $user->id,
        ]);

        $context = [
            'user_id' => $user->id,
            'cart'    => [
                'subtotal' => 90.00,
                'items'    => [
                    ['product_id' => null, 'quantity' => 1, 'unit_price' => 90.00],
                ],
            ],
        ];

        $service = $this->resolveService();
        $result = $service->apply($coupon->code, $context);

        $this->assertFalse($result['success']);
        $this->assertSame('You have already used this coupon the maximum number of times.', $result['message']);
    }

    public function test_apply_best_auto_coupon_uses_first_auto_eligible_coupon(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create();
        $product->categories()->attach($category->id);

        Coupon::factory()->create([
            'code'                  => 'MANUALONLY',
            'is_public'             => true,
            'is_auto_apply'         => false,
            'applicable_products'   => [$product->id],
            'applicable_categories' => [$category->id],
            'usage_limit_per_user'  => null,
        ]);

        Coupon::factory()->create([
            'code'                  => 'AUTOAPPLY',
            'is_public'             => true,
            'is_auto_apply'         => true,
            'applicable_products'   => [$product->id],
            'applicable_categories' => [$category->id],
            'usage_limit_per_user'  => null,
        ]);

        $context = [
            'user_id' => null,
            'cart'    => [
                'subtotal' => 75.00,
                'items'    => [
                    ['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 75.00],
                ],
            ],
        ];

        $service = $this->resolveService();
        $result = $service->applyBestAutoCoupon($context);

        $this->assertTrue($result['success']);
        $this->assertSame('AUTOAPPLY', Arr::get($result, 'coupon.code'));
    }

    /**
     * Build the service with a mocked discount engine to avoid unrelated database lookups.
     */
    private function resolveService(?MockInterface $engine = null): CouponApplicationService
    {
        $engine ??= Mockery::mock(DiscountEngine::class);
        $engine->shouldReceive('evaluate')->andReturn(['discount_total_amount' => 0.0]);

        /** @var Session $session */
        $session = $this->app->make('session.store');

        return new CouponApplicationService(
            $engine,
            $session,
            $this->app->make(Dispatcher::class),
        );
    }
}
