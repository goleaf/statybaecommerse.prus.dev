<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\ProductComparisonResource;
use App\Models\Product;
use App\Models\ProductComparison;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class ProductComparisonResourceTest extends TestCase
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

    public function test_can_list_product_comparisons(): void
    {
        // Create test data
        ProductComparison::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'session_id' => 'test-session-123',
        ]);

        $this->actingAs($this->user);

        Livewire::test(ListRecords::class, [
            'resource' => ProductComparisonResource::class,
        ])
            ->assertCanSeeTableRecords(ProductComparison::all());
    }

    public function test_can_create_product_comparison(): void
    {
        $this->actingAs($this->user);

        Livewire::test(CreateRecord::class, [
            'resource' => ProductComparisonResource::class,
        ])
            ->fillForm([
                'user_id' => $this->user->id,
                'product_id' => $this->product->id,
                'session_id' => 'new-session-456',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('product_comparisons', [
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'session_id' => 'new-session-456',
        ]);
    }

    public function test_can_edit_product_comparison(): void
    {
        $comparison = ProductComparison::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'session_id' => 'edit-session-789',
        ]);

        $this->actingAs($this->user);

        Livewire::test(EditRecord::class, [
            'resource' => ProductComparisonResource::class,
            'record' => $comparison->getRouteKey(),
        ])
            ->fillForm([
                'session_id' => 'updated-session-789',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('product_comparisons', [
            'id' => $comparison->id,
            'session_id' => 'updated-session-789',
        ]);
    }

    public function test_can_view_product_comparison(): void
    {
        $comparison = ProductComparison::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'session_id' => 'view-session-123',
        ]);

        $this->actingAs($this->user);

        Livewire::test(ViewRecord::class, [
            'resource' => ProductComparisonResource::class,
            'record' => $comparison->getRouteKey(),
        ])
            ->assertCanSeeText('view-session-123');
    }

    public function test_can_filter_by_user(): void
    {
        $user2 = User::factory()->create();

        ProductComparison::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
        ]);

        ProductComparison::factory()->create([
            'user_id' => $user2->id,
            'product_id' => $this->product->id,
        ]);

        $this->actingAs($this->user);

        Livewire::test(ListRecords::class, [
            'resource' => ProductComparisonResource::class,
        ])
            ->filterTable('user_id', $this->user->id)
            ->assertCanSeeTableRecords(ProductComparison::where('user_id', $this->user->id)->get())
            ->assertCanNotSeeTableRecords(ProductComparison::where('user_id', $user2->id)->get());
    }

    public function test_can_filter_by_product(): void
    {
        $product2 = Product::factory()->create();

        ProductComparison::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
        ]);

        ProductComparison::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $product2->id,
        ]);

        $this->actingAs($this->user);

        Livewire::test(ListRecords::class, [
            'resource' => ProductComparisonResource::class,
        ])
            ->filterTable('product_id', $this->product->id)
            ->assertCanSeeTableRecords(ProductComparison::where('product_id', $this->product->id)->get())
            ->assertCanNotSeeTableRecords(ProductComparison::where('product_id', $product2->id)->get());
    }

    public function test_can_search_by_session_id(): void
    {
        ProductComparison::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'session_id' => 'search-session-123',
        ]);

        $this->actingAs($this->user);

        Livewire::test(ListRecords::class, [
            'resource' => ProductComparisonResource::class,
        ])
            ->searchTable('search-session-123')
            ->assertCanSeeTableRecords(ProductComparison::where('session_id', 'like', '%search-session-123%')->get());
    }

    public function test_can_sort_by_created_at(): void
    {
        $comparison1 = ProductComparison::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'created_at' => now()->subDay(),
        ]);

        $comparison2 = ProductComparison::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'created_at' => now(),
        ]);

        $this->actingAs($this->user);

        Livewire::test(ListRecords::class, [
            'resource' => ProductComparisonResource::class,
        ])
            ->sortTable('created_at', 'desc')
            ->assertCanSeeTableRecords([$comparison2, $comparison1]);
    }

    public function test_navigation_group_is_products(): void
    {
        $this->assertEquals(
            NavigationGroup::Products,
            ProductComparisonResource::getNavigationGroup()
        );
    }

    public function test_navigation_sort_is_15(): void
    {
        $this->assertEquals(15, ProductComparisonResource::getNavigationSort());
    }

    public function test_record_title_attribute_is_session_id(): void
    {
        $this->assertEquals('session_id', ProductComparisonResource::getRecordTitleAttribute());
    }

    public function test_has_correct_pages(): void
    {
        $pages = ProductComparisonResource::getPages();

        $this->assertArrayHasKey('index', $pages);
        $this->assertArrayHasKey('create', $pages);
        $this->assertArrayHasKey('view', $pages);
        $this->assertArrayHasKey('edit', $pages);
    }

    public function test_form_validation_requires_user_and_product(): void
    {
        $this->actingAs($this->user);

        Livewire::test(CreateRecord::class, [
            'resource' => ProductComparisonResource::class,
        ])
            ->fillForm([
                'session_id' => 'test-session',
            ])
            ->call('create')
            ->assertHasFormErrors(['user_id', 'product_id']);
    }

    public function test_can_bulk_delete_product_comparisons(): void
    {
        $comparison1 = ProductComparison::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
        ]);

        $comparison2 = ProductComparison::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
        ]);

        $this->actingAs($this->user);

        Livewire::test(ListRecords::class, [
            'resource' => ProductComparisonResource::class,
        ])
            ->callTableBulkAction('delete', [$comparison1, $comparison2])
            ->assertHasNoTableBulkActionErrors();

        $this->assertDatabaseMissing('product_comparisons', [
            'id' => $comparison1->id,
        ]);

        $this->assertDatabaseMissing('product_comparisons', [
            'id' => $comparison2->id,
        ]);
    }
}
