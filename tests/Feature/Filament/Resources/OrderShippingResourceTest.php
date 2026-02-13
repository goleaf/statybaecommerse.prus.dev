<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages\EditOrder;
use App\Filament\Resources\OrderResource\RelationManagers\OrderShippingRelationManager;
use App\Models\Order;
use App\Models\OrderShipping;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class OrderShippingResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->order = Order::factory()->create([
            'user_id' => $this->user->getKey(),
        ]);
    }

    public function test_can_render_shipping_relation_manager(): void
    {
        $this->actingAs($this->user);

        Livewire::test(OrderShippingRelationManager::class, [
            'ownerRecord' => $this->order,
            'pageClass'   => EditOrder::class,
        ])->assertSuccessful();
    }

    public function test_can_create_shipping_record(): void
    {
        $this->actingAs($this->user);

        Livewire::test(OrderShippingRelationManager::class, [
            'ownerRecord' => $this->order,
            'pageClass'   => EditOrder::class,
        ])
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
            'order_id'        => $this->order->getKey(),
            'shipping_method' => 'express',
            'tracking_number' => 'TRK123456789',
            'carrier'         => 'DHL',
        ]);
    }

    public function test_can_mark_shipping_as_shipped(): void
    {
        $this->actingAs($this->user);

        $shipping = OrderShipping::factory()->create([
            'order_id' => $this->order->getKey(),
            'status'   => 'pending',
        ]);

        Livewire::test(OrderShippingRelationManager::class, [
            'ownerRecord' => $this->order,
            'pageClass'   => EditOrder::class,
        ])
            ->callTableAction('mark_shipped', $shipping)
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('order_shippings', [
            'id'     => $shipping->getKey(),
            'status' => 'shipped',
        ]);
    }

    public function test_can_mark_shipping_as_delivered(): void
    {
        $this->actingAs($this->user);

        $shipping = OrderShipping::factory()->create([
            'order_id' => $this->order->getKey(),
            'status'   => 'shipped',
        ]);

        Livewire::test(OrderShippingRelationManager::class, [
            'ownerRecord' => $this->order,
            'pageClass'   => EditOrder::class,
        ])
            ->callTableAction('mark_delivered', $shipping)
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('order_shippings', [
            'id'           => $shipping->getKey(),
            'status'       => 'delivered',
            'is_delivered' => true,
        ]);
    }

    public function test_can_filter_shipping_by_method(): void
    {
        $this->actingAs($this->user);

        $standard = OrderShipping::factory()->create([
            'order_id'        => $this->order->getKey(),
            'shipping_method' => 'standard',
        ]);

        $express = OrderShipping::factory()->create([
            'order_id'        => $this->order->getKey(),
            'shipping_method' => 'express',
        ]);

        Livewire::test(OrderShippingRelationManager::class, [
            'ownerRecord' => $this->order,
            'pageClass'   => EditOrder::class,
        ])
            ->filterTable('shipping_method', 'express')
            ->assertCanSeeTableRecords([$express])
            ->assertCanNotSeeTableRecords([$standard]);
    }

    public function test_can_bulk_mark_shippings_as_shipped(): void
    {
        $this->actingAs($this->user);

        $records = OrderShipping::factory()->count(2)->create([
            'order_id' => $this->order->getKey(),
            'status'   => 'pending',
        ]);

        Livewire::test(OrderShippingRelationManager::class, [
            'ownerRecord' => $this->order,
            'pageClass'   => EditOrder::class,
        ])
            ->callTableBulkAction('mark_shipped', $records)
            ->assertHasNoTableBulkActionErrors();

        foreach ($records as $record) {
            $this->assertDatabaseHas('order_shippings', [
                'id'     => $record->getKey(),
                'status' => 'shipped',
            ]);
        }
    }
}
