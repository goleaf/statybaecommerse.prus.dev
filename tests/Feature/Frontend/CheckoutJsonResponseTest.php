<?php

declare(strict_types=1);

namespace Tests\Feature\Frontend;

use App\Models\CartItem;
use App\Models\User;
use App\Support\ErrorCodes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CheckoutJsonResponseTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_returns_problem_when_cart_is_empty(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->postJson(route('frontend.checkout.process'), [
            'payment_method' => 'card',
            'confirm'        => true,
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

        $response = $this->postJson(route('frontend.checkout.process'), [
            'payment_method' => 'card',
            'confirm'        => true,
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('order.id', fn ($value) => is_int($value) && $value > 0);
    }
}
