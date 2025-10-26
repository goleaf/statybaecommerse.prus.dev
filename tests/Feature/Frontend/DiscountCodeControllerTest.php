<?php

declare(strict_types=1);

namespace Tests\Feature\Frontend;

use App\Models\Discount;
use App\Models\DiscountCode;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class DiscountCodeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_rejects_expired_discount_code_with_422(): void
    {
        // Authenticate a user so per-customer checks operate on a real identity.
        $this->actingAs(User::factory()->create());

        $discount = $this->makeDiscount();
        $code = $this->makeCode($discount, [
            'code'       => 'PAST10',
            'expires_at' => now()->subDay(),
        ]);
        $payload = $this->buildCartPayload();

        $response = $this->postJson(route('frontend.discount-codes.apply'), [
            'code'     => $code->code,
            'cart'     => $payload,
            'shipping' => ['base_amount' => 0],
        ]);

        $response->assertStatus(422);
        $response->assertJson(['reason' => 'expired']);
    }

    public function test_it_rejects_when_usage_limit_exceeded_with_409(): void
    {
        $this->actingAs(User::factory()->create());

        $discount = $this->makeDiscount(['usage_limit' => 1, 'usage_count' => 1]);
        $code = $this->makeCode($discount, [
            'code'        => 'LIMITED',
            'usage_limit' => 1,
            'usage_count' => 1,
        ]);
        $payload = $this->buildCartPayload();

        $response = $this->postJson(route('frontend.discount-codes.apply'), [
            'code'     => $code->code,
            'cart'     => $payload,
            'shipping' => ['base_amount' => 0],
        ]);

        $response->assertStatus(409);
        $response->assertJson(['reason' => 'usage_limit']);
    }

    public function test_it_prevents_stacking_non_stackable_codes(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $product = Product::factory()->create();
        $cartPayload = $this->buildCartPayload($product);

        $discountA = $this->makeDiscount(['code' => 'STACKA']);
        $codeA = $this->makeCode($discountA, [
            'code'         => 'STACKA',
            'is_stackable' => false,
        ]);
        $responseA = $this->postJson(route('frontend.discount-codes.apply'), [
            'code'     => $codeA->code,
            'cart'     => $cartPayload,
            'shipping' => ['base_amount' => 0],
        ]);
        $responseA->assertStatus(200);

        $discountB = $this->makeDiscount(['code' => 'STACKB']);
        $codeB = $this->makeCode($discountB, [
            'code'         => 'STACKB',
            'is_stackable' => false,
        ]);
        $responseB = $this->postJson(route('frontend.discount-codes.apply'), [
            'code'     => $codeB->code,
            'cart'     => $cartPayload,
            'shipping' => ['base_amount' => 0],
        ]);

        $responseB->assertStatus(409);
        $responseB->assertJson(['reason' => 'stacking']);
    }

    private function makeDiscount(array $overrides = []): Discount
    {
        // Create a baseline active discount that can be safely applied during tests.
        return Discount::factory()->create(array_merge([
            'type'               => 'percentage',
            'value'              => 10,
            'minimum_amount'     => null,
            'usage_limit'        => null,
            'usage_count'        => 0,
            'per_customer_limit' => null,
            'starts_at'          => now()->subDay(),
            'ends_at'            => now()->addDays(7),
            'status'             => 'active',
            'is_active'          => true,
            'is_enabled'         => true,
        ], $overrides));
    }

    private function makeCode(Discount $discount, array $overrides = []): DiscountCode
    {
        // Ensure every generated code matches the permissive baseline unless a test overrides it explicitly.
        return DiscountCode::factory()->create(array_merge([
            'discount_id'          => $discount->getKey(),
            'code'                 => 'HONEST10',
            'type'                 => 'percentage',
            'value'                => 10,
            'minimum_amount'       => 0,
            'usage_limit'          => null,
            'usage_limit_per_user' => null,
            'usage_count'          => 0,
            'starts_at'            => now()->subDay(),
            'expires_at'           => now()->addDays(7),
            'status'               => 'active',
            'is_active'            => true,
            'is_stackable'         => false,
            'customer_group_id'    => null,
        ], $overrides));
    }

    private function buildCartPayload(?Product $product = null): array
    {
        // Assemble a minimal cart snapshot that exercises subtotal-driven discounts.
        $product ??= Product::factory()->create();

        return [
            'subtotal' => 100.0,
            'items'    => [[
                'product_id' => $product->getKey(),
                'quantity'   => 1,
                'unit_price' => 100.0,
            ]],
        ];
    }
}
