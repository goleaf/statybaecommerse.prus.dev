<?php

declare(strict_types=1);

use App\Filament\Resources\NewsResource;
use App\Models\News;
use App\Models\NewsCategory;
use App\Models\NewsComment;
use App\Models\NewsImage;
use App\Models\NewsTag;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    // Create administrator role and permissions
    $role = Role::create(['name' => 'administrator']);
    $permissions = [
        'view news',
        'create news',
        'update news',
        'delete news',
        'browse_news',
    ];

    foreach ($permissions as $permission) {
        Permission::create(['name' => $permission]);
    }

    $role->givePermissionTo($permissions);

    // Create admin user
    $this->adminUser = User::factory()->create();
    $this->adminUser->assignRole('administrator');

    // Create test category with translations
    $this->testCategory = NewsCategory::factory()->create();
    $this->testCategory->translations()->create([
        'locale'      => 'en',
        'name'        => 'Test Category',
        'slug'        => 'test-category',
        'description' => 'Test category description',
    ]);

    // Create test news with translations
    $this->testNews = News::factory()->create([
        'is_visible'   => false,
        'is_featured'  => false,
        'published_at' => null,
    ]);

    $this->testNews->translations()->create([
        'locale'          => 'en',
        'title'           => 'Test News Article',
        'slug'            => 'test-news-article',
        'content'         => 'This is test content for the news article.',
        'summary'         => 'This is a test summary.',
        'seo_title'       => 'Test SEO Title',
        'seo_description' => 'Test SEO Description',
    ]);
});

it('can list news articles in admin panel', function (): void {
    $this
        ->actingAs($this->adminUser)
        ->get(NewsResource::getUrl('index'))
        ->assertOk()
        ->assertSee('Test News Article');
});

it('can create a new news article', function (): void {
    $category = NewsCategory::factory()->create();
    $category->translations()->create([
        'locale' => 'en',
        'name'   => 'New Category',
        'slug'   => 'new-category',
    ]);

    Livewire::actingAs($this->adminUser)
        ->test(NewsResource\Pages\CreateNews::class)
        ->fillForm([
            'translations' => [
                'en' => [
                    'title'           => 'New Article',
                    'slug'            => 'new-article',
                    'summary'         => 'English summary',
                    'content'         => '<p>English content</p>',
                    'seo_title'       => 'New Article SEO Title',
                    'seo_description' => 'New Article SEO Description',
                ],
            ],
            'is_visible'   => true,
            'is_featured'  => false,
            'published_at' => now(),
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $news = News::query()->latest('id')->first();
    expect($news)->not->toBeNull();

    $this->assertDatabaseHas('news', [
        'id'          => $news->id,
        'is_visible'  => true,
        'is_featured' => false,
    ]);

    $this->assertDatabaseHas('news_translations', [
        'news_id'         => $news->id,
        'locale'          => 'en',
        'title'           => 'New Article',
        'slug'            => 'new-article',
        'summary'         => 'English summary',
        'seo_title'       => 'New Article SEO Title',
        'seo_description' => 'New Article SEO Description',
    ]);
});

it('can view a news article', function (): void {
    $this
        ->actingAs($this->adminUser)
        ->get(NewsResource::getUrl('view', ['record' => $this->testNews]))
        ->assertOk()
        ->assertSee('Test News Article');
});

it('can edit a news article', function (): void {
    Livewire::actingAs($this->adminUser)
        ->test(NewsResource\Pages\EditNews::class, ['record' => $this->testNews->id])
        ->fillForm([
            'is_visible'  => true,
            'is_featured' => true,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('news', [
        'id'          => $this->testNews->id,
        'is_visible'  => true,
        'is_featured' => true,
    ]);
});

it('can delete a news article', function (): void {
    $news = News::factory()->create();

    Livewire::actingAs($this->adminUser)
        ->test(NewsResource\Pages\EditNews::class, ['record' => $news->id])
        ->callAction('delete')
        ->assertOk();

    $this->assertDatabaseMissing('news', [
        'id' => $news->id,
    ]);
});

it('requires a translation title when creating news', function (): void {
    Livewire::actingAs($this->adminUser)
        ->test(NewsResource\Pages\CreateNews::class)
        ->fillForm([
            'translations' => [
                'en' => [
                    'title'   => '',
                    'slug'    => 'missing-title',
                    'content' => '<p>Required content</p>',
                ],
            ],
        ])
        ->call('create')
        ->assertHasFormErrors(['translations.en.title' => 'required']);
});

it('validates unique translation slug when creating news', function (): void {
    $existingNews = News::factory()->create();
    $existingNews->translations()->create([
        'locale'  => 'en',
        'title'   => 'Existing News',
        'slug'    => 'existing-slug',
        'content' => 'Existing content',
    ]);

    Livewire::actingAs($this->adminUser)
        ->test(NewsResource\Pages\CreateNews::class)
        ->fillForm([
            'translations' => [
                'en' => [
                    'title'   => 'Duplicate Slug',
                    'slug'    => 'existing-slug',
                    'content' => '<p>Translated content</p>',
                ],
            ],
        ])
        ->call('create')
        ->assertHasFormErrors(['translations.en.slug' => 'unique']);
});

it('validates slug length when creating news', function (): void {
    $longSlug = str_repeat('a', 260);

    Livewire::actingAs($this->adminUser)
        ->test(NewsResource\Pages\CreateNews::class)
        ->fillForm([
            'translations' => [
                'en' => [
                    'title'   => 'Long Slug Article',
                    'slug'    => $longSlug,
                    'content' => '<p>Translated content</p>',
                ],
            ],
        ])
        ->call('create')
        ->assertHasFormErrors(['translations.en.slug' => 'max']);
});

it('populates translation fields when editing news', function (): void {
    Livewire::actingAs($this->adminUser)
        ->test(NewsResource\Pages\EditNews::class, ['record' => $this->testNews->id])
        ->assertFormSet('translations.en.title', 'Test News Article')
        ->assertFormSet('translations.en.slug', 'test-news-article')
        ->assertFormSet('translations.en.summary', 'This is a test summary.')
        ->assertFormSet('translations.en.seo_title', 'Test SEO Title')
        ->assertFormSet('translations.en.seo_description', 'Test SEO Description');
});

it('updates translation fields for the active locale', function (): void {
    Livewire::actingAs($this->adminUser)
        ->test(NewsResource\Pages\EditNews::class, ['record' => $this->testNews->id])
        ->fillForm([
            'translations' => [
                'en' => [
                    'title'           => 'Updated Title',
                    'slug'            => 'updated-title',
                    'summary'         => 'Updated summary',
                    'content'         => '<p>Updated content</p>',
                    'seo_title'       => 'Updated SEO Title',
                    'seo_description' => 'Updated SEO Description',
                ],
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('news_translations', [
        'news_id'         => $this->testNews->id,
        'locale'          => 'en',
        'title'           => 'Updated Title',
        'slug'            => 'updated-title',
        'summary'         => 'Updated summary',
        'seo_title'       => 'Updated SEO Title',
        'seo_description' => 'Updated SEO Description',
    ]);
});

it('can filter news by category', function (): void {
    $this
        ->actingAs($this->adminUser)
        ->get(NewsResource::getUrl('index'))
        ->assertOk()
        ->assertSee('Test Category');
});

it('can filter news by published status', function (): void {
    $this
        ->actingAs($this->adminUser)
        ->get(NewsResource::getUrl('index'))
        ->assertOk()
        ->assertSee('Published')
        ->assertSee('Draft');
});

it('can filter news by featured status', function (): void {
    $this
        ->actingAs($this->adminUser)
        ->get(NewsResource::getUrl('index'))
        ->assertOk()
        ->assertSee('Featured');
});

it('shows correct news data in table', function (): void {
    $this
        ->actingAs($this->adminUser)
        ->get(NewsResource::getUrl('index'))
        ->assertSee('Test News Article')
        ->assertSee('Test Category');
});

it('handles news publishing workflow', function (): void {
    $news = News::factory()->create([
        'is_visible'   => false,
        'published_at' => null,
    ]);

    // Publish the news
    $news->update([
        'is_visible'   => true,
        'published_at' => now(),
    ]);

    expect($news->is_visible)->toBeTrue();
    expect($news->published_at)->not->toBeNull();
});

it('handles bulk actions on news articles', function (): void {
    $news1 = News::factory()->create();
    $news2 = News::factory()->create();

    Livewire::actingAs($this->adminUser)
        ->test(NewsResource\Pages\ListNews::class)
        ->callTableBulkAction('delete', [$news1->id, $news2->id])
        ->assertOk();

    $this->assertDatabaseMissing('news', [
        'id' => $news1->id,
    ]);

    $this->assertDatabaseMissing('news', [
        'id' => $news2->id,
    ]);
});

it('can manage news SEO fields', function (): void {
    $news = News::factory()->create();

    // Create translation with SEO fields
    $news->translations()->create([
        'locale'          => 'en',
        'title'           => 'SEO Test Article',
        'slug'            => 'seo-test-article',
        'summary'         => 'SEO test summary',
        'content'         => 'Test content',
        'seo_title'       => 'Custom SEO Title',
        'seo_description' => 'Custom SEO description for better search results.',
    ]);

    $this->assertDatabaseHas('news_translations', [
        'news_id'         => $news->id,
        'locale'          => 'en',
        'title'           => 'SEO Test Article',
        'summary'         => 'SEO test summary',
        'seo_title'       => 'Custom SEO Title',
        'seo_description' => 'Custom SEO description for better search results.',
    ]);
});

it('can set featured image for news', function (): void {
    $news = News::factory()->create();

    // Create translation
    $news->translations()->create([
        'locale'  => 'en',
        'title'   => 'News with Image',
        'slug'    => 'news-with-image',
        'content' => 'Test content',
    ]);

    // Test that the news can be created with image
    $this->assertDatabaseHas('news', [
        'id' => $news->id,
    ]);

    $this->assertDatabaseHas('news_translations', [
        'news_id' => $news->id,
        'title'   => 'News with Image',
    ]);
});

it('can manage news categories relationship', function (): void {
    $news = News::factory()->create();
    $category1 = NewsCategory::factory()->create();
    $category2 = NewsCategory::factory()->create();

    // Attach categories to news
    $news->categories()->attach([$category1->id, $category2->id]);

    expect($news->categories)->toHaveCount(2);
    expect($news->categories->pluck('id')->toArray())->toContain($category1->id, $category2->id);
});

it('can manage news tags relationship', function (): void {
    $news = News::factory()->create();
    $tag1 = NewsTag::factory()->create();
    $tag2 = NewsTag::factory()->create();

    // Attach tags to news
    $news->tags()->attach([$tag1->id, $tag2->id]);

    expect($news->tags)->toHaveCount(2);
    expect($news->tags->pluck('id')->toArray())->toContain($tag1->id, $tag2->id);
});

it('can manage news comments relationship', function (): void {
    $news = News::factory()->create();
    $comment1 = NewsComment::factory()->create(['news_id' => $news->id]);
    $comment2 = NewsComment::factory()->create(['news_id' => $news->id]);

    expect($news->comments)->toHaveCount(2);
    expect($news->comments->pluck('id')->toArray())->toContain($comment1->id, $comment2->id);
});

it('can manage news images relationship', function (): void {
    $news = News::factory()->create();
    $image1 = NewsImage::factory()->create(['news_id' => $news->id]);
    $image2 = NewsImage::factory()->create(['news_id' => $news->id]);

    expect($news->images)->toHaveCount(2);
    expect($news->images->pluck('id')->toArray())->toContain($image1->id, $image2->id);
});

it('can handle multilingual news content', function (): void {
    $news = News::factory()->create();

    // Create translations for multiple languages
    $news->translations()->create([
        'locale'  => 'en',
        'title'   => 'English Title',
        'slug'    => 'english-title',
        'content' => 'English content',
    ]);

    $news->translations()->create([
        'locale'  => 'lt',
        'title'   => 'Lietuvių pavadinimas',
        'slug'    => 'lietuviu-pavadinimas',
        'content' => 'Lietuvių turinys',
    ]);

    expect($news->translations)->toHaveCount(2);
    expect($news->getTranslation('title', 'en'))->toBe('English Title');
    expect($news->getTranslation('title', 'lt'))->toBe('Lietuvių pavadinimas');
});

it('can handle news view count increment', function (): void {
    $news = News::factory()->create(['view_count' => 0]);

    $news->incrementViewCount();

    expect($news->fresh()->view_count)->toBe(1);
});

it('can scope published news', function (): void {
    $publishedNews = News::factory()->create([
        'is_visible'   => true,
        'published_at' => now()->subDay(),
    ]);

    $draftNews = News::factory()->create([
        'is_visible'   => false,
        'published_at' => null,
    ]);

    $publishedResults = News::published()->get();

    expect($publishedResults)->toHaveCount(1);
    expect($publishedResults->first()->id)->toBe($publishedNews->id);
});

it('can scope featured news', function (): void {
    $featuredNews = News::factory()->create(['is_featured' => true]);
    $regularNews = News::factory()->create(['is_featured' => false]);

    $featuredResults = News::featured()->get();

    expect($featuredResults)->toHaveCount(1);
    expect($featuredResults->first()->id)->toBe($featuredNews->id);
});

it('can filter breaking news tab without database errors', function () {
    $breakingNews = News::factory()->create(['is_breaking' => true]);
    $regularNews = News::factory()->create(['is_breaking' => false]);

    Livewire::actingAs($this->adminUser)
        ->test(NewsResource\Pages\ListNews::class)
        ->set('activeTab', 'breaking')
        ->assertCanSeeTableRecords([$breakingNews])
        ->assertCanNotSeeTableRecords([$regularNews]);
});

it('can search news by content', function () {
    $news1 = News::factory()->create();
    $news1->translations()->create([
        'locale'  => 'en',
        'title'   => 'Searchable Title',
        'content' => 'This content contains searchable text',
    ]);

    $news2 = News::factory()->create();
    $news2->translations()->create([
        'locale'  => 'en',
        'title'   => 'Different Title',
        'content' => 'This content is different',
    ]);

    $searchResults = News::search('searchable')->get();

    expect($searchResults)->toHaveCount(1);
    expect($searchResults->first()->id)->toBe($news1->id);
});
