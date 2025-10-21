<?php

declare(strict_types=1);

use App\Models\News;
use App\Models\NewsCategory;
use App\Models\NewsComment;
use App\Models\NewsTag;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->news = News::factory()->create([
        'is_visible'   => true,
        'published_at' => now()->subDay(),
        'author_name'  => 'Test Author',
    ]);

    $this->news->translations()->create([
        'locale'  => 'lt',
        'title'   => 'Test News Title',
        'slug'    => 'test-news-title',
        'summary' => 'Test news summary',
        'content' => 'Test news content',
    ]);

    $this->category = NewsCategory::factory()->create();
    $this->category->translations()->create([
        'locale' => 'lt',
        'name'   => 'Test Category',
        'slug'   => 'test-category',
    ]);

    $this->tag = NewsTag::factory()->create();
    $this->tag->translations()->create([
        'locale' => 'lt',
        'name'   => 'Test Tag',
        'slug'   => 'test-tag',
    ]);
});

it('can display news index page', function (): void {
    $response = $this->get(route('news.index'));

    $response->assertStatus(200);
    $response->assertViewIs('news.index');
    $response->assertSee('Test News Title');
    $response->assertSee('Test Author');
});

it('can display individual news article', function (): void {
    $response = $this->get(route('news.show', $this->news->slug));

    $response->assertStatus(200);
    $response->assertViewIs('news.show');
    $response->assertSee('Test News Title');
    $response->assertSee('Test news content');
    $response->assertSee('Test Author');
});

it('can display localized news article', function (): void {
    $response = $this->get(route('localized.news.show', ['locale' => 'lt', 'slug' => $this->news->slug]));

    $response->assertStatus(200);
    $response->assertViewIs('news.show');
    $response->assertSee('Test News Title');
});

it('increments view count when viewing news', function (): void {
    expect($this->news->view_count)->toBe(0);

    $this->get(route('news.show', $this->news->slug));

    expect($this->news->fresh()->view_count)->toBe(1);
});

it('can filter news by category', function (): void {
    $this->news->categories()->attach($this->category->id);

    $response = $this->get(route('news.index', ['category' => $this->category->id]));

    $response->assertStatus(200);
    $response->assertSee('Test News Title');
});

it('can filter news by tag', function (): void {
    $this->news->tags()->attach($this->tag->id);

    $response = $this->get(route('news.index', ['tag' => $this->tag->id]));

    $response->assertStatus(200);
    $response->assertSee('Test News Title');
});

it('can search news', function (): void {
    $response = $this->get(route('news.index', ['search' => 'Test']));

    $response->assertStatus(200);
    $response->assertSee('Test News Title');
});

it('can filter featured news', function (): void {
    $featuredNews = News::factory()->create([
        'is_visible'   => true,
        'is_featured'  => true,
        'published_at' => now()->subDay(),
    ]);

    $featuredNews->translations()->create([
        'locale'  => 'lt',
        'title'   => 'Featured News',
        'slug'    => 'featured-news',
        'summary' => 'Featured news summary',
        'content' => 'Featured news content',
    ]);

    $response = $this->get(route('news.index', ['featured' => '1']));

    $response->assertStatus(200);
    $response->assertSee('Featured News');
});

it('can display news by category', function (): void {
    $this->news->categories()->attach($this->category->id);

    $response = $this->get(route('news.category', $this->category->slug));

    $response->assertStatus(200);
    $response->assertViewIs('news.category');
    $response->assertSee('Test News Title');
    $response->assertSee('Test Category');
});

it('can display news by tag', function (): void {
    $this->news->tags()->attach($this->tag->id);

    $response = $this->get(route('news.tag', $this->tag->slug));

    $response->assertStatus(200);
    $response->assertViewIs('news.tag');
    $response->assertSee('Test News Title');
    $response->assertSee('Test Tag');
});

it('shows related news on news detail page', function (): void {
    $relatedNews = News::factory()->create([
        'is_visible'   => true,
        'published_at' => now()->subDay(),
    ]);

    $relatedNews->translations()->create([
        'locale'  => 'lt',
        'title'   => 'Related News',
        'slug'    => 'related-news',
        'summary' => 'Related news summary',
        'content' => 'Related news content',
    ]);

    $this->news->categories()->attach($this->category->id);
    $relatedNews->categories()->attach($this->category->id);

    $response = $this->get(route('news.show', $this->news->slug));

    $response->assertStatus(200);
    $response->assertSee('Related News');
});

it('displays comments on news detail page', function (): void {
    $comment = NewsComment::factory()->create([
        'news_id'     => $this->news->id,
        'author_name' => 'Comment Author',
        'content'     => 'Test comment content',
        'is_approved' => true,
        'is_visible'  => true,
    ]);

    $response = $this->get(route('news.show', $this->news->slug));

    $response->assertStatus(200);
    $response->assertSee('Comment Author');
    $response->assertSee('Test comment content');
});

it('displays comment form on news detail page', function (): void {
    $response = $this->get(route('news.show', $this->news->slug));

    $response->assertStatus(200);
    $response->assertSee('comment_name');
    $response->assertSee('comment_email');
    $response->assertSee('comment_content');
});

it('can store a new comment', function (): void {
    $commentData = [
        'author_name'  => 'New Comment Author',
        'author_email' => 'comment@example.com',
        'content'      => 'New comment content',
    ];

    $response = $this->post(route('news.comments.store', $this->news->slug), $commentData);

    $response->assertRedirect(route('news.show', $this->news->slug));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('news_comments', [
        'news_id'      => $this->news->id,
        'author_name'  => 'New Comment Author',
        'author_email' => 'comment@example.com',
        'content'      => 'New comment content',
        'is_approved'  => false,
        'is_visible'   => true,
    ]);
});

it('redirects localized comments back to the localized news page', function (): void {
    $commentData = [
        'author_name'  => 'Localized Commenter',
        'author_email' => 'localized@example.com',
        'content'      => 'Localized comment',
    ];

    $response = $this->post(route('localized.news.comments.store', ['locale' => 'lt', 'slug' => $this->news->slug]), $commentData);

    $response->assertRedirect(route('localized.news.show', ['locale' => 'lt', 'slug' => $this->news->slug]));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('news_comments', [
        'news_id'      => $this->news->id,
        'author_name'  => 'Localized Commenter',
        'author_email' => 'localized@example.com',
        'content'      => 'Localized comment',
    ]);
});

it('validates comment data', function (): void {
    $response = $this->post(route('news.comments.store', $this->news->slug), []);

    $response->assertSessionHasErrors(['author_name', 'author_email', 'content']);
});

it('can store a reply to a comment', function (): void {
    $parentComment = NewsComment::factory()->create([
        'news_id'     => $this->news->id,
        'author_name' => 'Parent Author',
        'content'     => 'Parent comment',
    ]);

    $replyData = [
        'parent_id'    => $parentComment->id,
        'author_name'  => 'Reply Author',
        'author_email' => 'reply@example.com',
        'content'      => 'Reply content',
    ];

    $response = $this->post(route('news.comments.store', $this->news->slug), $replyData);

    $response->assertRedirect(route('news.show', $this->news->slug));

    $this->assertDatabaseHas('news_comments', [
        'news_id'     => $this->news->id,
        'parent_id'   => $parentComment->id,
        'author_name' => 'Reply Author',
        'content'     => 'Reply content',
    ]);
});

it('does not show unpublished news', function (): void {
    $unpublishedNews = News::factory()->create([
        'is_visible'   => false,
        'published_at' => now()->subDay(),
    ]);

    $unpublishedNews->translations()->create([
        'locale'  => 'lt',
        'title'   => 'Unpublished News',
        'slug'    => 'unpublished-news',
        'summary' => 'Unpublished summary',
        'content' => 'Unpublished content',
    ]);

    $response = $this->get(route('news.index'));

    $response->assertStatus(200);
    $response->assertDontSee('Unpublished News');
});

it('does not show future published news', function (): void {
    $futureNews = News::factory()->create([
        'is_visible'   => true,
        'published_at' => now()->addDay(),
    ]);

    $futureNews->translations()->create([
        'locale'  => 'lt',
        'title'   => 'Future News',
        'slug'    => 'future-news',
        'summary' => 'Future summary',
        'content' => 'Future content',
    ]);

    $response = $this->get(route('news.index'));

    $response->assertStatus(200);
    $response->assertDontSee('Future News');
});

it('returns 404 for non-existent news', function (): void {
    $response = $this->get(route('news.show', 'non-existent-slug'));

    $response->assertStatus(404);
});

it('returns 404 for non-existent category', function (): void {
    $response = $this->get(route('news.category', 'non-existent-category'));

    $response->assertStatus(404);
});

it('returns 404 for non-existent tag', function (): void {
    $response = $this->get(route('news.tag', 'non-existent-tag'));

    $response->assertStatus(404);
});
