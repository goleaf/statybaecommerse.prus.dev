<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ProductHistory;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ProductHistoryResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->actingAs(User::factory()->create());
    }

    public function test_can_create_product_history(): void
    {
        $product = Product::factory()->create();
        
        $historyData = [
            'product_id' => $product->id,
            'action' => 'price_changed',
            'old_value' => '100.00',
            'new_value' => '120.00',
            'description' => 'Price updated from 100 to 120',
            'user_id' => auth()->id(),
        ];

        $history = ProductHistory::create($historyData);

        $this->assertDatabaseHas('product_histories', [
            'product_id' => $product->id,
            'action' => 'price_changed',
            'old_value' => '100.00',
            'new_value' => '120.00',
        ]);

        $this->assertEquals('price_changed', $history->action);
        $this->assertEquals('100.00', $history->old_value);
        $this->assertEquals('120.00', $history->new_value);
    }

    public function test_can_filter_product_history_by_action(): void
    {
        $product = Product::factory()->create();
        
        ProductHistory::factory()->create([
            'product_id' => $product->id,
            'action' => 'price_changed',
        ]);
        
        ProductHistory::factory()->create([
            'product_id' => $product->id,
            'action' => 'stock_changed',
        ]);

        $priceChanges = ProductHistory::where('action', 'price_changed')->get();
        $stockChanges = ProductHistory::where('action', 'stock_changed')->get();

        $this->assertCount(1, $priceChanges);
        $this->assertCount(1, $stockChanges);
        $this->assertEquals('price_changed', $priceChanges->first()->action);
        $this->assertEquals('stock_changed', $stockChanges->first()->action);
    }

    public function test_can_filter_product_history_by_date(): void
    {
        $product = Product::factory()->create();
        
        ProductHistory::factory()->create([
            'product_id' => $product->id,
            'action' => 'created',
            'created_at' => now(),
        ]);
        
        ProductHistory::factory()->create([
            'product_id' => $product->id,
            'action' => 'updated',
            'created_at' => now()->subDays(2),
        ]);

        $todayHistory = ProductHistory::whereDate('created_at', today())->get();
        $oldHistory = ProductHistory::whereDate('created_at', '<', today())->get();

        $this->assertCount(1, $todayHistory);
        $this->assertCount(1, $oldHistory);
        $this->assertEquals('created', $todayHistory->first()->action);
        $this->assertEquals('updated', $oldHistory->first()->action);
    }

    public function test_can_get_product_history_with_product_relationship(): void
    {
        $product = Product::factory()->create();
        
        $history = ProductHistory::factory()->create([
            'product_id' => $product->id,
            'action' => 'status_changed',
        ]);

        $this->assertInstanceOf(Product::class, $history->product);
        $this->assertEquals($product->id, $history->product->id);
    }

    public function test_can_get_product_history_with_user_relationship(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        
        $history = ProductHistory::factory()->create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'action' => 'deleted',
        ]);

        $this->assertInstanceOf(User::class, $history->user);
        $this->assertEquals($user->id, $history->user->id);
    }
}
