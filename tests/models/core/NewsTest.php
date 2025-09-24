<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('supports translations and publishes correctly for News model', function (): void {
    // Arrange
    /** @var \App\Models\News $news */
    $news = \App\Models\News::create([
        'is_visible' => true,
        'published_at' => now()->subDay(),
        'author_name' => 'Editor',
    ]);

    // Create translations (lt/en)
    \App\Models\Translations\NewsTranslation::create([
        'news_id' => $news->id,
        'locale' => 'lt',
        'title' => 'Naujiena LT',
        'slug' => 'naujiena-lt',
        'summary' => 'Trumpas aprašymas LT',
        'content' => '<p>Turinys LT</p>',
        'seo_title' => 'SEO pavadinimas LT',
        'seo_description' => 'SEO aprašymas LT',
    ]);

    \App\Models\Translations\NewsTranslation::create([
        'news_id' => $news->id,
        'locale' => 'en',
        'title' => 'News EN',
        'slug' => 'news-en',
        'summary' => 'Short summary EN',
        'content' => '<p>Content EN</p>',
        'seo_title' => 'SEO title EN',
        'seo_description' => 'SEO description EN',
    ]);

    // Act & Assert (lt)
    app()->setLocale('lt');
    $news->load('translations');
    expect($news->trans('title'))->toBe('Naujiena LT');
    expect($news->trans('slug'))->toBe('naujiena-lt');
    expect($news->isPublished())->toBeTrue();

    // Act & Assert (en)
    app()->setLocale('en');
    $news->load('translations');
    expect($news->trans('title'))->toBe('News EN');
    expect($news->trans('slug'))->toBe('news-en');
});

it('enforces unique slug per locale for News', function (): void {
    /** @var \App\Models\News $news */
    $news = \App\Models\News::create([
        'is_visible' => true,
        'published_at' => now()->subHour(),
        'author_name' => 'Editor',
    ]);

    \App\Models\Translations\NewsTranslation::create([
        'news_id' => $news->id,
        'locale' => 'lt',
        'title' => 'Naujiena A',
        'slug' => 'naujiena-a',
    ]);

    expect(function () use ($news): void {
        \App\Models\Translations\NewsTranslation::create([
            'news_id' => $news->id,
            'locale' => 'lt',
            'title' => 'Naujiena A (dup)',
            'slug' => 'naujiena-a',
        ]);
    })->toThrow(\Illuminate\Database\QueryException::class);
});
