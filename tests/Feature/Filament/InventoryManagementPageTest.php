<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Pages\InventoryManagement;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class InventoryManagementPageTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure Filament resolves the admin panel before mounting components.
        $this->resolveAdminPanel();

        // Authenticate as an administrator so the page policies pass.
        $this->admin = User::factory()->admin()->create();
        $this->actingAs($this->admin);
    }

    public function test_bulk_stock_update_increases_quantity(): void
    {
        // Seed a product so the table bulk action has a tangible record to operate on.
        $product = Product::factory()->create([
            'stock_quantity' => 5,
        ]);

        Livewire::test(InventoryManagement::class)
            // Mount the Filament v4 bulk action with the selected product.
            ->mountTableBulkAction('bulk_stock_update', [$product])
            // Provide the form payload the action expects before execution.
            ->setTableBulkActionData([
                'operation' => 'increase',
                'quantity' => 4,
            ])
            // Execute the mounted action and confirm no validation issues surface.
            ->callMountedTableBulkAction()
            ->assertHasNoTableActionErrors();

        $product->refresh();

        $this->assertSame(9, $product->stock_quantity);
    }

    public function test_bulk_stock_update_never_drops_below_zero(): void
    {
        // Start with a minimal stock level so the decrease path has work to do.
        $product = Product::factory()->create([
            'stock_quantity' => 3,
        ]);

        Livewire::test(InventoryManagement::class)
            ->mountTableBulkAction('bulk_stock_update', [$product])
            ->setTableBulkActionData([
                'operation' => 'decrease',
                'quantity' => 10,
            ])
            ->callMountedTableBulkAction()
            ->assertHasNoTableActionErrors();

        $product->refresh();

        $this->assertSame(0, $product->stock_quantity);
    }

    public function test_navigation_metadata_exposes_expected_icon(): void
    {
        // Verify the navigation metadata remains aligned with the product tooling group.
        $this->assertSame('heroicon-o-archive-box', InventoryManagement::getNavigationIcon());
        $this->assertSame('Products', InventoryManagement::getNavigationGroup());
        $this->assertSame('inventory-management', InventoryManagement::getSlug());
    }
}
