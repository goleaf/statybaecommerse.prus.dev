<?php declare(strict_types=1);

use App\Models\News;
use App\Models\NewsCategory;
use App\Models\NewsTag;
use App\Models\NewsComment;
use App\Models\NewsImage;
use App\Models\Translations\NewsTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->news = News::factory()->create([
        'is_visible' => true,
        'is_featured' => false,
        'published_at' => now()->subDay(),
        'author_name' => 'Test Author',
        'author_email' => 'test@example.com',
        'view_count' => 0,
        'meta_data' => ['key' => 'value'],
    ]);
});

it('can create a news article', function () {
    expect($this->news)->toBeInstanceOf(News::class);
    expect($this->news->is_visible)->toBeTrue();
    expect($this->news->is_featured)->toBeFalse();
    expect($this->news->author_name)->toBe('Test Author');
    expect($this->news->author_email)->toBe('test@example.com');
    expect($this->news->view_count)->toBe(0);
    expect($this->news->meta_data)->toBe(['key' => 'value']);
});

it('can check if news is published', function () {
    expect($this->news->isPublished())->toBeTrue();
    
    $unpublishedNews = News::factory()->create([
        'is_visible' => false,
        'published_at' => now()->subDay(),
    ]);
    
    expect($unpublishedNews->isPublished())->toBeFalse();
    
    $futureNews = News::factory()->create([
        'is_visible' => true,
        'published_at' => now()->addDay(),
    ]);
    
    expect($futureNews->isPublished())->toBeFalse();
});

it('can check if news is featured', function () {
    expect($this->news->isFeatured())->toBeFalse();
    
    $featuredNews = News::factory()->create(['is_featured' => true]);
    expect($featuredNews->isFeatured())->toBeTrue();
});

it('can increment view count', function () {
    expect($this->news->view_count)->toBe(0);
    
    $this->news->incrementViewCount();
    
    expect($this->news->fresh()->view_count)->toBe(1);
});

it('can have categories', function () {
    $category1 = NewsCategory::factory()->create();
    $category2 = NewsCategory::factory()->create();
    
    $this->news->categories()->attach([$category1->id, $category2->id]);
    
    expect($this->news->categories)->toHaveCount(2);
    expect($this->news->categories->pluck('id')->toArray())->toContain($category1->id, $category2->id);
});

it('can have tags', function () {
    $tag1 = NewsTag::factory()->create();
    $tag2 = NewsTag::factory()->create();
    
    $this->news->tags()->attach([$tag1->id, $tag2->id]);
    
    expect($this->news->tags)->toHaveCount(2);
    expect($this->news->tags->pluck('id')->toArray())->toContain($tag1->id, $tag2->id);
});

it('can have comments', function () {
    $comment1 = NewsComment::factory()->create(['news_id' => $this->news->id]);
    $comment2 = NewsComment::factory()->create(['news_id' => $this->news->id]);
    
    expect($this->news->comments)->toHaveCount(2);
    expect($this->news->comments->pluck('id')->toArray())->toContain($comment1->id, $comment2->id);
});

it('can have images', function () {
    $image1 = NewsImage::factory()->create(['news_id' => $this->news->id]);
    $image2 = NewsImage::factory()->create(['news_id' => $this->news->id]);
    
    expect($this->news->images)->toHaveCount(2);
    expect($this->news->images->pluck('id')->toArray())->toContain($image1->id, $image2->id);
});

it('can scope published news', function () {
    $publishedNews = News::factory()->create([
        'is_visible' => true,
        'published_at' => now()->subDay(),
    ]);
    
    $unpublishedNews = News::factory()->create([
        'is_visible' => false,
        'published_at' => now()->subDay(),
    ]);
    
    $futureNews = News::factory()->create([
        'is_visible' => true,
        'published_at' => now()->addDay(),
    ]);
    
    $publishedNewsList = News::published()->get();
    
    expect($publishedNewsList)->toHaveCount(1);
    expect($publishedNewsList->first()->id)->toBe($publishedNews->id);
});

it('can scope featured news', function () {
    $featuredNews = News::factory()->create(['is_featured' => true]);
    $regularNews = News::factory()->create(['is_featured' => false]);
    
    $featuredNewsList = News::featured()->get();
    
    expect($featuredNewsList)->toHaveCount(1);
    expect($featuredNewsList->first()->id)->toBe($featuredNews->id);
});

it('can scope by category', function () {
    $category = NewsCategory::factory()->create();
    $newsInCategory = News::factory()->create();
    $newsNotInCategory = News::factory()->create();
    
    $newsInCategory->categories()->attach($category->id);
    
    $newsByCategory = News::byCategory($category->id)->get();
    
    expect($newsByCategory)->toHaveCount(1);
    expect($newsByCategory->first()->id)->toBe($newsInCategory->id);
});

it('can scope by tag', function () {
    $tag = NewsTag::factory()->create();
    $newsWithTag = News::factory()->create();
    $newsWithoutTag = News::factory()->create();
    
    $newsWithTag->tags()->attach($tag->id);
    
    $newsByTag = News::byTag($tag->id)->get();
    
    expect($newsByTag)->toHaveCount(1);
    expect($newsByTag->first()->id)->toBe($newsWithTag->id);
});

it('can search news by title and content', function () {
    $searchableNews = News::factory()->create();
    $searchableNews->translations()->create([
        'locale' => 'lt',
        'title' => 'Test News Title',
        'slug' => 'test-news-title',
        'summary' => 'Test summary',
        'content' => 'Test content with searchable text',
    ]);
    
    $otherNews = News::factory()->create();
    $otherNews->translations()->create([
        'locale' => 'lt',
        'title' => 'Other News',
        'slug' => 'other-news',
        'summary' => 'Other summary',
        'content' => 'Other content',
    ]);
    
    $searchResults = News::search('Test')->get();
    
    expect($searchResults)->toHaveCount(1);
    expect($searchResults->first()->id)->toBe($searchableNews->id);
});

it('can get translated attributes', function () {
    $this->news->translations()->create([
        'locale' => 'lt',
        'title' => 'Test Title',
        'slug' => 'test-slug',
        'summary' => 'Test Summary',
        'content' => 'Test Content',
        'seo_title' => 'SEO Title',
        'seo_description' => 'SEO Description',
    ]);
    
    app()->setLocale('lt');
    
    expect($this->news->title)->toBe('Test Title');
    expect($this->news->slug)->toBe('test-slug');
    expect($this->news->summary)->toBe('Test Summary');
    expect($this->news->content)->toBe('Test Content');
    expect($this->news->seo_title)->toBe('SEO Title');
    expect($this->news->seo_description)->toBe('SEO Description');
});

it('uses slug as route key', function () {
    expect($this->news->getRouteKeyName())->toBe('slug');
});

