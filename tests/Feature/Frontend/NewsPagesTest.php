<?php declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders localized news index and show pages', function (): void {
    /** @var \App\Models\News $news */
    $news = \App\Models\News::create([
        'is_visible' => true,
        'published_at' => now()->subDay(),
        'author_name' => 'Editor',
    ]);

    \App\Models\Translations\NewsTranslation::create([
        'news_id' => $news->id,
        'locale' => 'lt',
        'title' => 'Naujiena LT',
        'slug' => 'naujiena-lt',
        'summary' => 'Trumpas aprašymas LT',
        'content' => '<p>Turinys LT</p>',
    ]);

    \App\Models\Translations\NewsTranslation::create([
        'news_id' => $news->id,
        'locale' => 'en',
        'title' => 'News EN',
        'slug' => 'news-en',
        'summary' => 'Short summary EN',
        'content' => '<p>Content EN</p>',
    ]);

    // Index
    $this->get('/lt/naujienos')->assertStatus(200);
    $this->get('/en/news')->assertStatus(200);

    // Show
    $this->get('/lt/naujienos/naujiena-lt')->assertStatus(200);
    $this->get('/en/news/news-en')->assertStatus(200);
});
