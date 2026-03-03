<?php

declare(strict_types=1);

use App\Models\News;
use App\Models\NewsImage;
use App\Models\Translations\NewsTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders news index and show pages with lean relations', function (): void {
    $news = News::withoutGlobalScopes()->create([
        'is_visible'       => true,
        'is_featured'      => true,
        'moderation_state' => 'published',
        'published_at'     => now()->subHour(),
        'author_name'      => 'Frontend Editor',
    ]);

    NewsTranslation::query()->create([
        'news_id'         => $news->id,
        'locale'          => 'lt',
        'title'           => 'Frontend naujiena',
        'slug'            => 'frontend-naujiena',
        'summary'         => 'Trumpa santrauka',
        'content'         => '<p>Turinys frontend puslapiui</p>',
        'seo_title'       => 'Frontend SEO',
        'seo_description' => 'Frontend SEO aprašymas',
    ]);

    NewsImage::query()->create([
        'news_id'     => $news->id,
        'file_path'   => 'https://example.test/news/front.jpg',
        'alt_text'    => 'Frontend image',
        'is_featured' => true,
        'sort_order'  => 1,
        'mime_type'   => 'image/jpeg',
    ]);

    $this->get('/news')
        ->assertOk()
        ->assertSee('Frontend naujiena');

    $this->get('/news/frontend-naujiena')
        ->assertOk()
        ->assertSee('Frontend naujiena')
        ->assertSee('Turinys frontend puslapiui', false);
});

it('redirects legacy category and tag urls to canonical index', function (): void {
    $this->get('/news/category/legacy')
        ->assertRedirect(route('frontend.news.index'));

    $this->get('/news/tag/legacy')
        ->assertRedirect(route('frontend.news.index'));

    $this->get('/lt/news/category/legacy')
        ->assertRedirect(route('localized.news.index', ['locale' => 'lt']));

    $this->get('/lt/news/tag/legacy')
        ->assertRedirect(route('localized.news.index', ['locale' => 'lt']));
});
