<?php

declare(strict_types=1);

use App\Filament\Resources\News\NewsResource;
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

/**
 * @param array<string, mixed> $attributes
 */
function createNewsRecord(array $attributes = []): News
{
    return News::withoutGlobalScopes()->create(array_merge([
        'is_visible'       => true,
        'is_featured'      => false,
        'moderation_state' => 'draft',
        'author_name'      => 'News Author',
        'published_at'     => now()->subMinute(),
    ], $attributes));
}

function addNewsTranslation(
    News $news,
    string $locale,
    string $title,
    string $slug,
    ?string $summary = null,
    ?string $content = null,
): void {
    NewsTranslation::query()->create([
        'news_id' => $news->id,
        'locale'  => $locale,
        'title'   => $title,
        'slug'    => $slug,
        'summary' => $summary,
        'content' => $content,
    ]);
}

it('can render news resource list page component', function (): void {
    Livewire::test(ListNews::class)
        ->assertSuccessful();
});

it('can render news resource index route', function (): void {
    $this->get(NewsResource::getUrl('index'))
        ->assertSuccessful();
});

it('can list news records in table', function (): void {
    $news = createNewsRecord(['author_name' => 'Visible Author']);
    addNewsTranslation($news, 'lt', 'Matoma naujiena', 'matoma-naujiena');

    Livewire::test(ListNews::class)
        ->call('loadTable')
        ->assertCanSeeTableRecords([$news]);
});

it('can search news table by translated title and summary', function (): void {
    $matching = createNewsRecord(['author_name' => 'Matching Author']);
    addNewsTranslation(
        $matching,
        'lt',
        'Specialus paieskos irasas',
        'specialus-paieskos-irasas',
        'Unikali santrauka',
        '<p>Unikalus turinys</p>',
    );

    $other = createNewsRecord(['author_name' => 'Other Author']);
    addNewsTranslation($other, 'lt', 'Kita naujiena', 'kita-naujiena', 'Kita santrauka');

    Livewire::test(ListNews::class)
        ->call('loadTable')
        ->searchTable('Specialus paieskos')
        ->assertCanSeeTableRecords([$matching])
        ->assertCanNotSeeTableRecords([$other]);

    Livewire::test(ListNews::class)
        ->call('loadTable')
        ->searchTable('Unikali santrauka')
        ->assertCanSeeTableRecords([$matching])
        ->assertCanNotSeeTableRecords([$other]);
});

it('can filter news table by moderation state', function (): void {
    $review = createNewsRecord(['moderation_state' => 'review']);
    addNewsTranslation($review, 'lt', 'Review naujiena', 'review-naujiena');

    $published = createNewsRecord(['moderation_state' => 'published']);
    addNewsTranslation($published, 'lt', 'Published naujiena', 'published-naujiena');

    Livewire::test(ListNews::class)
        ->call('loadTable')
        ->filterTable('moderation_state', 'review')
        ->assertCanSeeTableRecords([$review])
        ->assertCanNotSeeTableRecords([$published]);
});

it('can filter news table by visible state', function (): void {
    $visible = createNewsRecord(['is_visible' => true]);
    addNewsTranslation($visible, 'lt', 'Matoma', 'matoma');

    $hidden = createNewsRecord(['is_visible' => false]);
    addNewsTranslation($hidden, 'lt', 'Nematoma', 'nematoma');

    Livewire::test(ListNews::class)
        ->call('loadTable')
        ->filterTable('is_visible', true)
        ->assertCanSeeTableRecords([$visible])
        ->assertCanNotSeeTableRecords([$hidden]);
});

it('can filter news table by featured state', function (): void {
    $featured = createNewsRecord(['is_featured' => true]);
    addNewsTranslation($featured, 'lt', 'Featured', 'featured');

    $plain = createNewsRecord(['is_featured' => false]);
    addNewsTranslation($plain, 'lt', 'Plain', 'plain');

    Livewire::test(ListNews::class)
        ->call('loadTable')
        ->filterTable('is_featured', true)
        ->assertCanSeeTableRecords([$featured])
        ->assertCanNotSeeTableRecords([$plain]);
});

it('generates edit urls with record id when given model instances', function (): void {
    $news = createNewsRecord();
    addNewsTranslation($news, 'lt', 'Model URL', 'model-url');

    $url = NewsResource::getUrl('edit', ['record' => $news]);

    expect($url)
        ->toContain('/admin/news/' . $news->id . '/edit')
        ->not->toContain('/admin/news/model-url/edit');

    $this->get($url)->assertSuccessful();
});

it('generates view urls with record id when given model instances', function (): void {
    $news = createNewsRecord();
    addNewsTranslation($news, 'lt', 'View URL', 'view-url');

    $url = NewsResource::getUrl('view', ['record' => $news]);

    expect($url)
        ->toContain('/admin/news/' . $news->id)
        ->not->toContain('/admin/news/view-url');

    $this->get($url)->assertSuccessful();
});

it('does not return server errors for invalid edit identifiers', function (): void {
    $response = $this->get('/admin/news/sample/edit');

    expect($response->status())->toBeIn([302, 404]);
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
    $news = createNewsRecord([
        'moderation_state' => 'published',
        'author_name'      => 'Original Author',
    ]);
    addNewsTranslation(
        $news,
        'lt',
        'Originali naujiena',
        'originali-naujiena',
        'Originali santrauka',
        '<p>Originalus turinys</p>',
    );

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
    $news = createNewsRecord([
        'moderation_state' => 'draft',
        'author_name'      => 'Delete me',
    ]);
    addNewsTranslation($news, 'lt', 'Šalinama naujiena', 'salinama-naujiena');

    Livewire::test(ListNews::class)
        ->callTableAction('delete', $news)
        ->assertHasNoTableActionErrors();

    $this->assertSoftDeleted('news', [
        'id' => $news->id,
    ]);
});
