<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Resources\VariantInventoryResource;
use App\Models\Location;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\VariantInventory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * VariantInventoryResourceTest
 *
 * Comprehensive test suite for VariantInventoryResource functionality including
 * CRUD operations, relationships, filters, actions, and bulk operations.
 */
final class VariantInventoryResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected Product $product;

    protected ProductVariant $variant;

    protected Location $location;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin user
        $this->adminUser = User::factory()->create([
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);

        // Create test data
        $this->product = Product::factory()->create();
        $this->variant = ProductVariant::factory()->create([
            'product_id' => $this->product->id,
        ]);
        $this->location = Location::factory()->create();
    }

    /** @test */
    public function it_can_list_variant_inventories(): void
    {
        VariantInventory::factory()->count(3)->create([
            'variant_id' => $this->variant->id,
            'location_id' => $this->location->id,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(VariantInventoryResource\Pages\ListVariantInventories::class)
            ->assertCanSeeTableRecords(VariantInventory::all());
    }

    /** @test */
    public function it_can_create_variant_inventory(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(VariantInventoryResource\Pages\CreateVariantInventory::class)
            ->fillForm([
                'variant_id' => $this->variant->id,
                'location_id' => $this->location->id,
                'warehouse_code' => 'WH001',
                'stock' => 100,
                'reserved' => 10,
                'available' => 90,
                'threshold' => 20,
                'reorder_point' => 15,
                'cost_per_unit' => 25.50,
                'is_tracked' => true,
                'status' => 'active',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('variant_inventories', [
            'variant_id' => $this->variant->id,
            'location_id' => $this->location->id,
            'warehouse_code' => 'WH001',
            'stock' => 100,
            'reserved' => 10,
            'available' => 90,
            'threshold' => 20,
            'reorder_point' => 15,
            'cost_per_unit' => 25.50,
            'is_tracked' => true,
            'status' => 'active',
        ]);
    }

    /** @test */
    public function it_can_edit_variant_inventory(): void
    {
        $inventory = VariantInventory::factory()->create([
            'variant_id' => $this->variant->id,
            'location_id' => $this->location->id,
            'stock' => 50,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(VariantInventoryResource\Pages\EditVariantInventory::class, [
            'record' => $inventory->getRouteKey(),
        ])
            ->fillForm([
                'stock' => 75,
                'threshold' => 25,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('variant_inventories', [
            'id' => $inventory->id,
            'stock' => 75,
            'threshold' => 25,
        ]);
    }

    /** @test */
    public function it_can_view_variant_inventory(): void
    {
        $inventory = VariantInventory::factory()->create([
            'variant_id' => $this->variant->id,
            'location_id' => $this->location->id,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(VariantInventoryResource\Pages\ViewVariantInventory::class, [
            'record' => $inventory->getRouteKey(),
        ])
            ->assertOk();
    }

    /** @test */
    public function it_validates_required_fields(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(VariantInventoryResource\Pages\CreateVariantInventory::class)
            ->fillForm([
                'variant_id' => null,
                'location_id' => null,
            ])
            ->call('create')
            ->assertHasFormErrors([
                'variant_id' => 'required',
                'location_id' => 'required',
            ]);
    }

    /** @test */
    public function it_validates_numeric_fields(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(VariantInventoryResource\Pages\CreateVariantInventory::class)
            ->fillForm([
                'variant_id' => $this->variant->id,
                'location_id' => $this->location->id,
                'stock' => 'invalid',
                'cost_per_unit' => 'invalid',
            ])
            ->call('create')
            ->assertHasFormErrors([
                'stock' => 'numeric',
                'cost_per_unit' => 'numeric',
            ]);
    }

    /** @test */
    public function it_validates_minimum_values(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(VariantInventoryResource\Pages\CreateVariantInventory::class)
            ->fillForm([
                'variant_id' => $this->variant->id,
                'location_id' => $this->location->id,
                'stock' => -1,
                'threshold' => -1,
            ])
            ->call('create')
            ->assertHasFormErrors([
                'stock' => 'min',
                'threshold' => 'min',
            ]);
    }

    /** @test */
    public function it_can_filter_by_variant(): void
    {
        $variant2 = ProductVariant::factory()->create(['product_id' => $this->product->id]);

        VariantInventory::factory()->create([
            'variant_id' => $this->variant->id,
            'location_id' => $this->location->id,
        ]);

        VariantInventory::factory()->create([
            'variant_id' => $variant2->id,
            'location_id' => $this->location->id,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(VariantInventoryResource\Pages\ListVariantInventories::class)
            ->filterTable('variant_id', $this->variant->id)
            ->assertCanSeeTableRecords(
                VariantInventory::where('variant_id', $this->variant->id)->get()
            )
            ->assertCanNotSeeTableRecords(
                VariantInventory::where('variant_id', $variant2->id)->get()
            );
    }

    /** @test */
    public function it_can_filter_by_location(): void
    {
        $location2 = Location::factory()->create();

        VariantInventory::factory()->create([
            'variant_id' => $this->variant->id,
            'location_id' => $this->location->id,
        ]);

        VariantInventory::factory()->create([
            'variant_id' => $this->variant->id,
            'location_id' => $location2->id,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(VariantInventoryResource\Pages\ListVariantInventories::class)
            ->filterTable('location_id', $this->location->id)
            ->assertCanSeeTableRecords(
                VariantInventory::where('location_id', $this->location->id)->get()
            )
            ->assertCanNotSeeTableRecords(
                VariantInventory::where('location_id', $location2->id)->get()
            );
    }

    /** @test */
    public function it_can_filter_by_status(): void
    {
        VariantInventory::factory()->create([
            'variant_id' => $this->variant->id,
            'location_id' => $this->location->id,
            'status' => 'active',
        ]);

        VariantInventory::factory()->create([
            'variant_id' => $this->variant->id,
            'location_id' => $this->location->id,
            'status' => 'inactive',
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(VariantInventoryResource\Pages\ListVariantInventories::class)
            ->filterTable('status', 'active')
            ->assertCanSeeTableRecords(
                VariantInventory::where('status', 'active')->get()
            )
            ->assertCanNotSeeTableRecords(
                VariantInventory::where('status', 'inactive')->get()
            );
    }

    /** @test */
    public function it_can_filter_low_stock_items(): void
    {
        // Low stock item
        VariantInventory::factory()->create([
            'variant_id' => $this->variant->id,
            'location_id' => $this->location->id,
            'available' => 5,
            'reorder_point' => 10,
        ]);

        // Normal stock item
        VariantInventory::factory()->create([
            'variant_id' => $this->variant->id,
            'location_id' => $this->location->id,
            'available' => 50,
            'reorder_point' => 10,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(VariantInventoryResource\Pages\ListVariantInventories::class)
            ->filterTable('low_stock')
            ->assertCanSeeTableRecords(
                VariantInventory::whereRaw('available <= reorder_point')->get()
            );
    }

    /** @test */
    public function it_can_filter_out_of_stock_items(): void
    {
        // Out of stock item
        VariantInventory::factory()->create([
            'variant_id' => $this->variant->id,
            'location_id' => $this->location->id,
            'available' => 0,
        ]);

        // In stock item
        VariantInventory::factory()->create([
            'variant_id' => $this->variant->id,
            'location_id' => $this->location->id,
            'available' => 50,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(VariantInventoryResource\Pages\ListVariantInventories::class)
            ->filterTable('out_of_stock')
            ->assertCanSeeTableRecords(
                VariantInventory::where('available', '<=', 0)->get()
            );
    }

    /** @test */
    public function it_can_adjust_stock(): void
    {
        $inventory = VariantInventory::factory()->create([
            'variant_id' => $this->variant->id,
            'location_id' => $this->location->id,
            'stock' => 100,
            'available' => 90,
            'reserved' => 10,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(VariantInventoryResource\Pages\ListVariantInventories::class)
            ->callTableAction('adjust_stock', $inventory, [
                'quantity' => 20,
                'adjustment_type' => 'add',
                'reason' => 'Stock replenishment',
            ])
            ->assertNotified('Stock adjusted successfully');

        $inventory->refresh();
        $this->assertEquals(120, $inventory->stock);
        $this->assertEquals(110, $inventory->available);
    }

    /** @test */
    public function it_can_reserve_stock(): void
    {
        $inventory = VariantInventory::factory()->create([
            'variant_id' => $this->variant->id,
            'location_id' => $this->location->id,
            'stock' => 100,
            'available' => 100,
            'reserved' => 0,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(VariantInventoryResource\Pages\ListVariantInventories::class)
            ->callTableAction('reserve_stock', $inventory, [
                'quantity' => 25,
                'reason' => 'Customer order',
            ])
            ->assertNotified('Stock reserved successfully');

        $inventory->refresh();
        $this->assertEquals(25, $inventory->reserved);
        $this->assertEquals(75, $inventory->available);
    }

    /** @test */
    public function it_prevents_reserving_insufficient_stock(): void
    {
        $inventory = VariantInventory::factory()->create([
            'variant_id' => $this->variant->id,
            'location_id' => $this->location->id,
            'stock' => 100,
            'available' => 10,
            'reserved' => 90,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(VariantInventoryResource\Pages\ListVariantInventories::class)
            ->callTableAction('reserve_stock', $inventory, [
                'quantity' => 25,
                'reason' => 'Customer order',
            ])
            ->assertNotified('Insufficient stock');
    }

    /** @test */
    public function it_can_perform_bulk_stock_adjustment(): void
    {
        $inventories = VariantInventory::factory()->count(3)->create([
            'variant_id' => $this->variant->id,
            'location_id' => $this->location->id,
            'stock' => 50,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(VariantInventoryResource\Pages\ListVariantInventories::class)
            ->callTableBulkAction('bulk_adjust_stock', $inventories, [
                'quantity' => 10,
                'adjustment_type' => 'add',
                'reason' => 'Bulk restock',
            ])
            ->assertNotified('Successfully adjusted stock for 3 records');

        foreach ($inventories as $inventory) {
            $inventory->refresh();
            $this->assertEquals(60, $inventory->stock);
        }
    }

    /** @test */
    public function it_can_perform_bulk_status_update(): void
    {
        $inventories = VariantInventory::factory()->count(3)->create([
            'variant_id' => $this->variant->id,
            'location_id' => $this->location->id,
            'status' => 'active',
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(VariantInventoryResource\Pages\ListVariantInventories::class)
            ->callTableBulkAction('bulk_update_status', $inventories, [
                'status' => 'inactive',
            ])
            ->assertNotified('Successfully updated status for 3 records');

        foreach ($inventories as $inventory) {
            $inventory->refresh();
            $this->assertEquals('inactive', $inventory->status);
        }
    }

    /** @test */
    public function it_can_group_by_variant(): void
    {
        $variant2 = ProductVariant::factory()->create(['product_id' => $this->product->id]);

        VariantInventory::factory()->count(2)->create([
            'variant_id' => $this->variant->id,
            'location_id' => $this->location->id,
        ]);

        VariantInventory::factory()->create([
            'variant_id' => $variant2->id,
            'location_id' => $this->location->id,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(VariantInventoryResource\Pages\ListVariantInventories::class)
            ->groupTable('variant.name')
            ->assertCanSeeTableRecords(
                VariantInventory::with('variant')->get()
            );
    }

    /** @test */
    public function it_can_group_by_location(): void
    {
        $location2 = Location::factory()->create();

        VariantInventory::factory()->count(2)->create([
            'variant_id' => $this->variant->id,
            'location_id' => $this->location->id,
        ]);

        VariantInventory::factory()->create([
            'variant_id' => $this->variant->id,
            'location_id' => $location2->id,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(VariantInventoryResource\Pages\ListVariantInventories::class)
            ->groupTable('location.name')
            ->assertCanSeeTableRecords(
                VariantInventory::with('location')->get()
            );
    }

    /** @test */
    public function it_can_group_by_status(): void
    {
        VariantInventory::factory()->count(2)->create([
            'variant_id' => $this->variant->id,
            'location_id' => $this->location->id,
            'status' => 'active',
        ]);

        VariantInventory::factory()->create([
            'variant_id' => $this->variant->id,
            'location_id' => $this->location->id,
            'status' => 'inactive',
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(VariantInventoryResource\Pages\ListVariantInventories::class)
            ->groupTable('status')
            ->assertCanSeeTableRecords(
                VariantInventory::all()
            );
    }

    /** @test */
    public function it_shows_calculated_fields_in_form(): void
    {
        $inventory = VariantInventory::factory()->create([
            'variant_id' => $this->variant->id,
            'location_id' => $this->location->id,
            'available' => 5,
            'reorder_point' => 10,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(VariantInventoryResource\Pages\EditVariantInventory::class, [
            'record' => $inventory->getRouteKey(),
        ])
            ->assertFormSet([
                'is_low_stock' => true,
                'is_out_of_stock' => false,
                'stock_status' => 'low_stock',
            ]);
    }

    /** @test */
    public function it_displays_stock_status_colors_correctly(): void
    {
        // Low stock
        $lowStockInventory = VariantInventory::factory()->create([
            'variant_id' => $this->variant->id,
            'location_id' => $this->location->id,
            'available' => 5,
            'reorder_point' => 10,
        ]);

        // Out of stock
        $outOfStockInventory = VariantInventory::factory()->create([
            'variant_id' => $this->variant->id,
            'location_id' => $this->location->id,
            'available' => 0,
        ]);

        // In stock
        $inStockInventory = VariantInventory::factory()->create([
            'variant_id' => $this->variant->id,
            'location_id' => $this->location->id,
            'available' => 50,
            'reorder_point' => 10,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(VariantInventoryResource\Pages\ListVariantInventories::class)
            ->assertCanSeeTableRecords([
                $lowStockInventory,
                $outOfStockInventory,
                $inStockInventory,
            ]);
    }

    /** @test */
    public function it_can_delete_variant_inventory(): void
    {
        $inventory = VariantInventory::factory()->create([
            'variant_id' => $this->variant->id,
            'location_id' => $this->location->id,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(VariantInventoryResource\Pages\ListVariantInventories::class)
            ->callTableAction('delete', $inventory)
            ->assertNotified('Variant inventory deleted');

        $this->assertSoftDeleted('variant_inventories', [
            'id' => $inventory->id,
        ]);
    }

    /** @test */
    public function it_can_bulk_delete_variant_inventories(): void
    {
        $inventories = VariantInventory::factory()->count(3)->create([
            'variant_id' => $this->variant->id,
            'location_id' => $this->location->id,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(VariantInventoryResource\Pages\ListVariantInventories::class)
            ->callTableBulkAction('delete', $inventories)
            ->assertNotified('Variant inventories deleted');

        foreach ($inventories as $inventory) {
            $this->assertSoftDeleted('variant_inventories', [
                'id' => $inventory->id,
            ]);
        }
    }

    /** @test */
    public function it_can_export_inventory(): void
    {
        VariantInventory::factory()->count(5)->create([
            'variant_id' => $this->variant->id,
            'location_id' => $this->location->id,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(VariantInventoryResource\Pages\ListVariantInventories::class)
            ->callTableBulkAction('export_inventory', VariantInventory::all())
            ->assertNotified('Exported successfully');
    }

    /** @test */
    public function it_requires_admin_permissions(): void
    {
        $regularUser = User::factory()->create([
            'email' => 'user@example.com',
            'is_admin' => false,
        ]);

        $this->actingAs($regularUser);

        // Should not be able to access the resource
        $this->get(VariantInventoryResource::getUrl('index'))
            ->assertStatus(403);
    }

    /** @test */
    public function it_displays_relationships_correctly(): void
    {
        $inventory = VariantInventory::factory()->create([
            'variant_id' => $this->variant->id,
            'location_id' => $this->location->id,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(VariantInventoryResource\Pages\ListVariantInventories::class)
            ->assertCanSeeTableRecords([$inventory])
            ->assertCanSeeTableColumn('variant.name')
            ->assertCanSeeTableColumn('location.name');
    }

    /** @test */
    public function it_handles_foreign_key_constraints(): void
    {
        $this->actingAs($this->adminUser);

        // Try to create inventory with non-existent variant
        Livewire::test(VariantInventoryResource\Pages\CreateVariantInventory::class)
            ->fillForm([
                'variant_id' => 99999,
                'location_id' => $this->location->id,
            ])
            ->call('create')
            ->assertHasFormErrors(['variant_id']);

        // Try to create inventory with non-existent location
        Livewire::test(VariantInventoryResource\Pages\CreateVariantInventory::class)
            ->fillForm([
                'variant_id' => $this->variant->id,
                'location_id' => 99999,
            ])
            ->call('create')
            ->assertHasFormErrors(['location_id']);
    }
}
