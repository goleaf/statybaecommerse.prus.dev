<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Models\News;
use App\Models\NewsTag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\TestCase;

final class NewsTagResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles and permissions
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        // Create admin user with proper permissions
        $this->admin = User::factory()->create([
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);

        // Assign admin role
        $this->admin->assignRole('administrator');
    }

    public function test_can_list_news_tags(): void
    {
        // Create test data
        NewsTag::factory()->count(5)->create();

        $this->actingAs($this->admin);

        $response = $this->get('/admin/news-tags');

        $response->assertStatus(200);
        $response->assertSee('News Tags');
    }

    public function test_can_create_news_tag(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get('/admin/news-tags/create');

        $response->assertStatus(200);
        $response->assertSee('Create News Tag');
    }

    public function test_can_store_news_tag(): void
    {
        $this->actingAs($this->admin);

        $newsTagData = [
            'name' => 'Test Tag',
            'slug' => 'test-tag',
            'description' => 'Test description',
            'is_visible' => true,
            'sort_order' => 1,
            'color' => '#3B82F6',
        ];

        $response = $this->post('/admin/news-tags', $newsTagData);

        $this->assertDatabaseHas('news_tags', [
            'name' => 'Test Tag',
            'slug' => 'test-tag',
            'is_visible' => true,
        ]);
    }

    public function test_can_view_news_tag(): void
    {
        $newsTag = NewsTag::factory()->create();

        $this->actingAs($this->admin);

        $response = $this->get("/admin/news-tags/{$newsTag->id}");

        $response->assertStatus(200);
        $response->assertSee($newsTag->name);
    }

    public function test_can_edit_news_tag(): void
    {
        $newsTag = NewsTag::factory()->create();

        $this->actingAs($this->admin);

        $response = $this->get("/admin/news-tags/{$newsTag->id}/edit");

        $response->assertStatus(200);
        $response->assertSee('Edit News Tag');
    }

    public function test_can_update_news_tag(): void
    {
        $newsTag = NewsTag::factory()->create();

        $this->actingAs($this->admin);

        $updateData = [
            'name' => 'Updated Tag',
            'slug' => 'updated-tag',
            'description' => 'Updated description',
            'is_visible' => false,
            'sort_order' => 2,
            'color' => '#10B981',
        ];

        $response = $this->put("/admin/news-tags/{$newsTag->id}", $updateData);

        $this->assertDatabaseHas('news_tags', [
            'id' => $newsTag->id,
            'name' => 'Updated Tag',
            'slug' => 'updated-tag',
            'is_visible' => false,
        ]);
    }

    public function test_can_delete_news_tag(): void
    {
        $newsTag = NewsTag::factory()->create();

        $this->actingAs($this->admin);

        $response = $this->delete("/admin/news-tags/{$newsTag->id}");

        $this->assertDatabaseMissing('news_tags', [
            'id' => $newsTag->id,
        ]);
    }

    public function test_can_filter_active_news_tags(): void
    {
        NewsTag::factory()->create(['is_visible' => true]);
        NewsTag::factory()->create(['is_visible' => false]);

        $this->actingAs($this->admin);

        $response = $this->get('/admin/news-tags?filter[is_visible]=1');

        $response->assertStatus(200);
    }

    public function test_can_filter_inactive_news_tags(): void
    {
        NewsTag::factory()->create(['is_visible' => true]);
        NewsTag::factory()->create(['is_visible' => false]);

        $this->actingAs($this->admin);

        $response = $this->get('/admin/news-tags?filter[inactive]=1');

        $response->assertStatus(200);
    }

    public function test_can_filter_news_tags_with_news(): void
    {
        $newsTag = NewsTag::factory()->create();
        $news = News::factory()->create();
        $news->tags()->attach($newsTag);

        $this->actingAs($this->admin);

        $response = $this->get('/admin/news-tags?filter[with_news]=1');

        $response->assertStatus(200);
    }

    public function test_can_filter_news_tags_without_news(): void
    {
        NewsTag::factory()->create();
        $newsTag = NewsTag::factory()->create();
        $news = News::factory()->create();
        $news->tags()->attach($newsTag);

        $this->actingAs($this->admin);

        $response = $this->get('/admin/news-tags?filter[without_news]=1');

        $response->assertStatus(200);
    }

    public function test_can_filter_news_tags_by_color(): void
    {
        NewsTag::factory()->create(['color' => '#3B82F6']);
        NewsTag::factory()->create(['color' => '#10B981']);

        $this->actingAs($this->admin);

        $response = $this->get('/admin/news-tags?filter[color]=#3B82F6');

        $response->assertStatus(200);
    }

    public function test_can_filter_recent_news_tags(): void
    {
        NewsTag::factory()->create(['created_at' => now()->subDays(1)]);
        NewsTag::factory()->create(['created_at' => now()->subDays(10)]);

        $this->actingAs($this->admin);

        $response = $this->get('/admin/news-tags?filter[recent]=1');

        $response->assertStatus(200);
    }

    public function test_can_search_news_tags(): void
    {
        NewsTag::factory()->create(['name' => 'Technology Tag']);
        NewsTag::factory()->create(['name' => 'Sports Tag']);

        $this->actingAs($this->admin);

        $response = $this->get('/admin/news-tags?search=Technology');

        $response->assertStatus(200);
    }

    public function test_can_sort_news_tags_by_name(): void
    {
        NewsTag::factory()->create(['name' => 'Z Tag']);
        NewsTag::factory()->create(['name' => 'A Tag']);

        $this->actingAs($this->admin);

        $response = $this->get('/admin/news-tags?sort=name');

        $response->assertStatus(200);
    }

    public function test_can_sort_news_tags_by_sort_order(): void
    {
        NewsTag::factory()->create(['sort_order' => 2]);
        NewsTag::factory()->create(['sort_order' => 1]);

        $this->actingAs($this->admin);

        $response = $this->get('/admin/news-tags?sort=sort_order');

        $response->assertStatus(200);
    }

    public function test_can_sort_news_tags_by_news_count(): void
    {
        $newsTag1 = NewsTag::factory()->create();
        $newsTag2 = NewsTag::factory()->create();

        $news = News::factory()->create();
        $news->tags()->attach($newsTag1);

        $this->actingAs($this->admin);

        $response = $this->get('/admin/news-tags?sort=news_count');

        $response->assertStatus(200);
    }

    public function test_can_activate_news_tag(): void
    {
        $newsTag = NewsTag::factory()->create(['is_visible' => false]);

        $this->actingAs($this->admin);

        $response = $this->post("/admin/news-tags/{$newsTag->id}/activate");

        $this->assertDatabaseHas('news_tags', [
            'id' => $newsTag->id,
            'is_visible' => true,
        ]);
    }

    public function test_can_deactivate_news_tag(): void
    {
        $newsTag = NewsTag::factory()->create(['is_visible' => true]);

        $this->actingAs($this->admin);

        $response = $this->post("/admin/news-tags/{$newsTag->id}/deactivate");

        $this->assertDatabaseHas('news_tags', [
            'id' => $newsTag->id,
            'is_visible' => false,
        ]);
    }

    public function test_can_duplicate_news_tag(): void
    {
        $newsTag = NewsTag::factory()->create([
            'name' => 'Original Tag',
            'slug' => 'original-tag',
        ]);

        $this->actingAs($this->admin);

        $response = $this->post("/admin/news-tags/{$newsTag->id}/duplicate");

        $this->assertDatabaseHas('news_tags', [
            'name' => 'Original Tag (Copy)',
            'slug' => 'original-tag-copy',
        ]);
    }

    public function test_can_bulk_activate_news_tags(): void
    {
        $newsTags = NewsTag::factory()->count(3)->create(['is_visible' => false]);

        $this->actingAs($this->admin);

        $response = $this->post('/admin/news-tags/bulk-activate', [
            'records' => $newsTags->pluck('id')->toArray(),
        ]);

        foreach ($newsTags as $newsTag) {
            $this->assertDatabaseHas('news_tags', [
                'id' => $newsTag->id,
                'is_visible' => true,
            ]);
        }
    }

    public function test_can_bulk_deactivate_news_tags(): void
    {
        $newsTags = NewsTag::factory()->count(3)->create(['is_visible' => true]);

        $this->actingAs($this->admin);

        $response = $this->post('/admin/news-tags/bulk-deactivate', [
            'records' => $newsTags->pluck('id')->toArray(),
        ]);

        foreach ($newsTags as $newsTag) {
            $this->assertDatabaseHas('news_tags', [
                'id' => $newsTag->id,
                'is_visible' => false,
            ]);
        }
    }

    public function test_can_bulk_duplicate_news_tags(): void
    {
        $newsTags = NewsTag::factory()->count(2)->create([
            'name' => 'Original Tag',
            'slug' => 'original-tag',
        ]);

        $this->actingAs($this->admin);

        $response = $this->post('/admin/news-tags/bulk-duplicate', [
            'records' => $newsTags->pluck('id')->toArray(),
        ]);

        $this->assertDatabaseHas('news_tags', [
            'name' => 'Original Tag (Copy)',
            'slug' => 'original-tag-copy',
        ]);
    }

    public function test_can_bulk_delete_news_tags(): void
    {
        $newsTags = NewsTag::factory()->count(3)->create();

        $this->actingAs($this->admin);

        $response = $this->post('/admin/news-tags/bulk-delete', [
            'records' => $newsTags->pluck('id')->toArray(),
        ]);

        foreach ($newsTags as $newsTag) {
            $this->assertDatabaseMissing('news_tags', [
                'id' => $newsTag->id,
            ]);
        }
    }

    public function test_news_tag_has_news_relationship(): void
    {
        $newsTag = NewsTag::factory()->create();
        $news = News::factory()->create();
        $news->tags()->attach($newsTag);

        $this->assertTrue($newsTag->news()->exists());
        $this->assertEquals(1, $newsTag->news()->count());
    }

    public function test_news_tag_can_be_attached_to_multiple_news(): void
    {
        $newsTag = NewsTag::factory()->create();
        $news1 = News::factory()->create();
        $news2 = News::factory()->create();

        $news1->tags()->attach($newsTag);
        $news2->tags()->attach($newsTag);

        $this->assertEquals(2, $newsTag->news()->count());
    }

    public function test_news_tag_can_be_detached_from_news(): void
    {
        $newsTag = NewsTag::factory()->create();
        $news = News::factory()->create();
        $news->tags()->attach($newsTag);

        $this->assertEquals(1, $newsTag->news()->count());

        $news->tags()->detach($newsTag);

        $this->assertEquals(0, $newsTag->news()->count());
    }

    public function test_news_tag_scope_visible(): void
    {
        NewsTag::factory()->create(['is_visible' => true]);
        NewsTag::factory()->create(['is_visible' => false]);

        $visibleTags = NewsTag::visible()->get();

        $this->assertEquals(1, $visibleTags->count());
        $this->assertTrue($visibleTags->first()->is_visible);
    }

    public function test_news_tag_is_visible_method(): void
    {
        $activeTag = NewsTag::factory()->create(['is_visible' => true]);
        $inactiveTag = NewsTag::factory()->create(['is_visible' => false]);

        $this->assertTrue($activeTag->isVisible());
        $this->assertFalse($inactiveTag->isVisible());
    }

    public function test_news_tag_route_key_name(): void
    {
        $newsTag = NewsTag::factory()->create();

        $this->assertEquals('slug', $newsTag->getRouteKeyName());
    }

    public function test_news_tag_has_translatable_attributes(): void
    {
        $newsTag = NewsTag::factory()->create();

        $this->assertIsString($newsTag->name);
        $this->assertIsString($newsTag->slug);
        $this->assertIsString($newsTag->description);
    }

    public function test_news_tag_can_be_sorted_by_sort_order(): void
    {
        NewsTag::factory()->create(['sort_order' => 3]);
        NewsTag::factory()->create(['sort_order' => 1]);
        NewsTag::factory()->create(['sort_order' => 2]);

        $sortedTags = NewsTag::orderBy('sort_order')->get();

        $this->assertEquals(1, $sortedTags->first()->sort_order);
        $this->assertEquals(3, $sortedTags->last()->sort_order);
    }

    public function test_news_tag_has_color_attribute(): void
    {
        $newsTag = NewsTag::factory()->create(['color' => '#3B82F6']);

        $this->assertEquals('#3B82F6', $newsTag->color);
    }

    public function test_news_tag_has_default_color(): void
    {
        $newsTag = NewsTag::factory()->create();

        $this->assertNotNull($newsTag->color);
    }

    public function test_news_tag_can_be_replicated(): void
    {
        $originalTag = NewsTag::factory()->create([
            'name' => 'Original Tag',
            'slug' => 'original-tag',
            'color' => '#3B82F6',
        ]);

        $replicatedTag = $originalTag->replicate();
        $replicatedTag->name = 'Replicated Tag';
        $replicatedTag->slug = 'replicated-tag';
        $replicatedTag->save();

        $this->assertDatabaseHas('news_tags', [
            'name' => 'Replicated Tag',
            'slug' => 'replicated-tag',
            'color' => '#3B82F6',
        ]);
    }

    public function test_news_tag_has_timestamps(): void
    {
        $newsTag = NewsTag::factory()->create();

        $this->assertNotNull($newsTag->created_at);
        $this->assertNotNull($newsTag->updated_at);
    }

    public function test_news_tag_has_fillable_attributes(): void
    {
        $newsTag = NewsTag::factory()->create([
            'is_visible' => true,
            'color' => '#3B82F6',
            'sort_order' => 1,
        ]);

        $this->assertTrue($newsTag->is_visible);
        $this->assertEquals('#3B82F6', $newsTag->color);
        $this->assertEquals(1, $newsTag->sort_order);
    }

    public function test_news_tag_has_casts(): void
    {
        $newsTag = NewsTag::factory()->create([
            'is_visible' => '1',
            'sort_order' => '5',
        ]);

        $this->assertIsBool($newsTag->is_visible);
        $this->assertIsInt($newsTag->sort_order);
    }
}
