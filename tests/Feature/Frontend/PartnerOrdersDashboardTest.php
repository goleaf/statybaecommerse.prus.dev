<?php

declare(strict_types=1);

namespace Tests\Feature\Frontend;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PartnerOrdersDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_partner_user_can_view_orders(): void
    {
        // Create a partner and attach it to a user to simulate a real partner dashboard session.
        $partner = Partner::factory()->create();
        $user = User::factory()->create();
        $user->partners()->attach($partner->getKey());

        // Seed a couple of orders tied to the partner so the table has meaningful rows to render.
        $order = Order::factory()
            ->pending()
            ->for($partner, 'partner')
            ->create([
                'status' => OrderStatus::PENDING->value,
            ]);
        OrderItem::factory()->forOrder($order)->create();

        $response = $this->actingAs($user)->get(route('frontend.partner.orders.index'));

        $response->assertOk();
        $response->assertSee($order->number);
        $response->assertSee($partner->name);
        $response->assertSee(__('partners.dashboard.tabs.open'));
    }

    public function test_dashboard_filters_by_status_segment(): void
    {
        // Prepare a partner user with both open and shipped orders so the filter can be exercised.
        $partner = Partner::factory()->create();
        $user = User::factory()->create();
        $user->partners()->attach($partner->getKey());

        $openOrder = Order::factory()
            ->pending()
            ->for($partner, 'partner')
            ->create([
                'status' => OrderStatus::PENDING->value,
            ]);
        $shippedOrder = Order::factory()
            ->shipped()
            ->for($partner, 'partner')
            ->create([
                'status' => OrderStatus::SHIPPED->value,
            ]);

        $response = $this->actingAs($user)->get(route('frontend.partner.orders.index', ['status' => 'shipped']));

        $response->assertOk();
        $response->assertSee($shippedOrder->number);
        $response->assertDontSee($openOrder->number);
    }

    public function test_non_partner_receives_forbidden_state(): void
    {
        // A regular authenticated user without partner membership should see the guarded empty-state.
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('frontend.partner.orders.index'));

        $response->assertStatus(403);
        $response->assertSee(__('partners.dashboard.errors.forbidden.title'));
    }
}
