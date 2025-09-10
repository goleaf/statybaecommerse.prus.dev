<?php declare(strict_types=1);

namespace Tests\Feature\Filament\Pages;

use App\Filament\Pages\InventoryManagement;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class InventoryManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminUser = User::factory()->create();
        $this->actingAs($this->adminUser);
    }

    public function test_page_mounts(): void
    {
        Livewire::test(InventoryManagement::class)->assertOk();
    }

    public function test_table_renders_and_actions_exist(): void
    {
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'stock_quantity' => 5,
            'low_stock_threshold' => 2,
        ]);

        Livewire::test(InventoryManagement::class)
            ->call('loadTable')
            ->assertTableActionExists('update_stock', null, $product)
            ->assertTableActionExists('view_variants', null, $product)
            ->assertTableBulkActionExists('bulk_stock_update')
            ->assertTableBulkActionExists('enable_tracking')
            ->assertTableBulkActionExists('disable_tracking');
    }

    public function test_update_stock_action_updates_record(): void
    {
        $product = Product::factory()->create([
            'stock_quantity' => 1,
            'low_stock_threshold' => 0,
        ]);

        Livewire::test(InventoryManagement::class)
            ->callTableAction('update_stock', $product, [
                'stock_quantity' => 10,
                'low_stock_threshold' => 3,
            ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock_quantity' => 10,
            'low_stock_threshold' => 3,
        ]);
    }

    public function test_bulk_stock_update_increase(): void
    {
        $p1 = Product::factory()->create(['stock_quantity' => 1]);
        $p2 = Product::factory()->create(['stock_quantity' => 2]);

        Livewire::test(InventoryManagement::class)
            ->call('loadTable')
            ->callTableBulkAction('bulk_stock_update', collect([$p1, $p2]), [
                'operation' => 'increase',
                'quantity' => 3,
            ]);

        $this->assertDatabaseHas('products', ['id' => $p1->id, 'stock_quantity' => 4]);
        $this->assertDatabaseHas('products', ['id' => $p2->id, 'stock_quantity' => 5]);
    }
}


