<?php

declare(strict_types=1);

use App\Filament\Resources\News\Pages\CreateNews;
use App\Filament\Resources\News\Pages\EditNews;
use App\Filament\Resources\News\Pages\ListNews;
use App\Models\AdminUser;
use App\Models\News;
use App\Models\Translations\NewsTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->admin = AdminUser::factory()->create();
    $this->actingAs($this->admin, 'admin');
});

it('can render news resource list page component', function (): void {
    Livewire::test(ListNews::class)
        ->assertSuccessful();
});

it('can create news with localized content', function (): void {
    Livewire::test(CreateNews::class)
        ->fillForm([
            'moderation_state' => 'published',
            'is_visible'       => true,
            'author_name'      => 'News Admin',
            'title'            => [
                'lt' => 'Testinė naujiena',
                'en' => 'Test news',
            ],
            'slug' => [
                'lt' => 'testine-naujiena',
                'en' => 'test-news',
            ],
            'summary' => [
                'lt' => 'Trumpas aprašymas',
                'en' => 'Short summary',
            ],
            'content' => [
                'lt' => '<p>Turinys</p>',
                'en' => '<p>Content</p>',
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $news = News::withoutGlobalScopes()->where('author_name', 'News Admin')->first();

    expect($news)->not->toBeNull();

    $this->assertDatabaseHas('news_translations', [
        'news_id' => $news?->getKey(),
        'locale'  => 'lt',
        'title'   => 'Testinė naujiena',
        'slug'    => 'testine-naujiena',
    ]);
});

it('can edit news and persist translation changes', function (): void {
    $news = News::withoutGlobalScopes()->create([
        'is_visible'       => true,
        'moderation_state' => 'published',
        'published_at'     => now()->subMinute(),
        'author_name'      => 'Original Author',
    ]);

    NewsTranslation::query()->create([
        'news_id' => $news->id,
        'locale'  => 'lt',
        'title'   => 'Originali naujiena',
        'slug'    => 'originali-naujiena',
        'summary' => 'Originali santrauka',
        'content' => '<p>Originalus turinys</p>',
    ]);

    Livewire::test(EditNews::class, [
        'record' => $news->getKey(),
    ])
        ->fillForm([
            'author_name' => 'Updated Author',
            'title'       => ['lt' => 'Atnaujinta naujiena'],
            'slug'        => ['lt' => 'atnaujinta-naujiena'],
            'summary'     => ['lt' => 'Atnaujinta santrauka'],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('news', [
        'id'          => $news->id,
        'author_name' => 'Updated Author',
    ]);

    $this->assertDatabaseHas('news_translations', [
        'news_id' => $news->id,
        'locale'  => 'lt',
        'title'   => 'Atnaujinta naujiena',
        'slug'    => 'atnaujinta-naujiena',
    ]);
});

it('can soft delete news from table action', function (): void {
    $news = News::withoutGlobalScopes()->create([
        'is_visible'       => true,
        'moderation_state' => 'draft',
        'author_name'      => 'Delete me',
    ]);

    NewsTranslation::query()->create([
        'news_id' => $news->id,
        'locale'  => 'lt',
        'title'   => 'Šalinama naujiena',
        'slug'    => 'salinama-naujiena',
    ]);

    Livewire::test(ListNews::class)
        ->callTableAction('delete', $news)
        ->assertHasNoTableActionErrors();

    $this->assertSoftDeleted('news', [
        'id' => $news->id,
    ]);
});
