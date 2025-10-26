<?php

declare(strict_types=1);

namespace Tests\Feature\Frontend;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\Cart\CartService;
use App\Support\ErrorCodes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class CheckoutJsonResponseTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_returns_problem_when_cart_is_empty(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->withHeaders([
            'Idempotency-Key' => (string) Str::uuid(),
        ])->postJson(route('frontend.checkout.process'), [
            'payment_method' => 'card',
            'confirm'        => true,
            'totals'         => $this->zeroTotals(),
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('error.code', ErrorCodes::CHECKOUT_CART_EMPTY)
            ->assertJsonPath('detail', __('errors.messages.checkout_empty'));
    }

    public function test_checkout_falls_back_to_user_owned_cart_items(): void
    {
        $user = User::factory()->create();

        CartItem::factory()
            ->forUser($user)
            ->state([
                'session_id'  => 'legacy-session',
                'quantity'    => 1,
                'unit_price'  => 49.99,
                'price'       => 49.99,
                'total_price' => 49.99,
            ])
            ->create();

        $this->actingAs($user);

        $totals = $this->buildTotals($user);

        $response = $this->withHeaders([
            'Idempotency-Key' => (string) Str::uuid(),
        ])->postJson(route('frontend.checkout.process'), [
            'payment_method' => 'card',
            'confirm'        => true,
            'totals'         => $totals,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.id', fn ($value) => is_int($value) && $value > 0);
    }

    public function test_double_submit_replays_original_order(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'price'          => 25.0,
            'manage_stock'   => true,
            'stock_quantity' => 5,
        ]);

        CartItem::factory()
            ->forUser($user)
            ->forProduct($product)
            ->state([
                'quantity'    => 2,
                'unit_price'  => 25.0,
                'price'       => 25.0,
                'total_price' => 50.0,
            ])
            ->create();

        $this->actingAs($user);

        $totals = $this->buildTotals($user);
        $idempotencyKey = (string) Str::uuid();

        $first = $this->withHeaders(['Idempotency-Key' => $idempotencyKey])
            ->postJson(route('frontend.checkout.process'), [
                'payment_method' => 'card',
                'confirm'        => true,
                'totals'         => $totals,
            ]);

        $first->assertCreated();
        $orderId = $first->json('data.id');

        $second = $this->withHeaders(['Idempotency-Key' => $idempotencyKey])
            ->postJson(route('frontend.checkout.process'), [
                'payment_method' => 'card',
                'confirm'        => true,
                'totals'         => $totals,
            ]);

        $second->assertCreated()->assertJsonPath('data.id', $orderId);
        $this->assertSame(1, Order::count());
    }

    public function test_mismatched_totals_return_conflict(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'price'          => 40.0,
            'manage_stock'   => true,
            'stock_quantity' => 10,
        ]);

        CartItem::factory()
            ->forUser($user)
            ->forProduct($product)
            ->state([
                'quantity'    => 1,
                'unit_price'  => 40.0,
                'price'       => 40.0,
                'total_price' => 40.0,
            ])
            ->create();

        $this->actingAs($user);

        $totals = $this->buildTotals($user);
        $totals['subtotal'] = $totals['subtotal'] + 10.0;

        $response = $this->withHeaders([
            'Idempotency-Key' => (string) Str::uuid(),
        ])->postJson(route('frontend.checkout.process'), [
            'payment_method' => 'card',
            'confirm'        => true,
            'totals'         => $totals,
        ]);

        $response->assertStatus(409)
            ->assertJsonPath('reason', 'totals_mismatch')
            ->assertJsonStructure(['corrected_totals' => ['subtotal', 'tax', 'shipping', 'discount', 'total', 'lines']]);
    }

    public function test_stock_change_returns_conflict(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'price'          => 30.0,
            'manage_stock'   => true,
            'stock_quantity' => 0,
        ]);

        CartItem::factory()
            ->forUser($user)
            ->forProduct($product)
            ->state([
                'quantity'    => 1,
                'unit_price'  => 30.0,
                'price'       => 30.0,
                'total_price' => 30.0,
            ])
            ->create();

        $this->actingAs($user);

        $totals = $this->buildTotals($user);

        $response = $this->withHeaders([
            'Idempotency-Key' => (string) Str::uuid(),
        ])->postJson(route('frontend.checkout.process'), [
            'payment_method' => 'card',
            'confirm'        => true,
            'totals'         => $totals,
        ]);

        $response->assertStatus(409)
            ->assertJsonPath('reason', 'stock_unavailable');
    }

    /**
     * @return array{subtotal:float,tax:float,shipping:float,discount:float,total:float,lines:array<int,float>}
     */
    private function buildTotals(User $user): array
    {
        /** @var CartService $service */
        $service = app(CartService::class);
        $summary = $service->getSummary($user->id, session()->getId());

        return [
            'subtotal' => (float) $summary['subtotal'],
            'tax'      => (float) $summary['tax'],
            'shipping' => (float) $summary['shipping'],
            'discount' => (float) $summary['discount'],
            'total'    => (float) $summary['total'],
            'lines'    => array_map(static fn ($item) => (float) ($item['total'] ?? 0.0), $summary['items']),
        ];
    }

    /**
     * Provide a zeroed totals payload for error scenarios.
     *
     * @return array{subtotal:float,tax:float,shipping:float,discount:float,total:float,lines:array<int,float>}
     */
    private function zeroTotals(): array
    {
        return [
            'subtotal' => 0.0,
            'tax'      => 0.0,
            'shipping' => 0.0,
            'discount' => 0.0,
            'total'    => 0.0,
            'lines'    => [],
        ];
    }
}
