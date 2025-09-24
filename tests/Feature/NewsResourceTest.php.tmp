<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\News;
use App\Models\NewsCategory;
use App\Models\NewsComment;
use App\Models\NewsImage;
use App\Models\NewsTag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\TestCase;

final class NewsResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create([
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]));
    }

    public function test_can_list_news_articles(): void
    {
        News::factory()->count(5)->create();

        $this
            ->get('/admin/news')
            ->assertOk()
            ->assertSee('News Articles');
    }

    public function test_can_create_news_article(): void
    {
        $newsData = [
            'title' => 'Test News Article',
            'slug' => 'test-news-article',
            'excerpt' => 'This is a test excerpt',
            'content' => 'This is the full content of the test news article.',
            'author_name' => 'Test Author',
            'author_email' => 'test@example.com',
            'published_at' => now(),
            'is_visible' => true,
            'is_featured' => false,
            'meta_title' => 'Test Meta Title',
            'meta_description' => 'Test meta description',
            'meta_keywords' => 'test, news, article',
        ];

        $this
            ->post('/admin/news', $newsData)
            ->assertRedirect();

        $this->assertDatabaseHas('news', [
            'author_name' => 'Test Author',
            'author_email' => 'test@example.com',
            'is_visible' => true,
            'is_featured' => false,
        ]);
    }

    public function test_can_view_news_article(): void
    {
        $news = News::factory()->create();

        $this
            ->get("/admin/news/{$news->id}")
            ->assertOk()
            ->assertSee($news->title);
    }

    public function test_can_edit_news_article(): void
    {
        $news = News::factory()->create([
            'author_name' => 'Original Author',
        ]);

        $updateData = [
            'author_name' => 'Updated Author',
            'is_featured' => true,
        ];

        $this
            ->put("/admin/news/{$news->id}", $updateData)
            ->assertRedirect();

        $this->assertDatabaseHas('news', [
            'id' => $news->id,
            'author_name' => 'Updated Author',
            'is_featured' => true,
        ]);
    }

    public function test_can_delete_news_article(): void
    {
        $news = News::factory()->create();

        $this
            ->delete("/admin/news/{$news->id}")
            ->assertRedirect();

        $this->assertSoftDeleted('news', [
            'id' => $news->id,
        ]);
    }

    public function test_can_filter_news_by_visibility(): void
    {
        News::factory()->create(['is_visible' => true]);
        News::factory()->create(['is_visible' => false]);

        $response = $this->get('/admin/news?tableFilters[is_visible][value]=1');

        $response->assertOk();
    }

    public function test_can_filter_news_by_featured_status(): void
    {
        News::factory()->create(['is_featured' => true]);
        News::factory()->create(['is_featured' => false]);

        $response = $this->get('/admin/news?tableFilters[is_featured][value]=1');

        $response->assertOk();
    }

    public function test_can_search_news_articles(): void
    {
        News::factory()->create(['author_name' => 'John Doe']);
        News::factory()->create(['author_name' => 'Jane Smith']);

        $response = $this->get('/admin/news?search=John');

        $response->assertOk();
    }

    public function test_news_article_has_categories_relation(): void
    {
        $news = News::factory()->create();
        $category = NewsCategory::factory()->create();

        $news->categories()->attach($category->id);

        $this->assertTrue($news->categories->contains($category));
    }

    public function test_news_article_has_tags_relation(): void
    {
        $news = News::factory()->create();
        $tag = NewsTag::factory()->create();

        $news->tags()->attach($tag->id);

        $this->assertTrue($news->tags->contains($tag));
    }

    public function test_news_article_has_comments_relation(): void
    {
        $news = News::factory()->create();
        $comment = NewsComment::factory()->create(['news_id' => $news->id]);

        $this->assertTrue($news->comments->contains($comment));
    }

    public function test_news_article_has_images_relation(): void
    {
        $news = News::factory()->create();
        $image = NewsImage::factory()->create(['news_id' => $news->id]);

        $this->assertTrue($news->images->contains($image));
    }

    public function test_can_create_news_with_categories(): void
    {
        $categories = NewsCategory::factory()->count(3)->create();

        $newsData = [
            'title' => 'Test News with Categories',
            'slug' => 'test-news-with-categories',
            'content' => 'Test content',
            'author_name' => 'Test Author',
            'published_at' => now(),
            'is_visible' => true,
            'categories' => $categories->pluck('id')->toArray(),
        ];

        $response = $this->post('/admin/news', $newsData);
        $response->assertRedirect();

        $news = News::where('slug', 'test-news-with-categories')->first();
        $this->assertCount(3, $news->categories);
    }

    public function test_can_create_news_with_tags(): void
    {
        $tags = NewsTag::factory()->count(2)->create();

        $newsData = [
            'title' => 'Test News with Tags',
            'slug' => 'test-news-with-tags',
            'content' => 'Test content',
            'author_name' => 'Test Author',
            'published_at' => now(),
            'is_visible' => true,
            'tags' => $tags->pluck('id')->toArray(),
        ];

        $response = $this->post('/admin/news', $newsData);
        $response->assertRedirect();

        $news = News::where('slug', 'test-news-with-tags')->first();
        $this->assertCount(2, $news->tags);
    }

    public function test_news_slug_is_automatically_generated(): void
    {
        $newsData = [
            'title' => 'Test News Article Title',
            'content' => 'Test content',
            'author_name' => 'Test Author',
            'published_at' => now(),
            'is_visible' => true,
        ];

        $response = $this->post('/admin/news', $newsData);
        $response->assertRedirect();

        $news = News::where('author_name', 'Test Author')->first();
        $this->assertEquals('test-news-article-title', $news->slug);
    }

    public function test_news_article_validation_requires_title(): void
    {
        $newsData = [
            'content' => 'Test content',
            'author_name' => 'Test Author',
            'published_at' => now(),
            'is_visible' => true,
        ];

        $response = $this->post('/admin/news', $newsData);
        $response->assertSessionHasErrors('title');
    }

    public function test_news_article_validation_requires_content(): void
    {
        $newsData = [
            'title' => 'Test Title',
            'author_name' => 'Test Author',
            'published_at' => now(),
            'is_visible' => true,
        ];

        $response = $this->post('/admin/news', $newsData);
        $response->assertSessionHasErrors('content');
    }

    public function test_news_article_validation_requires_author_name(): void
    {
        $newsData = [
            'title' => 'Test Title',
            'content' => 'Test content',
            'published_at' => now(),
            'is_visible' => true,
        ];

        $response = $this->post('/admin/news', $newsData);
        $response->assertSessionHasErrors('author_name');
    }
}
