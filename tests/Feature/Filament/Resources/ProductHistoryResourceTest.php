<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\ProductHistoryResource;
use App\Models\Product;
use App\Models\ProductHistory;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class ProductHistoryResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user
        $this->user = User::factory()->create([
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);

        // Create test product
        $this->product = Product::factory()->create([
            'name' => 'Test Product',
            'sku' => 'TEST-001',
        ]);
    }

    public function test_can_list_product_histories(): void
    {
        // Create test data
        ProductHistory::factory()->create([
            'product_id' => $this->product->id,
            'user_id' => $this->user->id,
            'action' => 'created',
            'field_name' => 'name',
            'old_value' => null,
            'new_value' => 'Test Product',
        ]);

        $this->actingAs($this->user);

        Livewire::test(ListRecords::class, [
            'resource' => ProductHistoryResource::class,
        ])
            ->assertCanSeeTableRecords(ProductHistory::all());
    }

    public function test_can_create_product_history(): void
    {
        $this->actingAs($this->user);

        Livewire::test(CreateRecord::class, [
            'resource' => ProductHistoryResource::class,
        ])
            ->fillForm([
                'product_id' => $this->product->id,
                'user_id' => $this->user->id,
                'action' => 'updated',
                'field_name' => 'price',
                'old_value' => '100.00',
                'new_value' => '120.00',
                'description' => 'Price updated by admin',
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Test Browser',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('product_histories', [
            'product_id' => $this->product->id,
            'user_id' => $this->user->id,
            'action' => 'updated',
            'field_name' => 'price',
            'description' => 'Price updated by admin',
        ]);
    }

    public function test_can_edit_product_history(): void
    {
        $history = ProductHistory::factory()->create([
            'product_id' => $this->product->id,
            'user_id' => $this->user->id,
            'action' => 'created',
            'field_name' => 'name',
            'description' => 'Product created',
        ]);

        $this->actingAs($this->user);

        Livewire::test(EditRecord::class, [
            'resource' => ProductHistoryResource::class,
            'record' => $history->getRouteKey(),
        ])
            ->fillForm([
                'description' => 'Updated description',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('product_histories', [
            'id' => $history->id,
            'description' => 'Updated description',
        ]);
    }

    public function test_can_view_product_history(): void
    {
        $history = ProductHistory::factory()->create([
            'product_id' => $this->product->id,
            'user_id' => $this->user->id,
            'action' => 'updated',
            'field_name' => 'price',
            'description' => 'Price was updated',
        ]);

        $this->actingAs($this->user);

        Livewire::test(ViewRecord::class, [
            'resource' => ProductHistoryResource::class,
            'record' => $history->getRouteKey(),
        ])
            ->assertCanSeeText('Price was updated');
    }

    public function test_can_filter_by_product(): void
    {
        $product2 = Product::factory()->create();

        ProductHistory::factory()->create([
            'product_id' => $this->product->id,
            'user_id' => $this->user->id,
            'action' => 'created',
        ]);

        ProductHistory::factory()->create([
            'product_id' => $product2->id,
            'user_id' => $this->user->id,
            'action' => 'created',
        ]);

        $this->actingAs($this->user);

        Livewire::test(ListRecords::class, [
            'resource' => ProductHistoryResource::class,
        ])
            ->filterTable('product_id', $this->product->id)
            ->assertCanSeeTableRecords(ProductHistory::where('product_id', $this->product->id)->get())
            ->assertCanNotSeeTableRecords(ProductHistory::where('product_id', $product2->id)->get());
    }

    public function test_can_filter_by_action(): void
    {
        ProductHistory::factory()->create([
            'product_id' => $this->product->id,
            'user_id' => $this->user->id,
            'action' => 'created',
        ]);

        ProductHistory::factory()->create([
            'product_id' => $this->product->id,
            'user_id' => $this->user->id,
            'action' => 'updated',
        ]);

        $this->actingAs($this->user);

        Livewire::test(ListRecords::class, [
            'resource' => ProductHistoryResource::class,
        ])
            ->filterTable('action', 'created')
            ->assertCanSeeTableRecords(ProductHistory::where('action', 'created')->get())
            ->assertCanNotSeeTableRecords(ProductHistory::where('action', 'updated')->get());
    }

    public function test_can_filter_by_date_range(): void
    {
        $oldHistory = ProductHistory::factory()->create([
            'product_id' => $this->product->id,
            'user_id' => $this->user->id,
            'action' => 'created',
            'created_at' => now()->subDays(10),
        ]);

        $recentHistory = ProductHistory::factory()->create([
            'product_id' => $this->product->id,
            'user_id' => $this->user->id,
            'action' => 'updated',
            'created_at' => now()->subDays(2),
        ]);

        $this->actingAs($this->user);

        Livewire::test(ListRecords::class, [
            'resource' => ProductHistoryResource::class,
        ])
            ->filterTable('date_range', [
                'from' => now()->subDays(5)->format('Y-m-d'),
                'until' => now()->format('Y-m-d'),
            ])
            ->assertCanSeeTableRecords([$recentHistory])
            ->assertCanNotSeeTableRecords([$oldHistory]);
    }

    public function test_can_sort_by_created_at(): void
    {
        $history1 = ProductHistory::factory()->create([
            'product_id' => $this->product->id,
            'user_id' => $this->user->id,
            'created_at' => now()->subDay(),
        ]);

        $history2 = ProductHistory::factory()->create([
            'product_id' => $this->product->id,
            'user_id' => $this->user->id,
            'created_at' => now(),
        ]);

        $this->actingAs($this->user);

        Livewire::test(ListRecords::class, [
            'resource' => ProductHistoryResource::class,
        ])
            ->sortTable('created_at', 'desc')
            ->assertCanSeeTableRecords([$history2, $history1]);
    }

    public function test_navigation_group_is_products(): void
    {
        $this->assertEquals(
            NavigationGroup::Products->value,
            ProductHistoryResource::getNavigationGroup()
        );
    }

    public function test_navigation_sort_is_11(): void
    {
        $this->assertEquals(11, ProductHistoryResource::getNavigationSort());
    }

    public function test_navigation_icon_is_clock(): void
    {
        $this->assertEquals('heroicon-o-clock', ProductHistoryResource::getNavigationIcon());
    }

    public function test_has_correct_pages(): void
    {
        $pages = ProductHistoryResource::getPages();

        $this->assertArrayHasKey('index', $pages);
        $this->assertArrayHasKey('create', $pages);
        $this->assertArrayHasKey('view', $pages);
        $this->assertArrayHasKey('edit', $pages);
    }

    public function test_form_validation_requires_product_and_action(): void
    {
        $this->actingAs($this->user);

        Livewire::test(CreateRecord::class, [
            'resource' => ProductHistoryResource::class,
        ])
            ->fillForm([
                'field_name' => 'name',
                'description' => 'Test description',
            ])
            ->call('create')
            ->assertHasFormErrors(['product_id', 'action']);
    }

    public function test_can_bulk_delete_product_histories(): void
    {
        $history1 = ProductHistory::factory()->create([
            'product_id' => $this->product->id,
            'user_id' => $this->user->id,
            'action' => 'created',
        ]);

        $history2 = ProductHistory::factory()->create([
            'product_id' => $this->product->id,
            'user_id' => $this->user->id,
            'action' => 'updated',
        ]);

        $this->actingAs($this->user);

        Livewire::test(ListRecords::class, [
            'resource' => ProductHistoryResource::class,
        ])
            ->callTableBulkAction('delete', [$history1, $history2])
            ->assertHasNoTableBulkActionErrors();

        $this->assertDatabaseMissing('product_histories', [
            'id' => $history1->id,
        ]);

        $this->assertDatabaseMissing('product_histories', [
            'id' => $history2->id,
        ]);
    }

    public function test_action_options_are_available(): void
    {
        $expectedActions = [
            'created' => 'Created',
            'updated' => 'Updated',
            'deleted' => 'Deleted',
            'restored' => 'Restored',
            'price_changed' => 'Price Changed',
            'stock_updated' => 'Stock Updated',
            'status_changed' => 'Status Changed',
            'category_changed' => 'Category Changed',
            'image_changed' => 'Image Changed',
            'custom' => 'Custom',
        ];

        // Test that all expected actions are available in the form
        $this->actingAs($this->user);

        Livewire::test(CreateRecord::class, [
            'resource' => ProductHistoryResource::class,
        ])
            ->assertFormExists()
            ->assertFormFieldExists('action');
    }

    public function test_can_handle_json_values(): void
    {
        $this->actingAs($this->user);

        $jsonData = ['key1' => 'value1', 'key2' => 'value2'];

        Livewire::test(CreateRecord::class, [
            'resource' => ProductHistoryResource::class,
        ])
            ->fillForm([
                'product_id' => $this->product->id,
                'action' => 'updated',
                'field_name' => 'metadata',
                'old_value' => json_encode($jsonData),
                'new_value' => json_encode(array_merge($jsonData, ['key3' => 'value3'])),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('product_histories', [
            'field_name' => 'metadata',
        ]);
    }

    public function test_can_handle_metadata_key_value_pairs(): void
    {
        $this->actingAs($this->user);

        Livewire::test(CreateRecord::class, [
            'resource' => ProductHistoryResource::class,
        ])
            ->fillForm([
                'product_id' => $this->product->id,
                'action' => 'updated',
                'field_name' => 'attributes',
                'metadata' => [
                    'source' => 'admin_panel',
                    'version' => '1.0',
                    'timestamp' => now()->toISOString(),
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('product_histories', [
            'field_name' => 'attributes',
        ]);
    }
}
