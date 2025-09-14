<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Product;
use App\Models\ProductHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ProductHistoryTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->product = Product::factory()->create();
    }

    public function test_can_create_product_history_entry(): void
    {
        $history = ProductHistory::create([
            'product_id' => $this->product->id,
            'user_id' => $this->user->id,
            'action' => 'created',
            'field_name' => 'name',
            'old_value' => null,
            'new_value' => 'Test Product',
            'description' => 'Product was created',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Agent',
            'metadata' => ['test' => 'data'],
            'causer_type' => User::class,
            'causer_id' => $this->user->id,
        ]);

        $this->assertInstanceOf(ProductHistory::class, $history);
        $this->assertEquals($this->product->id, $history->product_id);
        $this->assertEquals($this->user->id, $history->user_id);
        $this->assertEquals('created', $history->action);
        $this->assertEquals('name', $history->field_name);
        $this->assertNull($history->old_value);
        $this->assertEquals('Test Product', $history->new_value);
    }

    public function test_belongs_to_product(): void
    {
        $history = ProductHistory::factory()->create([
            'product_id' => $this->product->id,
        ]);

        $this->assertInstanceOf(Product::class, $history->product);
        $this->assertEquals($this->product->id, $history->product->id);
    }

    public function test_belongs_to_user(): void
    {
        $history = ProductHistory::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $this->assertInstanceOf(User::class, $history->user);
        $this->assertEquals($this->user->id, $history->user->id);
    }

    public function test_scope_for_product(): void
    {
        $product2 = Product::factory()->create();
        
        ProductHistory::factory()->create(['product_id' => $this->product->id]);
        ProductHistory::factory()->create(['product_id' => $product2->id]);
        ProductHistory::factory()->create(['product_id' => $this->product->id]);

        $histories = ProductHistory::forProduct($this->product->id)->get();

        $this->assertCount(2, $histories);
        $this->assertTrue($histories->every(fn($h) => $h->product_id === $this->product->id));
    }

    public function test_scope_by_user(): void
    {
        $user2 = User::factory()->create();
        
        ProductHistory::factory()->create(['user_id' => $this->user->id]);
        ProductHistory::factory()->create(['user_id' => $user2->id]);
        ProductHistory::factory()->create(['user_id' => $this->user->id]);

        $histories = ProductHistory::byUser($this->user->id)->get();

        $this->assertCount(2, $histories);
        $this->assertTrue($histories->every(fn($h) => $h->user_id === $this->user->id));
    }

    public function test_scope_by_action(): void
    {
        ProductHistory::factory()->create(['action' => 'created']);
        ProductHistory::factory()->create(['action' => 'updated']);
        ProductHistory::factory()->create(['action' => 'created']);

        $histories = ProductHistory::byAction('created')->get();

        $this->assertCount(2, $histories);
        $this->assertTrue($histories->every(fn($h) => $h->action === 'created'));
    }

    public function test_scope_by_field(): void
    {
        ProductHistory::factory()->create(['field_name' => 'price']);
        ProductHistory::factory()->create(['field_name' => 'name']);
        ProductHistory::factory()->create(['field_name' => 'price']);

        $histories = ProductHistory::byField('price')->get();

        $this->assertCount(2, $histories);
        $this->assertTrue($histories->every(fn($h) => $h->field_name === 'price'));
    }

    public function test_scope_recent(): void
    {
        $oldHistory = ProductHistory::factory()->create([
            'created_at' => now()->subDays(35),
        ]);
        
        $recentHistory = ProductHistory::factory()->create([
            'created_at' => now()->subDays(15),
        ]);

        $histories = ProductHistory::recent(30)->get();

        $this->assertCount(1, $histories);
        $this->assertEquals($recentHistory->id, $histories->first()->id);
    }

    public function test_formatted_old_value_attribute(): void
    {
        $history = ProductHistory::factory()->create([
            'old_value' => ['key' => 'value'],
        ]);

        $this->assertStringContainsString('key', $history->formatted_old_value);
        $this->assertStringContainsString('value', $history->formatted_old_value);
    }

    public function test_formatted_new_value_attribute(): void
    {
        $history = ProductHistory::factory()->create([
            'new_value' => true,
        ]);

        $this->assertStringContainsString('Yes', $history->formatted_new_value);
    }

    public function test_action_display_attribute(): void
    {
        $history = ProductHistory::factory()->create(['action' => 'created']);
        $this->assertNotEmpty($history->action_display);

        $history = ProductHistory::factory()->create(['action' => 'price_changed']);
        $this->assertNotEmpty($history->action_display);
    }

    public function test_field_display_attribute(): void
    {
        $history = ProductHistory::factory()->create(['field_name' => 'price']);
        $this->assertNotEmpty($history->field_display);
    }

    public function test_change_summary_attribute(): void
    {
        $history = ProductHistory::factory()->create([
            'action' => 'created',
            'field_name' => 'name',
        ]);
        
        $this->assertNotEmpty($history->change_summary);
        $this->assertStringContainsString('created', $history->change_summary);
    }

    public function test_is_significant_change(): void
    {
        $significantHistory = ProductHistory::factory()->create(['field_name' => 'price']);
        $this->assertTrue($significantHistory->isSignificantChange());

        $insignificantHistory = ProductHistory::factory()->create(['field_name' => 'description']);
        $this->assertFalse($insignificantHistory->isSignificantChange());
    }

    public function test_get_change_impact(): void
    {
        $highImpact = ProductHistory::factory()->create(['field_name' => 'price']);
        $this->assertEquals('high', $highImpact->getChangeImpact());

        $mediumImpact = ProductHistory::factory()->create(['field_name' => 'status']);
        $this->assertEquals('medium', $mediumImpact->getChangeImpact());

        $lowImpact = ProductHistory::factory()->create(['field_name' => 'description']);
        $this->assertEquals('low', $lowImpact->getChangeImpact());
    }

    public function test_create_history_entry_static_method(): void
    {
        $history = ProductHistory::createHistoryEntry(
            product: $this->product,
            action: 'updated',
            fieldName: 'price',
            oldValue: 100,
            newValue: 120,
            description: 'Price updated',
            user: $this->user
        );

        $this->assertInstanceOf(ProductHistory::class, $history);
        $this->assertEquals($this->product->id, $history->product_id);
        $this->assertEquals($this->user->id, $history->user_id);
        $this->assertEquals('updated', $history->action);
        $this->assertEquals('price', $history->field_name);
        $this->assertEquals(100, $history->old_value);
        $this->assertEquals(120, $history->new_value);
        $this->assertNotNull($history->ip_address);
        $this->assertNotNull($history->user_agent);
        $this->assertArrayHasKey('product_name', $history->metadata);
        $this->assertArrayHasKey('product_sku', $history->metadata);
    }

    public function test_metadata_casting(): void
    {
        $metadata = ['key' => 'value', 'number' => 123];
        
        $history = ProductHistory::factory()->create(['metadata' => $metadata]);

        $this->assertIsArray($history->metadata);
        $this->assertEquals('value', $history->metadata['key']);
        $this->assertEquals(123, $history->metadata['number']);
    }

    public function test_old_value_casting(): void
    {
        $oldValue = ['old' => 'data'];
        
        $history = ProductHistory::factory()->create(['old_value' => $oldValue]);

        $this->assertIsArray($history->old_value);
        $this->assertEquals('data', $history->old_value['old']);
    }

    public function test_new_value_casting(): void
    {
        $newValue = ['new' => 'data'];
        
        $history = ProductHistory::factory()->create(['new_value' => $newValue]);

        $this->assertIsArray($history->new_value);
        $this->assertEquals('data', $history->new_value['new']);
    }
}
