<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\OrderResource\RelationManagers\OrderItemsRelationManager;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use App\Models\User;
use App\Support\Filament\ProductVariantFieldHelper;
use DefStudio\SearchableInput\Forms\Components\SearchableInput;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * OrderItemsRelationManagerTest
 *
 * Comprehensive test suite for OrderItemsRelationManager with Filament v4 compatibility
 */
final class OrderItemsRelationManagerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test data
        $this->user = User::factory()->create();
        $this->order = Order::factory()->create(['user_id' => $this->user->id]);
        $this->productVariant = ProductVariant::factory()->create();
    }

    #[Test]
    public function it_can_render_order_items_relation_manager(): void
    {
        $this->actingAs($this->user);

        $component = Livewire::test(OrderItemsRelationManager::class, [
            'ownerRecord' => $this->order,
            'pageClass'   => \App\Filament\Resources\OrderResource\Pages\ViewOrder::class,
        ]);

        $component->assertSuccessful();
    }

    #[Test]
    public function it_can_create_order_item(): void
    {
        $this->actingAs($this->user);

        $component = Livewire::test(OrderItemsRelationManager::class, [
            'ownerRecord' => $this->order,
            'pageClass'   => \App\Filament\Resources\OrderResource\Pages\ViewOrder::class,
        ]);

        $component
            ->callTableAction('create', null, [
                'product_variant_id' => $this->productVariant->id,
                'quantity'           => 2,
                'unit_price'         => 25.5,
                'discount_amount'    => 5.0,
                'notes'              => 'Test order item',
            ])
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('order_items', [
            'order_id'           => $this->order->id,
            'product_variant_id' => $this->productVariant->id,
            'quantity'           => 2,
            'unit_price'         => 25.5,
            'discount_amount'    => 5.0,
        ]);
    }

    #[Test]
    public function it_can_edit_order_item(): void
    {
        $this->actingAs($this->user);

        $orderItem = OrderItem::factory()->create([
            'order_id'           => $this->order->id,
            'product_variant_id' => $this->productVariant->id,
        ]);

        $component = Livewire::test(OrderItemsRelationManager::class, [
            'ownerRecord' => $this->order,
            'pageClass'   => \App\Filament\Resources\OrderResource\Pages\ViewOrder::class,
        ]);

        $component
            ->callTableAction('edit', $orderItem, [
                'quantity'   => 5,
                'unit_price' => 30.0,
            ])
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('order_items', [
            'id'         => $orderItem->id,
            'quantity'   => 5,
            'unit_price' => 30.0,
        ]);
    }

    #[Test]
    public function it_can_delete_order_item(): void
    {
        $this->actingAs($this->user);

        $orderItem = OrderItem::factory()->create([
            'order_id' => $this->order->id,
        ]);

        $component = Livewire::test(OrderItemsRelationManager::class, [
            'ownerRecord' => $this->order,
            'pageClass'   => \App\Filament\Resources\OrderResource\Pages\ViewOrder::class,
        ]);

        $component
            ->callTableAction('delete', $orderItem)
            ->assertHasNoFormErrors();

        $this->assertDatabaseMissing('order_items', [
            'id' => $orderItem->id,
        ]);
    }

    #[Test]
    public function it_can_duplicate_order_item(): void
    {
        $this->actingAs($this->user);

        $orderItem = OrderItem::factory()->create([
            'order_id'           => $this->order->id,
            'product_variant_id' => $this->productVariant->id,
        ]);

        $component = Livewire::test(OrderItemsRelationManager::class, [
            'ownerRecord' => $this->order,
            'pageClass'   => \App\Filament\Resources\OrderResource\Pages\ViewOrder::class,
        ]);

        $component
            ->callTableAction('duplicate_item', $orderItem)
            ->assertHasNoFormErrors();

        $this->assertDatabaseCount('order_items', 2);
    }

    #[Test]
    public function it_can_filter_order_items_by_status(): void
    {
        $this->actingAs($this->user);

        OrderItem::factory()->create([
            'order_id' => $this->order->id,
            'status'   => 'pending',
        ]);

        OrderItem::factory()->create([
            'order_id' => $this->order->id,
            'status'   => 'completed',
        ]);

        $component = Livewire::test(OrderItemsRelationManager::class, [
            'ownerRecord' => $this->order,
            'pageClass'   => \App\Filament\Resources\OrderResource\Pages\ViewOrder::class,
        ]);

        $component
            ->filterTable('status', 'pending')
            ->assertCanSeeTableRecords(
                OrderItem::where('status', 'pending')->get()
            );
    }

    #[Test]
    public function it_can_filter_order_items_by_discount(): void
    {
        $this->actingAs($this->user);

        OrderItem::factory()->create([
            'order_id'        => $this->order->id,
            'discount_amount' => 10.0,
        ]);

        OrderItem::factory()->create([
            'order_id'        => $this->order->id,
            'discount_amount' => 0.0,
        ]);

        $component = Livewire::test(OrderItemsRelationManager::class, [
            'ownerRecord' => $this->order,
            'pageClass'   => \App\Filament\Resources\OrderResource\Pages\ViewOrder::class,
        ]);

        $component
            ->filterTable('has_discount', true)
            ->assertCanSeeTableRecords(
                OrderItem::where('discount_amount', '>', 0)->get()
            );
    }

    #[Test]
    public function it_can_perform_bulk_mark_completed(): void
    {
        $this->actingAs($this->user);

        $orderItems = OrderItem::factory()->count(3)->create([
            'order_id' => $this->order->id,
            'status'   => 'pending',
        ]);

        $component = Livewire::test(OrderItemsRelationManager::class, [
            'ownerRecord' => $this->order,
            'pageClass'   => \App\Filament\Resources\OrderResource\Pages\ViewOrder::class,
        ]);

        $component
            ->callTableBulkAction('mark_completed', $orderItems)
            ->assertHasNoFormErrors();

        foreach ($orderItems as $item) {
            $this->assertDatabaseHas('order_items', [
                'id'     => $item->id,
                'status' => 'completed',
            ]);
        }
    }

    #[Test]
    public function it_can_perform_bulk_apply_discount(): void
    {
        $this->actingAs($this->user);

        $orderItems = OrderItem::factory()->count(2)->create([
            'order_id' => $this->order->id,
        ]);

        $component = Livewire::test(OrderItemsRelationManager::class, [
            'ownerRecord' => $this->order,
            'pageClass'   => \App\Filament\Resources\OrderResource\Pages\ViewOrder::class,
        ]);

        $component
            ->callTableBulkAction('apply_discount', $orderItems, [
                'discount_amount' => 15.0,
            ])
            ->assertHasNoFormErrors();

        foreach ($orderItems as $item) {
            $this->assertDatabaseHas('order_items', [
                'id'              => $item->id,
                'discount_amount' => 15.0,
            ]);
        }
    }

    #[Test]
    public function it_calculates_total_correctly(): void
    {
        $this->actingAs($this->user);

        $component = Livewire::test(OrderItemsRelationManager::class, [
            'ownerRecord' => $this->order,
            'pageClass'   => \App\Filament\Resources\OrderResource\Pages\ViewOrder::class,
        ]);

        $component
            ->callTableAction('create', null, [
                'product_variant_id' => $this->productVariant->id,
                'quantity'           => 3,
                'unit_price'         => 20.0,
                'discount_amount'    => 10.0,
            ])
            ->assertHasNoFormErrors();

        $orderItem = OrderItem::where('order_id', $this->order->id)->first();
        $expectedTotal = (3 * 20.0) - 10.0;  // 50.00

        $this->assertEquals($expectedTotal, $orderItem->total);
    }

    #[Test]
    public function it_validates_required_fields(): void
    {
        $this->actingAs($this->user);

        $component = Livewire::test(OrderItemsRelationManager::class, [
            'ownerRecord' => $this->order,
            'pageClass'   => \App\Filament\Resources\OrderResource\Pages\ViewOrder::class,
        ]);

        $component
            ->callTableAction('create', null, [
                'product_variant_id' => null,
                'quantity'           => null,
                'unit_price'         => null,
            ])
            ->assertHasFormErrors(['product_variant_id', 'quantity', 'unit_price']);
    }

    #[Test]
    public function it_validates_minimum_quantity(): void
    {
        $this->actingAs($this->user);

        $component = Livewire::test(OrderItemsRelationManager::class, [
            'ownerRecord' => $this->order,
            'pageClass'   => \App\Filament\Resources\OrderResource\Pages\ViewOrder::class,
        ]);

        $component
            ->callTableAction('create', null, [
                'product_variant_id' => $this->productVariant->id,
                'quantity'           => 0,
                'unit_price'         => 20.0,
            ])
            ->assertHasFormErrors(['quantity']);
    }

    /**
     * @test
     */
    public function it_clears_variant_lookup_and_snapshot_fields_when_selection_is_removed(): void
    {
        $this->actingAs($this->user);

        $component = SearchableInput::make('product_variant_id');

        // Hydrate the component to simulate a previously selected variant in the action form.
        ProductVariantFieldHelper::hydrateSearchableVariant($component, $this->productVariant->getKey());

        $fields = [
            'product_variant_id' => $this->productVariant->getKey(),
            'product_id'         => $this->productVariant->getAttribute('product_id'),
            'name'               => 'Persisted variant name',
            'sku'                => 'PERSISTED-SKU',
            'unit_price'         => 45.0,
            'quantity'           => 2,
            'discount_amount'    => 5.0,
            'total'              => 85.0,
        ];

        $set = static function (string $field, mixed $value) use (&$fields): void {
            $fields[$field] = $value;
        };

        $get = static function (string $field) use (&$fields): mixed {
            return $fields[$field] ?? null;
        };

        // Invoke the helper with an empty state to mirror the lookup being cleared in the UI.
        ProductVariantFieldHelper::handleVariantSelection('', $set, $get, $component);

        $this->assertNull($component->getState());
        $this->assertSame([], $component->getOptions());
        $this->assertSame([], $component->getPayload());

        $this->assertNull($fields['product_variant_id']);
        $this->assertNull($fields['product_id']);
        $this->assertSame('', $fields['name']);
        $this->assertSame('', $fields['sku']);
        $this->assertSame(0.0, $fields['unit_price']);
        $this->assertSame(0.0, $fields['total']);
    }
}
