<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\UserBehavior;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * UserBehaviorResource Feature Test
 *
 * Comprehensive test suite for UserBehaviorResource functionality including CRUD operations,
 * filtering, actions, and relations in Filament admin panel.
 */
final class UserBehaviorResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    private UserBehavior $userBehavior;

    private Product $product;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);

        $this->product = Product::factory()->create();
        $this->category = Category::factory()->create();

        $this->userBehavior = UserBehavior::factory()->create([
            'user_id' => $this->adminUser->id,
            'product_id' => $this->product->id,
            'category_id' => $this->category->id,
            'behavior_type' => 'view',
            'session_id' => 'test-session-123',
            'referrer' => 'https://example.com',
            'user_agent' => 'Mozilla/5.0 (Test Browser)',
            'ip_address' => '192.168.1.1',
            'metadata' => ['test_key' => 'test_value'],
        ]);
    }

    public function test_can_view_user_behaviors_list(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->get('/admin/user-behaviors');

        $response->assertOk();
        $response->assertSee('User Behaviors');
        $response->assertSee($this->adminUser->name);
        $response->assertSee('view');
    }

    public function test_can_create_user_behavior(): void
    {
        $this->actingAs($this->adminUser);

        $newUser = User::factory()->create();
        $newProduct = Product::factory()->create();

        $response = $this->post('/admin/user-behaviors', [
            'user_id' => $newUser->id,
            'behavior_type' => 'click',
            'product_id' => $newProduct->id,
            'session_id' => 'new-session-456',
            'referrer' => 'https://google.com',
            'user_agent' => 'Mozilla/5.0 (New Browser)',
            'ip_address' => '192.168.1.2',
            'metadata' => ['new_key' => 'new_value'],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('user_behaviors', [
            'user_id' => $newUser->id,
            'behavior_type' => 'click',
            'product_id' => $newProduct->id,
            'session_id' => 'new-session-456',
        ]);
    }

    public function test_can_update_user_behavior(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->put("/admin/user-behaviors/{$this->userBehavior->id}", [
            'user_id' => $this->userBehavior->user_id,
            'behavior_type' => 'purchase',
            'product_id' => $this->userBehavior->product_id,
            'category_id' => $this->userBehavior->category_id,
            'session_id' => 'updated-session-789',
            'referrer' => 'https://updated.com',
            'user_agent' => 'Mozilla/5.0 (Updated Browser)',
            'ip_address' => '192.168.1.3',
            'metadata' => ['updated_key' => 'updated_value'],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('user_behaviors', [
            'id' => $this->userBehavior->id,
            'behavior_type' => 'purchase',
            'session_id' => 'updated-session-789',
        ]);
    }

    public function test_can_delete_user_behavior(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->delete("/admin/user-behaviors/{$this->userBehavior->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('user_behaviors', [
            'id' => $this->userBehavior->id,
        ]);
    }

    public function test_can_filter_by_behavior_type(): void
    {
        $this->actingAs($this->adminUser);

        // Create additional behaviors with different types
        UserBehavior::factory()->create([
            'user_id' => $this->adminUser->id,
            'behavior_type' => 'click',
        ]);

        UserBehavior::factory()->create([
            'user_id' => $this->adminUser->id,
            'behavior_type' => 'purchase',
        ]);

        $response = $this->get('/admin/user-behaviors?tableFilters[behavior_type][values][0]=view');

        $response->assertOk();
        $response->assertSee('view');
        $response->assertDontSee('click');
        $response->assertDontSee('purchase');
    }

    public function test_can_filter_by_user(): void
    {
        $this->actingAs($this->adminUser);

        $anotherUser = User::factory()->create();
        UserBehavior::factory()->create([
            'user_id' => $anotherUser->id,
            'behavior_type' => 'search',
        ]);

        $response = $this->get("/admin/user-behaviors?tableFilters[user_id][value]={$this->adminUser->id}");

        $response->assertOk();
        $response->assertSee($this->adminUser->name);
        $response->assertDontSee($anotherUser->name);
    }

    public function test_can_filter_by_date_range(): void
    {
        $this->actingAs($this->adminUser);

        // Create behavior from yesterday
        $yesterday = now()->subDay();
        UserBehavior::factory()->create([
            'user_id' => $this->adminUser->id,
            'created_at' => $yesterday,
        ]);

        $response = $this->get('/admin/user-behaviors?tableFilters[created_at][created_from]='.now()->format('Y-m-d'));

        $response->assertOk();
        $response->assertSee($this->userBehavior->behavior_type);
    }

    public function test_can_group_by_behavior_type(): void
    {
        $this->actingAs($this->adminUser);

        UserBehavior::factory()->create([
            'user_id' => $this->adminUser->id,
            'behavior_type' => 'click',
        ]);

        $response = $this->get('/admin/user-behaviors?tableGroup=behavior_type');

        $response->assertOk();
        $response->assertSee('view');
        $response->assertSee('click');
    }

    public function test_can_group_by_user(): void
    {
        $this->actingAs($this->adminUser);

        $anotherUser = User::factory()->create();
        UserBehavior::factory()->create([
            'user_id' => $anotherUser->id,
            'behavior_type' => 'search',
        ]);

        $response = $this->get('/admin/user-behaviors?tableGroup=user.name');

        $response->assertOk();
        $response->assertSee($this->adminUser->name);
        $response->assertSee($anotherUser->name);
    }

    public function test_can_export_analytics(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->post("/admin/user-behaviors/{$this->userBehavior->id}/actions/export-analytics");

        $response->assertRedirect();
    }

    public function test_can_analyze_user_behavior(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->post("/admin/user-behaviors/{$this->userBehavior->id}/actions/analyze");

        $response->assertRedirect();
    }

    public function test_can_bulk_delete_user_behaviors(): void
    {
        $this->actingAs($this->adminUser);

        $behavior2 = UserBehavior::factory()->create([
            'user_id' => $this->adminUser->id,
        ]);

        $response = $this->post('/admin/user-behaviors/bulk-actions/delete', [
            'records' => [$this->userBehavior->id, $behavior2->id],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseMissing('user_behaviors', [
            'id' => $this->userBehavior->id,
        ]);
        $this->assertDatabaseMissing('user_behaviors', [
            'id' => $behavior2->id,
        ]);
    }

    public function test_can_bulk_export_analytics(): void
    {
        $this->actingAs($this->adminUser);

        $behavior2 = UserBehavior::factory()->create([
            'user_id' => $this->adminUser->id,
        ]);

        $response = $this->post('/admin/user-behaviors/bulk-actions/export-analytics', [
            'records' => [$this->userBehavior->id, $behavior2->id],
        ]);

        $response->assertRedirect();
    }

    public function test_can_bulk_analyze_selected(): void
    {
        $this->actingAs($this->adminUser);

        $behavior2 = UserBehavior::factory()->create([
            'user_id' => $this->adminUser->id,
        ]);

        $response = $this->post('/admin/user-behaviors/bulk-actions/analyze-selected', [
            'records' => [$this->userBehavior->id, $behavior2->id],
        ]);

        $response->assertRedirect();
    }

    public function test_can_generate_insights(): void
    {
        $this->actingAs($this->adminUser);

        $behavior2 = UserBehavior::factory()->create([
            'user_id' => $this->adminUser->id,
        ]);

        $response = $this->post('/admin/user-behaviors/bulk-actions/generate-insights', [
            'records' => [$this->userBehavior->id, $behavior2->id],
        ]);

        $response->assertRedirect();
    }

    public function test_can_view_user_journey(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->post("/admin/user-behaviors/{$this->userBehavior->id}/actions/view-user-journey");

        $response->assertRedirect();
    }

    public function test_can_view_session_details(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->post("/admin/user-behaviors/{$this->userBehavior->id}/actions/view-session-details");

        $response->assertRedirect();
    }

    public function test_can_access_analytics_dashboard(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->get('/admin/user-behaviors/analytics');

        $response->assertOk();
    }

    public function test_can_export_all_data(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->post('/admin/user-behaviors/actions/export-all');

        $response->assertRedirect();
    }

    public function test_requires_authentication(): void
    {
        $response = $this->get('/admin/user-behaviors');

        $response->assertRedirect('/login');
    }

    public function test_requires_admin_permissions(): void
    {
        $regularUser = User::factory()->create([
            'is_admin' => false,
        ]);

        $this->actingAs($regularUser);

        $response = $this->get('/admin/user-behaviors');

        $response->assertForbidden();
    }

    public function test_behavior_type_validation(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->post('/admin/user-behaviors', [
            'user_id' => $this->adminUser->id,
            'behavior_type' => 'invalid_type',
        ]);

        $response->assertSessionHasErrors(['behavior_type']);
    }

    public function test_user_relationship_works(): void
    {
        $this->actingAs($this->adminUser);

        $this->assertEquals($this->adminUser->id, $this->userBehavior->user->id);
        $this->assertEquals($this->adminUser->name, $this->userBehavior->user->name);
    }

    public function test_product_relationship_works(): void
    {
        $this->actingAs($this->adminUser);

        $this->assertEquals($this->product->id, $this->userBehavior->product->id);
        $this->assertEquals($this->product->name, $this->userBehavior->product->name);
    }

    public function test_category_relationship_works(): void
    {
        $this->actingAs($this->adminUser);

        $this->assertEquals($this->category->id, $this->userBehavior->category->id);
        $this->assertEquals($this->category->name, $this->userBehavior->category->name);
    }

    public function test_metadata_is_casted_to_array(): void
    {
        $this->actingAs($this->adminUser);

        $this->assertIsArray($this->userBehavior->metadata);
        $this->assertEquals('test_value', $this->userBehavior->metadata['test_key']);
    }

    public function test_can_search_by_user_name(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->get('/admin/user-behaviors?search='.$this->adminUser->name);

        $response->assertOk();
        $response->assertSee($this->adminUser->name);
    }

    public function test_can_search_by_product_name(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->get('/admin/user-behaviors?search='.$this->product->name);

        $response->assertOk();
        $response->assertSee($this->product->name);
    }

    public function test_can_search_by_behavior_type(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->get('/admin/user-behaviors?search=view');

        $response->assertOk();
        $response->assertSee('view');
    }

    public function test_can_search_by_ip_address(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->get('/admin/user-behaviors?search=192.168.1.1');

        $response->assertOk();
        $response->assertSee('192.168.1.1');
    }

    public function test_can_sort_by_created_at(): void
    {
        $this->actingAs($this->adminUser);

        $olderBehavior = UserBehavior::factory()->create([
            'user_id' => $this->adminUser->id,
            'created_at' => now()->subDays(2),
        ]);

        $response = $this->get('/admin/user-behaviors?sort=created_at&direction=desc');

        $response->assertOk();
    }

    public function test_can_sort_by_user_name(): void
    {
        $this->actingAs($this->adminUser);

        $anotherUser = User::factory()->create(['name' => 'Adam Smith']);
        UserBehavior::factory()->create([
            'user_id' => $anotherUser->id,
        ]);

        $response = $this->get('/admin/user-behaviors?sort=user.name&direction=asc');

        $response->assertOk();
    }

    public function test_can_toggle_columns(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->get('/admin/user-behaviors');

        $response->assertOk();
        // Test that toggleable columns are present
        $response->assertSee('Product');
        $response->assertSee('Category');
        $response->assertSee('Session ID');
        $response->assertSee('Referrer');
        $response->assertSee('IP Address');
    }

    public function test_recent_behaviors_filter(): void
    {
        $this->actingAs($this->adminUser);

        // Create behavior from 10 days ago
        UserBehavior::factory()->create([
            'user_id' => $this->adminUser->id,
            'created_at' => now()->subDays(10),
        ]);

        $response = $this->get('/admin/user-behaviors?tableFilters[recent_behaviors][value]=1');

        $response->assertOk();
        $response->assertSee($this->userBehavior->behavior_type);
    }

    public function test_today_filter(): void
    {
        $this->actingAs($this->adminUser);

        // Create behavior from yesterday
        UserBehavior::factory()->create([
            'user_id' => $this->adminUser->id,
            'created_at' => now()->subDay(),
        ]);

        $response = $this->get('/admin/user-behaviors?tableFilters[today][value]=1');

        $response->assertOk();
        $response->assertSee($this->userBehavior->behavior_type);
    }

    public function test_this_week_filter(): void
    {
        $this->actingAs($this->adminUser);

        // Create behavior from 2 weeks ago
        UserBehavior::factory()->create([
            'user_id' => $this->adminUser->id,
            'created_at' => now()->subWeeks(2),
        ]);

        $response = $this->get('/admin/user-behaviors?tableFilters[this_week][value]=1');

        $response->assertOk();
        $response->assertSee($this->userBehavior->behavior_type);
    }

    public function test_this_month_filter(): void
    {
        $this->actingAs($this->adminUser);

        // Create behavior from last month
        UserBehavior::factory()->create([
            'user_id' => $this->adminUser->id,
            'created_at' => now()->subMonth(),
        ]);

        $response = $this->get('/admin/user-behaviors?tableFilters[this_month][value]=1');

        $response->assertOk();
        $response->assertSee($this->userBehavior->behavior_type);
    }

    public function test_has_product_filter(): void
    {
        $this->actingAs($this->adminUser);

        // Create behavior without product
        UserBehavior::factory()->create([
            'user_id' => $this->adminUser->id,
            'product_id' => null,
        ]);

        $response = $this->get('/admin/user-behaviors?tableFilters[has_product][value]=1');

        $response->assertOk();
        $response->assertSee($this->userBehavior->behavior_type);
    }

    public function test_has_category_filter(): void
    {
        $this->actingAs($this->adminUser);

        // Create behavior without category
        UserBehavior::factory()->create([
            'user_id' => $this->adminUser->id,
            'category_id' => null,
        ]);

        $response = $this->get('/admin/user-behaviors?tableFilters[has_category][value]=1');

        $response->assertOk();
        $response->assertSee($this->userBehavior->behavior_type);
    }

    public function test_multiple_behavior_types_filter(): void
    {
        $this->actingAs($this->adminUser);

        UserBehavior::factory()->create([
            'user_id' => $this->adminUser->id,
            'behavior_type' => 'click',
        ]);

        UserBehavior::factory()->create([
            'user_id' => $this->adminUser->id,
            'behavior_type' => 'purchase',
        ]);

        $response = $this->get('/admin/user-behaviors?tableFilters[behavior_type][values][0]=view&tableFilters[behavior_type][values][1]=click');

        $response->assertOk();
        $response->assertSee('view');
        $response->assertSee('click');
        $response->assertDontSee('purchase');
    }
}
