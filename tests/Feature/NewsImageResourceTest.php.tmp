<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\News;
use App\Models\NewsImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\TestCase;

final class NewsImageResourceTest extends TestCase
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

        $this->admin->assignRole('administrator');

        // Create test news
        $this->news = News::factory()->create([
            'is_visible' => true,
            'published_at' => now(),
        ]);

        // Create test news image
        $this->newsImage = NewsImage::factory()->create([
            'news_id' => $this->news->id,
            'file_path' => 'news-images/test-image.jpg',
            'alt_text' => 'Test image alt text',
            'caption' => 'Test image caption',
            'is_featured' => true,
            'sort_order' => 1,
            'file_size' => 1024000,  // 1MB
            'mime_type' => 'image/jpeg',
            'dimensions' => ['width' => 800, 'height' => 600],
        ]);
    }

    public function test_can_list_news_images(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get('/admin/news-image-resources');

        $response->assertOk();
        $response->assertSee('Test image alt text');
        $response->assertSee('Test image caption');
    }

    public function test_can_view_news_image(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get('/admin/news-images/'.$this->newsImage->id);

        $response->assertOk();
        $response->assertSee('Test image alt text');
        $response->assertSee('Test image caption');
    }

    public function test_can_create_news_image(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get('/admin/news-images/create');

        $response->assertOk();
        $response->assertSee('Create News Image');
    }

    public function test_can_edit_news_image(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get('/admin/news-images/'.$this->newsImage->id.'/edit');

        $response->assertOk();
        $response->assertSee('Edit News Image');
    }

    public function test_can_filter_by_news(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get('/admin/news-images?filter[news_id]='.$this->news->id);

        $response->assertOk();
        $response->assertSee('Test image alt text');
    }

    public function test_can_filter_by_featured_status(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get('/admin/news-images?filter[is_featured]=1');

        $response->assertOk();
        $response->assertSee('Test image alt text');
    }

    public function test_can_filter_by_mime_type(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get('/admin/news-images?filter[mime_type]=image/jpeg');

        $response->assertOk();
        $response->assertSee('Test image alt text');
    }

    public function test_can_filter_large_files(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get('/admin/news-images?filter[large_files]=1');

        $response->assertOk();
        $response->assertSee('Test image alt text');
    }

    public function test_can_filter_recent_uploads(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get('/admin/news-images?filter[recent_uploads]=1');

        $response->assertOk();
        $response->assertSee('Test image alt text');
    }

    public function test_can_filter_no_alt_text(): void
    {
        // Create image without alt text
        NewsImage::factory()->create([
            'news_id' => $this->news->id,
            'alt_text' => null,
        ]);

        $this->actingAs($this->admin);

        $response = $this->get('/admin/news-images?filter[no_alt_text]=1');

        $response->assertOk();
    }

    public function test_can_search_news_images(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get('/admin/news-images?search=test');

        $response->assertOk();
        $response->assertSee('Test image alt text');
    }

    public function test_can_sort_by_sort_order(): void
    {
        // Create additional images with different sort orders
        NewsImage::factory()->create([
            'news_id' => $this->news->id,
            'sort_order' => 3,
        ]);

        NewsImage::factory()->create([
            'news_id' => $this->news->id,
            'sort_order' => 2,
        ]);

        $this->actingAs($this->admin);

        $response = $this->get('/admin/news-images?sort=sort_order');

        $response->assertOk();
    }

    public function test_can_sort_by_created_at(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get('/admin/news-images?sort=-created_at');

        $response->assertOk();
    }

    public function test_can_toggle_columns(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get('/admin/news-images');

        $response->assertOk();
        $response->assertSee('Sort Order');
        $response->assertSee('File Size');
        $response->assertSee('MIME Type');
    }

    public function test_can_use_pagination(): void
    {
        // Create multiple images to test pagination
        NewsImage::factory()->count(15)->create([
            'news_id' => $this->news->id,
        ]);

        $this->actingAs($this->admin);

        $response = $this->get('/admin/news-images');

        $response->assertOk();
        $response->assertSee('10');
        $response->assertSee('25');
        $response->assertSee('50');
        $response->assertSee('100');
    }

    public function test_can_duplicate_news_image(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post('/admin/news-images/'.$this->newsImage->id.'/duplicate');

        $response->assertRedirect();

        $this->assertDatabaseCount('news_images', 2);

        $duplicatedImage = NewsImage::where('id', '!=', $this->newsImage->id)->first();
        $this->assertEquals($this->newsImage->alt_text, $duplicatedImage->alt_text);
        $this->assertEquals($this->newsImage->caption, $duplicatedImage->caption);
    }

    public function test_can_download_news_image(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get('/admin/news-images/'.$this->newsImage->id.'/download');

        $response->assertOk();
    }

    public function test_can_bulk_set_featured(): void
    {
        // Create additional images
        $images = NewsImage::factory()->count(3)->create([
            'news_id' => $this->news->id,
            'is_featured' => false,
        ]);

        $this->actingAs($this->admin);

        $response = $this->post('/admin/news-images/bulk-actions/set_featured', [
            'records' => $images->pluck('id')->toArray(),
        ]);

        $response->assertRedirect();

        foreach ($images as $image) {
            $image->refresh();
            $this->assertTrue($image->is_featured);
        }
    }

    public function test_can_bulk_unset_featured(): void
    {
        // Create additional images
        $images = NewsImage::factory()->count(3)->create([
            'news_id' => $this->news->id,
            'is_featured' => true,
        ]);

        $this->actingAs($this->admin);

        $response = $this->post('/admin/news-images/bulk-actions/unset_featured', [
            'records' => $images->pluck('id')->toArray(),
        ]);

        $response->assertRedirect();

        foreach ($images as $image) {
            $image->refresh();
            $this->assertFalse($image->is_featured);
        }
    }

    public function test_can_bulk_reorder(): void
    {
        // Create additional images
        $images = NewsImage::factory()->count(3)->create([
            'news_id' => $this->news->id,
        ]);

        $this->actingAs($this->admin);

        $response = $this->post('/admin/news-images/bulk-actions/reorder', [
            'records' => $images->pluck('id')->toArray(),
        ]);

        $response->assertRedirect();

        foreach ($images as $index => $image) {
            $image->refresh();
            $this->assertEquals($index + 1, $image->sort_order);
        }
    }

    public function test_can_bulk_delete(): void
    {
        // Create additional images
        $images = NewsImage::factory()->count(3)->create([
            'news_id' => $this->news->id,
        ]);

        $this->actingAs($this->admin);

        $response = $this->delete('/admin/news-images/bulk-actions/delete', [
            'records' => $images->pluck('id')->toArray(),
        ]);

        $response->assertRedirect();

        $this->assertDatabaseCount('news_images', 1);  // Only the original test image remains
    }

    public function test_news_image_model_relationships(): void
    {
        $this->assertInstanceOf(News::class, $this->newsImage->news);
        $this->assertEquals($this->news->id, $this->newsImage->news->id);
    }

    public function test_news_image_model_scopes(): void
    {
        // Test featured scope
        $featuredImages = NewsImage::featured()->get();
        $this->assertCount(1, $featuredImages);
        $this->assertTrue($featuredImages->first()->is_featured);

        // Test ordered scope
        $orderedImages = NewsImage::ordered()->get();
        $this->assertCount(1, $orderedImages);
        $this->assertEquals(1, $orderedImages->first()->sort_order);
    }

    public function test_news_image_model_accessors(): void
    {
        $this->assertStringContains('storage/', $this->newsImage->url);
        $this->assertStringContains('test-image.jpg', $this->newsImage->url);

        $this->assertStringContains('storage/', $this->newsImage->thumbnail_url);
        $this->assertStringContains('thumbnails/', $this->newsImage->thumbnail_url);

        $this->assertTrue($this->newsImage->is_image);

        $this->assertEquals('1000.00 KB', $this->newsImage->file_size_formatted);
    }

    public function test_news_image_model_casts(): void
    {
        $this->assertIsInt($this->newsImage->news_id);
        $this->assertIsBool($this->newsImage->is_featured);
        $this->assertIsInt($this->newsImage->sort_order);
        $this->assertIsInt($this->newsImage->file_size);
        $this->assertIsArray($this->newsImage->dimensions);
    }

    public function test_news_image_validation_rules(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post('/admin/news-images', [
            'news_id' => '',  // Required field
            'file_path' => '',  // Required field
        ]);

        $response->assertSessionHasErrors(['news_id', 'file_path']);
    }

    public function test_news_image_file_upload_validation(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post('/admin/news-images', [
            'news_id' => $this->news->id,
            'file_path' => 'invalid-file.txt',  // Invalid file type
        ]);

        $response->assertSessionHasErrors(['file_path']);
    }

    public function test_news_image_sort_order_validation(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post('/admin/news-images', [
            'news_id' => $this->news->id,
            'file_path' => 'news-images/test.jpg',
            'sort_order' => -1,  // Invalid negative value
        ]);

        $response->assertSessionHasErrors(['sort_order']);
    }

    public function test_news_image_alt_text_length_validation(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post('/admin/news-images', [
            'news_id' => $this->news->id,
            'file_path' => 'news-images/test.jpg',
            'alt_text' => str_repeat('a', 256),  // Too long
        ]);

        $response->assertSessionHasErrors(['alt_text']);
    }

    public function test_news_image_caption_length_validation(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post('/admin/news-images', [
            'news_id' => $this->news->id,
            'file_path' => 'news-images/test.jpg',
            'caption' => str_repeat('a', 501),  // Too long
        ]);

        $response->assertSessionHasErrors(['caption']);
    }

    public function test_news_image_automatic_file_info_extraction(): void
    {
        $this->actingAs($this->admin);

        // Create a temporary test file
        $testFile = storage_path('app/public/news-images/test.jpg');
        if (! file_exists(dirname($testFile))) {
            mkdir(dirname($testFile), 0755, true);
        }
        file_put_contents($testFile, 'fake image content');

        $response = $this->post('/admin/news-images', [
            'news_id' => $this->news->id,
            'file_path' => 'news-images/test.jpg',
            'alt_text' => 'Test alt text',
            'caption' => 'Test caption',
        ]);

        $response->assertRedirect();

        $image = NewsImage::latest()->first();
        $this->assertNotNull($image->file_size);
        $this->assertNotNull($image->mime_type);

        // Clean up
        unlink($testFile);
    }

    public function test_news_image_automatic_sort_order_assignment(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post('/admin/news-images', [
            'news_id' => $this->news->id,
            'file_path' => 'news-images/test.jpg',
            'alt_text' => 'Test alt text',
        ]);

        $response->assertRedirect();

        $image = NewsImage::latest()->first();
        $this->assertEquals(2, $image->sort_order);  // Should be next in sequence
    }

    public function test_news_image_polling_enabled(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get('/admin/news-images');

        $response->assertOk();
        $response->assertSee('data-poll="30s"');
    }

    public function test_news_image_persistent_filters(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get('/admin/news-images?filter[is_featured]=1');

        $response->assertOk();
        $response->assertSee('data-persist-filters-in-session="true"');
    }

    public function test_news_image_persistent_sort(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get('/admin/news-images?sort=sort_order');

        $response->assertOk();
        $response->assertSee('data-persist-sort-in-session="true"');
    }

    public function test_news_image_persistent_search(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get('/admin/news-images?search=test');

        $response->assertOk();
        $response->assertSee('data-persist-search-in-session="true"');
    }
}
