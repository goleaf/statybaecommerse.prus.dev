<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Product;
use App\Models\ProductHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ProductHistoryModelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Authenticate as admin to bypass UserOwnedScope
        $this->actingAs(User::factory()->admin()->create());
    }

    public function test_product_history_belongs_to_product(): void
    {
        $product = Product::factory()->create();
        $history = ProductHistory::factory()->create(['product_id' => $product->id]);

        $this->assertInstanceOf(Product::class, $history->product);
        $this->assertEquals($product->id, $history->product->id);
    }

    public function test_product_history_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $history = ProductHistory::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $history->user);
        $this->assertEquals($user->id, $history->user->id);
    }

    public function test_product_history_has_morph_to_causer(): void
    {
        $user = User::factory()->create();
        $history = ProductHistory::factory()->create([
            'causer_type' => User::class,
            'causer_id'   => $user->id,
        ]);

        $this->assertInstanceOf(User::class, $history->causer);
        $this->assertEquals($user->id, $history->causer->id);
    }

    public function test_fillable_attributes_are_mass_assignable(): void
    {
        $product = Product::factory()->create();
        $user = User::factory()->create();

        $data = [
            'product_id'  => $product->id,
            'user_id'     => $user->id,
            'action'      => 'updated',
            'field_name'  => 'price',
            'old_value'   => ['price' => '10.00'],
            'new_value'   => ['price' => '12.00'],
            'description' => 'Price updated',
            'ip_address'  => '127.0.0.1',
            'user_agent'  => 'Mozilla/5.0',
            'metadata'    => ['source' => 'admin_panel'],
            'causer_type' => User::class,
            'causer_id'   => $user->id,
        ];

        $history = ProductHistory::create($data);

        $this->assertEquals($product->id, $history->product_id);
        $this->assertEquals($user->id, $history->user_id);
        $this->assertEquals('updated', $history->action);
        $this->assertEquals('price', $history->field_name);
    }

    public function test_casts_array_and_json_correctly(): void
    {
        $history = ProductHistory::factory()->create([
            'metadata'  => ['key' => 'value'],
            'old_value' => ['price' => '10.00'],
            'new_value' => ['price' => '12.00'],
        ]);

        $this->assertIsArray($history->metadata);
        $this->assertEquals(['key' => 'value'], $history->metadata);
        $this->assertEquals(['price' => '10.00'], $history->old_value);
        $this->assertEquals(['price' => '12.00'], $history->new_value);
    }

    public function test_causer_is_set_automatically_on_create(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $history = ProductHistory::factory()->create([
            'causer_type' => null,
            'causer_id'   => null,
        ]);

        $this->assertEquals(User::class, $history->causer_type);
        $this->assertEquals($user->id, $history->causer_id);
    }

    public function test_scope_for_product_filters_by_product_id(): void
    {
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        ProductHistory::factory()->count(3)->create(['product_id' => $product1->id]);
        ProductHistory::factory()->count(2)->create(['product_id' => $product2->id]);

        $histories = ProductHistory::forProduct($product1->id)->get();

        $this->assertCount(3, $histories);
        $this->assertTrue($histories->every(fn ($h) => $h->product_id === $product1->id));
    }

    public function test_scope_by_user_filters_by_user_id(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        ProductHistory::factory()->count(3)->create(['user_id' => $user1->id]);
        ProductHistory::factory()->count(2)->create(['user_id' => $user2->id]);

        $histories = ProductHistory::byUser($user1->id)->get();

        $this->assertCount(3, $histories);
        $this->assertTrue($histories->every(fn ($h) => $h->user_id === $user1->id));
    }

    public function test_scope_by_action_filters_by_action_type(): void
    {
        ProductHistory::factory()->count(3)->create(['action' => 'created']);
        ProductHistory::factory()->count(2)->create(['action' => 'updated']);
        ProductHistory::factory()->count(1)->create(['action' => 'deleted']);

        $histories = ProductHistory::byAction('created')->get();

        $this->assertCount(3, $histories);
        $this->assertTrue($histories->every(fn ($h) => $h->action === 'created'));
    }

    public function test_scope_by_field_filters_by_field_name(): void
    {
        ProductHistory::factory()->count(3)->create(['field_name' => 'price']);
        ProductHistory::factory()->count(2)->create(['field_name' => 'stock_quantity']);

        $histories = ProductHistory::byField('price')->get();

        $this->assertCount(3, $histories);
        $this->assertTrue($histories->every(fn ($h) => $h->field_name === 'price'));
    }

    public function test_scope_recent_filters_by_days(): void
    {
        ProductHistory::factory()->create(['created_at' => now()->subDays(5)]);
        ProductHistory::factory()->create(['created_at' => now()->subDays(10)]);
        ProductHistory::factory()->create(['created_at' => now()->subDays(35)]);
        ProductHistory::factory()->create(['created_at' => now()->subDays(60)]);

        $recentHistories = ProductHistory::recent(30)->get();

        $this->assertCount(2, $recentHistories);
        $this->assertTrue($recentHistories->every(fn ($h) => $h->created_at >= now()->subDays(30)));
    }

    public function test_formatted_old_value_accessor_handles_null(): void
    {
        $history = ProductHistory::factory()->create(['old_value' => null]);

        $this->assertEquals(__('admin.common.none'), $history->formatted_old_value);
    }

    public function test_formatted_old_value_accessor_handles_array(): void
    {
        $arrayValue = ['price' => '10.00', 'currency' => 'EUR'];
        $history = ProductHistory::factory()->create(['old_value' => $arrayValue]);

        $this->assertEquals(json_encode($arrayValue, JSON_UNESCAPED_UNICODE), $history->formatted_old_value);
    }

    public function test_formatted_old_value_accessor_handles_boolean(): void
    {
        $history1 = ProductHistory::factory()->create(['old_value' => true]);
        $history2 = ProductHistory::factory()->create(['old_value' => false]);

        $this->assertEquals(__('admin.common.yes'), $history1->formatted_old_value);
        $this->assertEquals(__('admin.common.no'), $history2->formatted_old_value);
    }

    public function test_formatted_new_value_accessor_handles_string(): void
    {
        $history = ProductHistory::factory()->create(['new_value' => 'Simple text value']);

        $this->assertEquals('Simple text value', $history->formatted_new_value);
    }

    public function test_action_display_accessor_returns_translated_action(): void
    {
        $history = ProductHistory::factory()->create(['action' => 'created']);

        $actionDisplay = $history->action_display;

        $this->assertIsString($actionDisplay);
        $this->assertNotEmpty($actionDisplay);
    }

    public function test_action_display_accessor_returns_raw_action_for_unknown(): void
    {
        $history = ProductHistory::factory()->create(['action' => 'unknown_action']);

        $this->assertEquals('unknown_action', $history->action_display);
    }

    public function test_field_display_accessor_returns_translated_field(): void
    {
        $history = ProductHistory::factory()->create(['field_name' => 'price']);

        $fieldDisplay = $history->field_display;

        $this->assertIsString($fieldDisplay);
        $this->assertNotEmpty($fieldDisplay);
    }

    public function test_change_summary_accessor_for_created_action(): void
    {
        $history = ProductHistory::factory()->create([
            'action'     => 'created',
            'field_name' => 'name',
        ]);

        $summary = $history->change_summary;

        $this->assertIsString($summary);
        $this->assertNotEmpty($summary);
    }

    public function test_change_summary_accessor_for_deleted_action(): void
    {
        $history = ProductHistory::factory()->create([
            'action'     => 'deleted',
            'field_name' => 'status',
        ]);

        $summary = $history->change_summary;

        $this->assertIsString($summary);
        $this->assertNotEmpty($summary);
    }

    public function test_change_summary_accessor_for_updated_action(): void
    {
        $history = ProductHistory::factory()->create([
            'action'     => 'updated',
            'field_name' => 'price',
            'old_value'  => '10.00',
            'new_value'  => '12.00',
        ]);

        $summary = $history->change_summary;

        $this->assertIsString($summary);
        $this->assertNotEmpty($summary);
    }

    public function test_is_significant_change_returns_true_for_significant_fields(): void
    {
        $history1 = ProductHistory::factory()->create(['field_name' => 'price']);
        $history2 = ProductHistory::factory()->create(['field_name' => 'stock_quantity']);
        $history3 = ProductHistory::factory()->create(['field_name' => 'status']);

        $this->assertTrue($history1->isSignificantChange());
        $this->assertTrue($history2->isSignificantChange());
        $this->assertTrue($history3->isSignificantChange());
    }

    public function test_is_significant_change_returns_false_for_non_significant_fields(): void
    {
        $history = ProductHistory::factory()->create(['field_name' => 'meta_title']);

        $this->assertFalse($history->isSignificantChange());
    }

    public function test_get_change_impact_returns_high_for_price_changes(): void
    {
        $history1 = ProductHistory::factory()->create(['field_name' => 'price']);
        $history2 = ProductHistory::factory()->create(['field_name' => 'sale_price']);
        $history3 = ProductHistory::factory()->create(['field_name' => 'stock_quantity']);

        $this->assertEquals('high', $history1->getChangeImpact());
        $this->assertEquals('high', $history2->getChangeImpact());
        $this->assertEquals('high', $history3->getChangeImpact());
    }

    public function test_get_change_impact_returns_medium_for_status_changes(): void
    {
        $history = ProductHistory::factory()->create(['field_name' => 'status']);

        $this->assertEquals('medium', $history->getChangeImpact());
    }

    public function test_get_change_impact_returns_low_for_non_significant_changes(): void
    {
        $history = ProductHistory::factory()->create(['field_name' => 'meta_description']);

        $this->assertEquals('low', $history->getChangeImpact());
    }

    public function test_create_history_entry_static_method(): void
    {
        $product = Product::factory()->create();
        $user = User::factory()->create();

        $history = ProductHistory::createHistoryEntry(
            product: $product,
            action: 'price_changed',
            fieldName: 'price',
            oldValue: '10.00',
            newValue: '12.00',
            description: 'Price updated',
            user: $user
        );

        $this->assertInstanceOf(ProductHistory::class, $history);
        $this->assertEquals($product->id, $history->product_id);
        $this->assertEquals($user->id, $history->user_id);
        $this->assertEquals('price_changed', $history->action);
        $this->assertEquals('price', $history->field_name);
        $this->assertDatabaseHas('product_histories', [
            'id'     => $history->id,
            'action' => 'price_changed',
        ]);
    }

    public function test_create_history_entry_uses_authenticated_user_by_default(): void
    {
        $authUser = User::factory()->create();
        $this->actingAs($authUser);
        $product = Product::factory()->create();

        $history = ProductHistory::createHistoryEntry(
            product: $product,
            action: 'updated',
            fieldName: 'name',
            oldValue: 'Old Name',
            newValue: 'New Name'
        );

        $this->assertEquals($authUser->id, $history->user_id);
        $this->assertEquals($authUser->id, $history->causer_id);
    }

    public function test_timestamps_are_set_automatically(): void
    {
        $history = ProductHistory::factory()->create();

        $this->assertNotNull($history->created_at);
        $this->assertNotNull($history->updated_at);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $history->created_at);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $history->updated_at);
    }

    public function test_factory_created_state(): void
    {
        $history = ProductHistory::factory()->created()->create();

        $this->assertEquals('created', $history->action);
        $this->assertEquals('name', $history->field_name);
        $this->assertNull($history->old_value);
    }

    public function test_factory_updated_state(): void
    {
        $history = ProductHistory::factory()->updated()->create();

        $this->assertEquals('updated', $history->action);
        $this->assertNotNull($history->old_value);
        $this->assertNotNull($history->new_value);
    }

    public function test_factory_price_changed_state(): void
    {
        $history = ProductHistory::factory()->priceChanged()->create();

        $this->assertEquals('price_changed', $history->action);
        $this->assertEquals('price', $history->field_name);
        $this->assertArrayHasKey('price_change_percentage', $history->metadata);
    }

    public function test_factory_stock_updated_state(): void
    {
        $history = ProductHistory::factory()->stockUpdated()->create();

        $this->assertEquals('stock_updated', $history->action);
        $this->assertEquals('stock_quantity', $history->field_name);
        $this->assertArrayHasKey('stock_change', $history->metadata);
    }

    public function test_factory_status_changed_state(): void
    {
        $history = ProductHistory::factory()->statusChanged()->create();

        $this->assertEquals('status_changed', $history->action);
        $this->assertEquals('status', $history->field_name);
    }

    public function test_factory_for_product_state(): void
    {
        $product = Product::factory()->create();
        $history = ProductHistory::factory()->forProduct($product)->create();

        $this->assertEquals($product->id, $history->product_id);
    }

    public function test_factory_for_user_state(): void
    {
        $user = User::factory()->create();
        $history = ProductHistory::factory()->forUser($user)->create();

        $this->assertEquals($user->id, $history->user_id);
    }

    public function test_factory_significant_state(): void
    {
        $history = ProductHistory::factory()->significant()->create();

        $this->assertContains($history->field_name, ['price', 'sale_price', 'stock_quantity', 'status', 'is_visible']);
        $this->assertTrue($history->isSignificantChange());
    }

    public function test_factory_low_impact_state(): void
    {
        $history = ProductHistory::factory()->lowImpact()->create();

        $this->assertContains($history->field_name, ['meta_title', 'meta_description', 'tags', 'notes']);
        $this->assertFalse($history->isSignificantChange());
    }
}
