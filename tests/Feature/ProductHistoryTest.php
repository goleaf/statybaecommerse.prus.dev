<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ProductHistoryTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->product = Product::factory()->create();
    }

    public function test_can_view_product_history_page(): void
    {
        ProductHistory::factory()->count(5)->create([
            'product_id' => $this->product->id,
        ]);

        $response = $this->get(route('localized.products.history', [
            'locale' => 'lt',
            'product' => $this->product->slug,
        ]));

        $response->assertStatus(200);
        $response->assertSee('Product History');
    }

    public function test_product_history_page_shows_correct_data(): void
    {
        $history = ProductHistory::factory()->create([
            'product_id' => $this->product->id,
            'action' => 'price_changed',
            'field_name' => 'price',
            'old_value' => 100,
            'new_value' => 120,
        ]);

        $response = $this->get(route('localized.products.history', [
            'locale' => 'lt',
            'product' => $this->product->slug,
        ]));

        $response->assertStatus(200);
        $response->assertSee($this->product->name);
        $response->assertSee('price_changed');
    }

    public function test_product_history_page_handles_nonexistent_product(): void
    {
        $response = $this->get(route('localized.products.history', [
            'locale' => 'lt',
            'product' => 'nonexistent-product',
        ]));

        $response->assertStatus(404);
    }

    public function test_product_history_api_endpoint(): void
    {
        ProductHistory::factory()->count(3)->create([
            'product_id' => $this->product->id,
        ]);

        $response = $this->getJson("/api/products/{$this->product->slug}/history");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'action',
                    'field_name',
                    'old_value',
                    'new_value',
                    'description',
                    'created_at',
                    'user' => [
                        'id',
                        'name',
                    ],
                ],
            ],
        ]);
    }

    public function test_product_history_api_with_filters(): void
    {
        ProductHistory::factory()->create([
            'product_id' => $this->product->id,
            'action' => 'price_changed',
        ]);
        
        ProductHistory::factory()->create([
            'product_id' => $this->product->id,
            'action' => 'stock_updated',
        ]);

        $response = $this->getJson("/api/products/{$this->product->slug}/history?action=price_changed");

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.action', 'price_changed');
    }

    public function test_product_history_api_pagination(): void
    {
        ProductHistory::factory()->count(25)->create([
            'product_id' => $this->product->id,
        ]);

        $response = $this->getJson("/api/products/{$this->product->slug}/history?per_page=10");

        $response->assertStatus(200);
        $response->assertJsonCount(10, 'data');
        $response->assertJsonStructure([
            'data',
            'links',
            'meta',
        ]);
    }

    public function test_authenticated_user_can_view_product_history(): void
    {
        $this->actingAs($this->user);

        ProductHistory::factory()->create([
            'product_id' => $this->product->id,
        ]);

        $response = $this->get(route('localized.products.history', [
            'locale' => 'lt',
            'product' => $this->product->slug,
        ]));

        $response->assertStatus(200);
    }

    public function test_product_history_creation_through_product_update(): void
    {
        $this->actingAs($this->user);

        $oldPrice = $this->product->price;
        $newPrice = $oldPrice + 50;

        $response = $this->patch(route('admin.products.update', $this->product), [
            'price' => $newPrice,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('product_histories', [
            'product_id' => $this->product->id,
            'user_id' => $this->user->id,
            'action' => 'updated',
            'field_name' => 'price',
            'old_value' => json_encode($oldPrice),
            'new_value' => json_encode($newPrice),
        ]);
    }

    public function test_product_history_creation_through_product_creation(): void
    {
        $this->actingAs($this->user);

        $productData = [
            'name' => 'New Test Product',
            'sku' => 'TEST-001',
            'price' => 99.99,
            'description' => 'Test product description',
        ];

        $response = $this->post(route('admin.products.store'), $productData);

        $response->assertRedirect();

        $product = Product::where('sku', 'TEST-001')->first();
        
        $this->assertDatabaseHas('product_histories', [
            'product_id' => $product->id,
            'user_id' => $this->user->id,
            'action' => 'created',
        ]);
    }

    public function test_product_history_with_different_locales(): void
    {
        $history = ProductHistory::factory()->create([
            'product_id' => $this->product->id,
            'description' => 'Test description',
        ]);

        // Test Lithuanian locale
        $response = $this->get(route('localized.products.history', [
            'locale' => 'lt',
            'product' => $this->product->slug,
        ]));

        $response->assertStatus(200);

        // Test English locale
        $response = $this->get(route('localized.products.history', [
            'locale' => 'en',
            'product' => $this->product->slug,
        ]));

        $response->assertStatus(200);
    }

    public function test_product_history_search_functionality(): void
    {
        ProductHistory::factory()->create([
            'product_id' => $this->product->id,
            'description' => 'Price increase due to market changes',
        ]);
        
        ProductHistory::factory()->create([
            'product_id' => $this->product->id,
            'description' => 'Stock updated after inventory check',
        ]);

        $response = $this->getJson("/api/products/{$this->product->slug}/history?search=price");

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.description', 'Price increase due to market changes');
    }

    public function test_product_history_date_range_filter(): void
    {
        $oldHistory = ProductHistory::factory()->create([
            'product_id' => $this->product->id,
            'created_at' => now()->subDays(10),
        ]);
        
        $recentHistory = ProductHistory::factory()->create([
            'product_id' => $this->product->id,
            'created_at' => now()->subDays(2),
        ]);

        $response = $this->getJson("/api/products/{$this->product->slug}/history?date_from=" . now()->subDays(5)->toDateString());

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.id', $recentHistory->id);
    }

    public function test_product_history_export_functionality(): void
    {
        ProductHistory::factory()->count(5)->create([
            'product_id' => $this->product->id,
        ]);

        $response = $this->get("/api/products/{$this->product->slug}/history/export");

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_product_history_statistics(): void
    {
        ProductHistory::factory()->create([
            'product_id' => $this->product->id,
            'action' => 'price_changed',
        ]);
        
        ProductHistory::factory()->create([
            'product_id' => $this->product->id,
            'action' => 'stock_updated',
        ]);
        
        ProductHistory::factory()->create([
            'product_id' => $this->product->id,
            'action' => 'price_changed',
        ]);

        $response = $this->getJson("/api/products/{$this->product->slug}/history/statistics");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'total_changes',
            'changes_by_action',
            'changes_by_field',
            'recent_activity',
        ]);
        
        $response->assertJsonPath('total_changes', 3);
        $response->assertJsonPath('changes_by_action.price_changed', 2);
        $response->assertJsonPath('changes_by_action.stock_updated', 1);
    }
}
