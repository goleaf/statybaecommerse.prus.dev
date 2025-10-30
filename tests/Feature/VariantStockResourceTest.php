<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\VariantStockResource\Pages\ListVariantStocks;
use App\Models\Location;
use App\Models\VariantInventory;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class VariantStockResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Boot Filament so resource metadata and table filters match the live configuration.
        $this->resolveAdminPanel();

        // Seed roles and permissions to replicate production inventory privileges.
        $this->seed(RolesAndPermissionsSeeder::class);

        // Provision an administrator with super admin access for inventory maintenance flows.
        $this->adminUser = User::factory()->create([
            'email'    => 'inventory-admin@example.test',
            'is_admin' => true,
        ]);
        $this->adminUser->assignRole('super_admin');
    }

    public function test_list_page_displays_variant_inventory_rows(): void
    {
        // Persist a pair of inventory snapshots so the listing exposes product and location relations.
        $inventories = VariantInventory::factory()->count(2)->create();

        Livewire::actingAs($this->adminUser)
            ->test(ListVariantStocks::class)
            ->call('loadTable')
            ->assertCanSeeTableRecords($inventories);
    }

    public function test_location_filter_scopes_results_to_selected_facility(): void
    {
        // Create inventories mapped to distinct warehouse locations to validate the select filter wiring.
        $kaunasLocation = Location::factory()->create(['name' => 'Kaunas DC']);
        $vilniusLocation = Location::factory()->create(['name' => 'Vilnius DC']);

        $kaunasInventory = VariantInventory::factory()->for($kaunasLocation)->create();
        $vilniusInventory = VariantInventory::factory()->for($vilniusLocation)->create();

        Livewire::actingAs($this->adminUser)
            ->test(ListVariantStocks::class)
            ->call('loadTable')
            ->filterTable('location_id', (string) $kaunasLocation->getKey())
            ->assertCanSeeTableRecords([$kaunasInventory])
            ->assertCanNotSeeTableRecords([$vilniusInventory]);
    }

    public function test_low_stock_filter_returns_threshold_breaching_variants(): void
    {
        // Force one inventory record below its reorder threshold to trigger the low stock query scope.
        $lowStock = VariantInventory::factory()->create([
            'stock'     => 3,
            'threshold' => 5,
        ]);
        $healthyStock = VariantInventory::factory()->create([
            'stock'     => 20,
            'threshold' => 5,
        ]);

        Livewire::actingAs($this->adminUser)
            ->test(ListVariantStocks::class)
            ->call('loadTable')
            ->filterTable('low_stock', true)
            ->assertCanSeeTableRecords([$lowStock])
            ->assertCanNotSeeTableRecords([$healthyStock]);
    }
}
