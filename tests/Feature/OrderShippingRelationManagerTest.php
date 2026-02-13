<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\OrderResource\Pages\EditOrder;
use App\Filament\Resources\OrderResource\RelationManagers\OrderShippingRelationManager;
use App\Models\Order;
use App\Models\OrderShipping;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * OrderShippingRelationManagerTest
 *
 * Comprehensive test suite for OrderShippingRelationManager with Filament v4 compatibility
 */
final class OrderShippingRelationManagerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->order = Order::factory()->create(['user_id' => $this->user->id]);
    }

    #[Test]
    public function it_can_render_order_shipping_relation_manager(): void
    {
        $this->actingAs($this->user);

        $component = Livewire::test(OrderShippingRelationManager::class, [
            'ownerRecord' => $this->order,
            'pageClass'   => EditOrder::class,
        ]);

        $component->assertSuccessful();
    }

    #[Test]
    public function it_can_create_order_shipping(): void
    {
        $this->actingAs($this->user);

        $component = Livewire::test(OrderShippingRelationManager::class, [
            'ownerRecord' => $this->order,
            'pageClass'   => EditOrder::class,
        ]);

        $component
            ->mountTableAction('create')
            ->set('mountedActions.0.data.shipping_method', 'express')
            ->set('mountedActions.0.data.tracking_number', 'TRK123456789')
            ->set('mountedActions.0.data.carrier', 'DHL')
            ->set('mountedActions.0.data.service_type', 'Express')
            ->set('mountedActions.0.data.base_cost', 15.0)
            ->set('mountedActions.0.data.insurance_cost', 5.0)
            ->set('mountedActions.0.data.total_cost', 20.0)
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('order_shippings', [
            'order_id'        => $this->order->id,
            'shipping_method' => 'express',
            'tracking_number' => 'TRK123456789',
            'carrier'         => 'DHL',
        ]);
    }

    #[Test]
    public function it_can_mark_shipping_as_shipped(): void
    {
        $this->actingAs($this->user);

        $shipping = OrderShipping::factory()->create([
            'order_id' => $this->order->id,
            'status'   => 'pending',
        ]);

        $component = Livewire::test(OrderShippingRelationManager::class, [
            'ownerRecord' => $this->order,
            'pageClass'   => EditOrder::class,
        ]);

        $component
            ->callTableAction('mark_shipped', $shipping)
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('order_shippings', [
            'id'     => $shipping->id,
            'status' => 'shipped',
        ]);
    }

    #[Test]
    public function it_can_mark_shipping_as_delivered(): void
    {
        $this->actingAs($this->user);

        $shipping = OrderShipping::factory()->create([
            'order_id' => $this->order->id,
            'status'   => 'shipped',
        ]);

        $component = Livewire::test(OrderShippingRelationManager::class, [
            'ownerRecord' => $this->order,
            'pageClass'   => EditOrder::class,
        ]);

        $component
            ->callTableAction('mark_delivered', $shipping)
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('order_shippings', [
            'id'           => $shipping->id,
            'status'       => 'delivered',
            'is_delivered' => true,
        ]);
    }

    #[Test]
    public function it_can_filter_by_shipping_method(): void
    {
        $this->actingAs($this->user);

        OrderShipping::factory()->create([
            'order_id'        => $this->order->id,
            'shipping_method' => 'standard',
        ]);

        OrderShipping::factory()->create([
            'order_id'        => $this->order->id,
            'shipping_method' => 'express',
        ]);

        $component = Livewire::test(OrderShippingRelationManager::class, [
            'ownerRecord' => $this->order,
            'pageClass'   => EditOrder::class,
        ]);

        $component
            ->filterTable('shipping_method', 'express')
            ->assertCanSeeTableRecords(
                OrderShipping::where('shipping_method', 'express')->get()
            );
    }

    #[Test]
    public function it_can_perform_bulk_mark_shipped(): void
    {
        $this->actingAs($this->user);

        $shippings = OrderShipping::factory()->count(2)->create([
            'order_id' => $this->order->id,
            'status'   => 'pending',
        ]);

        $component = Livewire::test(OrderShippingRelationManager::class, [
            'ownerRecord' => $this->order,
            'pageClass'   => EditOrder::class,
        ]);

        $component
            ->callTableBulkAction('mark_shipped', $shippings)
            ->assertHasNoFormErrors();

        foreach ($shippings as $shipping) {
            $this->assertDatabaseHas('order_shippings', [
                'id'     => $shipping->id,
                'status' => 'shipped',
            ]);
        }
    }
}
