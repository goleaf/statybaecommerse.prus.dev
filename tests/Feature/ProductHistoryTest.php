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

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->admin()->create());
    }

    public function test_history_record_is_created_with_defaults(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $history = ProductHistory::create([
            'product_id' => $product->id,
            'user_id'    => $user->id,
            'action'     => 'updated',
            'field_name' => 'price',
            'old_value'  => ['price' => '10.00'],
            'new_value'  => ['price' => '12.00'],
        ]);

        $this->assertDatabaseHas('product_histories', [
            'id'         => $history->id,
            'product_id' => $product->id,
            'action'     => 'updated',
        ]);
        $this->assertSame('price', $history->field_name);
        $this->assertSame(['price' => '10.00'], $history->old_value);
        $this->assertSame(['price' => '12.00'], $history->new_value);
    }

    public function test_history_scopes_filter_by_product_and_action(): void
    {
        $product = Product::factory()->create();

        ProductHistory::factory()->count(2)->create([
            'product_id' => $product->id,
            'action'     => 'updated',
        ]);

        ProductHistory::factory()->create([
            'product_id' => $product->id,
            'action'     => 'created',
        ]);

        $filtered = ProductHistory::query()
            ->forProduct($product->id)
            ->byAction('updated')
            ->get();

        $this->assertCount(2, $filtered);
        $this->assertTrue($filtered->every(fn (ProductHistory $history) => $history->action === 'updated'));
    }

    public function test_recent_scope_limits_results(): void
    {
        $product = Product::factory()->create();

        ProductHistory::factory()->create([
            'product_id' => $product->id,
            'created_at' => now()->subDays(5),
        ]);

        ProductHistory::factory()->create([
            'product_id' => $product->id,
            'created_at' => now()->subDays(40),
        ]);

        $recent = ProductHistory::query()
            ->forProduct($product->id)
            ->recent(30)
            ->get();

        $this->assertCount(1, $recent);
        $this->assertTrue($recent->first()->created_at->greaterThan(now()->subDays(30)));
    }
}
