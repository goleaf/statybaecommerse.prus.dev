<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\ProductVariantResource\Pages\ListProductVariants;
use App\Models\ProductVariant;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class ProductVariantResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Resolve the Filament admin panel so schema caches and navigation align with production.
        $this->resolveAdminPanel();

        // Seed permissions to guarantee the super admin can interact with catalogue management tooling.
        $this->seed(RolesAndPermissionsSeeder::class);

        // Create a deterministic administrator endowed with super admin privileges for variant operations.
        $this->adminUser = User::factory()->create([
            'email'    => 'variant-admin@example.test',
            'is_admin' => true,
        ]);
        $this->adminUser->assignRole('super_admin');
    }

    public function test_list_page_displays_variants_with_product_context(): void
    {
        // Generate two variants so the listing showcases product and pricing metadata.
        $variants = ProductVariant::factory()->count(2)->create();

        Livewire::actingAs($this->adminUser)
            ->test(ListProductVariants::class)
            ->call('loadTable')
            ->assertCanSeeTableRecords($variants);
    }

    public function test_product_lookup_filter_restricts_results_by_name(): void
    {
        // Produce clearly named variants to assert the product search filter behaves deterministically.
        $hammerVariant = ProductVariant::factory()->create([
            'name' => ['lt' => 'Plaktukas', 'en' => 'Hammer Variant'],
        ]);
        $sawVariant = ProductVariant::factory()->create([
            'name' => ['lt' => 'Pjūklas', 'en' => 'Saw Variant'],
        ]);

        Livewire::actingAs($this->adminUser)
            ->test(ListProductVariants::class)
            ->call('loadTable')
            ->filterTable('product_lookup', ['product_name' => 'Hammer'])
            ->assertCanSeeTableRecords([$hammerVariant])
            ->assertCanNotSeeTableRecords([$sawVariant]);
    }

    public function test_stock_status_filter_exposes_out_of_stock_variants(): void
    {
        // Force inventory data to hit both the out-of-stock and in-stock code paths for the filter.
        $outOfStock = ProductVariant::factory()->create([
            'stock_quantity'     => 0,
            'reserved_quantity'  => 0,
            'track_inventory'    => true,
        ]);
        $inStock = ProductVariant::factory()->create([
            'stock_quantity'     => 25,
            'reserved_quantity'  => 0,
            'track_inventory'    => true,
        ]);

        Livewire::actingAs($this->adminUser)
            ->test(ListProductVariants::class)
            ->call('loadTable')
            ->filterTable('stock_status', 'out_of_stock')
            ->assertCanSeeTableRecords([$outOfStock])
            ->assertCanNotSeeTableRecords([$inStock]);
    }
}
