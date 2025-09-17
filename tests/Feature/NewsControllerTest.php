<?php declare(strict_types=1);

use App\Models\News;
use App\Models\NewsCategory;
use App\Models\NewsTag;
use App\Models\NewsComment;
use App\Models\NewsImage;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->news = News::factory()->create([
        'is_visible' => true,
        'published_at' => now()->subDay(),
        'author_name' => 'Test Author',
    ]);
    
    $this->news->translations()->create([
        'locale' => 'lt',
        'title' => 'Test News Title',
        'slug' => 'test-news-title',
        'summary' => 'Test news summary',
        'content' => 'Test news content',
    ]);
    
    $this->category = NewsCategory::factory()->create();
    $this->category->translations()->create([
        'locale' => 'lt',
        'name' => 'Test Category',
        'slug' => 'test-category',
    ]);
    
    $this->tag = NewsTag::factory()->create();
    $this->tag->translations()->create([
        'locale' => 'lt',
        'name' => 'Test Tag',
        'slug' => 'test-tag',
    ]);
});

it('can display news index page', function () {
    $response = $this->get(localized_route('news.index'));
    
    $response->assertStatus(200);
    $response->assertViewIs('news.index');
    $response->assertSee('Test News Title');
    $response->assertSee('Test Author');
});

it('can display individual news article', function () {
    $response = $this->get(localized_route('news.show', $this->news->slug));
    
    $response->assertStatus(200);
    $response->assertViewIs('news.show');
    $response->assertSee('Test News Title');
    $response->assertSee('Test news content');
    $response->assertSee('Test Author');
});

it('increments view count when viewing news', function () {
    expect($this->news->view_count)->toBe(0);
    
    $this->get(localized_route('news.show', $this->news->slug));
    
    expect($this->news->fresh()->view_count)->toBe(1);
});

it('can filter news by category', function () {
    $this->news->categories()->attach($this->category->id);
    
    $response = $this->get(localized_route('news.index', ['category' => $this->category->id]));
    
    $response->assertStatus(200);
    $response->assertSee('Test News Title');
});

it('can filter news by tag', function () {
    $this->news->tags()->attach($this->tag->id);
    
    $response = $this->get(localized_route('news.index', ['tag' => $this->tag->id]));
    
    $response->assertStatus(200);
    $response->assertSee('Test News Title');
});

it('can search news', function () {
    $response = $this->get(localized_route('news.index', ['search' => 'Test']));
    
    $response->assertStatus(200);
    $response->assertSee('Test News Title');
});

it('can filter featured news', function () {
    $featuredNews = News::factory()->create([
        'is_visible' => true,
        'is_featured' => true,
        'published_at' => now()->subDay(),
    ]);
    
    $featuredNews->translations()->create([
        'locale' => 'lt',
        'title' => 'Featured News',
        'slug' => 'featured-news',
        'summary' => 'Featured news summary',
        'content' => 'Featured news content',
    ]);
    
    $response = $this->get(localized_route('news.index', ['featured' => '1']));
    
    $response->assertStatus(200);
    $response->assertSee('Featured News');
});

it('can display news by category', function () {
    $this->news->categories()->attach($this->category->id);
    
    $response = $this->get(localized_route('news.category', $this->category->slug));
    
    $response->assertStatus(200);
    $response->assertViewIs('news.category');
    $response->assertSee('Test News Title');
    $response->assertSee('Test Category');
});

it('can display news by tag', function () {
    $this->news->tags()->attach($this->tag->id);
    
    $response = $this->get(localized_route('news.tag', $this->tag->slug));
    
    $response->assertStatus(200);
    $response->assertViewIs('news.tag');
    $response->assertSee('Test News Title');
    $response->assertSee('Test Tag');
});

it('shows related news on news detail page', function () {
    $relatedNews = News::factory()->create([
        'is_visible' => true,
        'published_at' => now()->subDay(),
    ]);
    
    $relatedNews->translations()->create([
        'locale' => 'lt',
        'title' => 'Related News',
        'slug' => 'related-news',
        'summary' => 'Related news summary',
        'content' => 'Related news content',
    ]);
    
    $this->news->categories()->attach($this->category->id);
    $relatedNews->categories()->attach($this->category->id);
    
    $response = $this->get(localized_route('news.show', $this->news->slug));
    
    $response->assertStatus(200);
    $response->assertSee('Related News');
});

it('displays comments on news detail page', function () {
    $comment = NewsComment::factory()->create([
        'news_id' => $this->news->id,
        'author_name' => 'Comment Author',
        'content' => 'Test comment content',
        'is_approved' => true,
        'is_visible' => true,
    ]);
    
    $response = $this->get(localized_route('news.show', $this->news->slug));
    
    $response->assertStatus(200);
    $response->assertSee('Comment Author');
    $response->assertSee('Test comment content');
});

it('displays comment form on news detail page', function () {
    $response = $this->get(localized_route('news.show', $this->news->slug));
    
    $response->assertStatus(200);
    $response->assertSee('comment_name');
    $response->assertSee('comment_email');
    $response->assertSee('comment_content');
});

it('can store a new comment', function () {
    $commentData = [
        'author_name' => 'New Comment Author',
        'author_email' => 'comment@example.com',
        'content' => 'New comment content',
    ];
    
    $response = $this->post(localized_route('news.comments.store', $this->news->slug), $commentData);
    
    $response->assertRedirect(localized_route('news.show', $this->news->slug));
    $response->assertSessionHas('success');
    
    $this->assertDatabaseHas('news_comments', [
        'news_id' => $this->news->id,
        'author_name' => 'New Comment Author',
        'author_email' => 'comment@example.com',
        'content' => 'New comment content',
        'is_approved' => false,
        'is_visible' => true,
    ]);
});

it('validates comment data', function () {
    $response = $this->post(localized_route('news.comments.store', $this->news->slug), []);
    
    $response->assertSessionHasErrors(['author_name', 'author_email', 'content']);
});

it('can store a reply to a comment', function () {
    $parentComment = NewsComment::factory()->create([
        'news_id' => $this->news->id,
        'author_name' => 'Parent Author',
        'content' => 'Parent comment',
    ]);
    
    $replyData = [
        'parent_id' => $parentComment->id,
        'author_name' => 'Reply Author',
        'author_email' => 'reply@example.com',
        'content' => 'Reply content',
    ];
    
    $response = $this->post(localized_route('news.comments.store', $this->news->slug), $replyData);
    
    $response->assertRedirect(localized_route('news.show', $this->news->slug));
    
    $this->assertDatabaseHas('news_comments', [
        'news_id' => $this->news->id,
        'parent_id' => $parentComment->id,
        'author_name' => 'Reply Author',
        'content' => 'Reply content',
    ]);
});

it('does not show unpublished news', function () {
    $unpublishedNews = News::factory()->create([
        'is_visible' => false,
        'published_at' => now()->subDay(),
    ]);
    
    $unpublishedNews->translations()->create([
        'locale' => 'lt',
        'title' => 'Unpublished News',
        'slug' => 'unpublished-news',
        'summary' => 'Unpublished summary',
        'content' => 'Unpublished content',
    ]);
    
    $response = $this->get(localized_route('news.index'));
    
    $response->assertStatus(200);
    $response->assertDontSee('Unpublished News');
});

it('does not show future published news', function () {
    $futureNews = News::factory()->create([
        'is_visible' => true,
        'published_at' => now()->addDay(),
    ]);
    
    $futureNews->translations()->create([
        'locale' => 'lt',
        'title' => 'Future News',
        'slug' => 'future-news',
        'summary' => 'Future summary',
        'content' => 'Future content',
    ]);
    
    $response = $this->get(localized_route('news.index'));
    
    $response->assertStatus(200);
    $response->assertDontSee('Future News');
});

it('returns 404 for non-existent news', function () {
    $response = $this->get(localized_route('news.show', 'non-existent-slug'));
    
    $response->assertStatus(404);
});

it('returns 404 for non-existent category', function () {
    $response = $this->get(localized_route('news.category', 'non-existent-category'));
    
    $response->assertStatus(404);
});

it('returns 404 for non-existent tag', function () {
    $response = $this->get(localized_route('news.tag', 'non-existent-tag'));
    
    $response->assertStatus(404);
});

