<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\NewsResource\Pages\CreateNews;
use App\Models\News;
use App\Models\NewsCategory;
use App\Models\NewsTag;
use App\Models\User;
use App\Services\Cart\CartService;
use App\Services\Shared\CacheService;
use Livewire\Livewire;
use Tests\Concerns\InteractsWithFilamentPivotTables;
use Tests\TestCase;

final class NewsResourcePivotSyncTest extends TestCase
{
    use InteractsWithFilamentPivotTables;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureFilamentPivotTablesMigrated();
        $this->resetFilamentPivotTables();
        $this->resolveAdminPanel();

        config([
            'app.locale'                              => 'en',
            'app.fallback_locale'                     => 'en',
            'filament-language-tabs.default_locales'  => ['en'],
            'filament-language-tabs.required_locales' => ['en'],
            'activitylog.enabled'                     => false,
        ]);

        app()->setLocale('en');

        app()->singleton(CartService::class, fn () => new class
        {
            public function getCount(?int $userId, ?string $sessionId): int
            {
                return 0;
            }

            /**
             * @return array<int, array<string, mixed>>
             */
            public function getSessionItems(): array
            {
                return [];
            }

            public function getSessionCount(): int
            {
                return 0;
            }
        });

        app()->singleton(CacheService::class, fn () => new class
        {
            public function rememberShort(string $key, callable $callback, ?int $ttl = null): mixed
            {
                return collect();
            }

            public function rememberDefault(string $key, callable $callback, ?int $ttl = null): mixed
            {
                return collect();
            }

            public function rememberLong(string $key, callable $callback, ?int $ttl = null): mixed
            {
                return collect();
            }

            public function forgetPattern(string $pattern): void {}

            public function generateProductKey(int $productId, string $locale, string $currency): string
            {
                return '';
            }

            public function generateCategoryKey(int $categoryId, string $locale): string
            {
                return '';
            }

            public function generateBrandKey(int $brandId, string $locale): string
            {
                return '';
            }

            public function generateCollectionKey(int $collectionId, string $locale): string
            {
                return '';
            }

            public function generateHomeKey(string $section, string $locale, ?string $currency = null): string
            {
                return '';
            }

            public function invalidateProductCache(int $productId): void {}

            public function invalidateCategoryCache(int $categoryId): void {}
        });

        $this->admin = User::factory()->create([
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]);

        $this->actingAs($this->admin);
    }

    public function test_it_synchronizes_categories_and_tags_on_create(): void
    {
        $categories = NewsCategory::factory()->count(2)->state(['is_visible' => true])->create();

        foreach ($categories as $index => $category) {
            $category->translations()->create([
                'locale' => 'en',
                'name'   => "Category {$index}",
                'slug'   => "category-{$index}",
            ]);

            $category->forceFill([
                'name' => "Category {$index}",
                'slug' => "category-{$index}",
            ])->save();
        }

        $tags = NewsTag::factory()->count(2)->state(['is_visible' => true])->create();

        foreach ($tags as $index => $tag) {
            $tag->translations()->create([
                'locale' => 'en',
                'name'   => "Tag {$index}",
                'slug'   => "tag-{$index}",
            ]);

            $tag->forceFill([
                'name' => "Tag {$index}",
                'slug' => "tag-{$index}",
            ])->save();
        }

        Livewire::test(CreateNews::class)
            ->fillForm([
                'title'        => ['en' => 'Integration Title'],
                'slug'         => ['en' => 'integration-title'],
                'summary'      => ['en' => 'Integration summary'],
                'content'      => ['en' => '<p>Integration content</p>'],
                'published_at' => now()->format('Y-m-d H:i:s'),
                'author_name'  => 'Integration Author',
                'categories'   => $categories->pluck('id')->all(),
                'tags'         => $tags->pluck('id')->all(),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $news = News::withoutGlobalScopes()->first();

        $this->assertNotNull($news);

        $this->assertEqualsCanonicalizing(
            $categories->pluck('id')->all(),
            $news->categories()->pluck('news_categories.id')->all()
        );

        $this->assertEqualsCanonicalizing(
            $tags->pluck('id')->all(),
            $news->tags()->pluck('news_tags.id')->all()
        );

        foreach ($categories as $category) {
            $this->assertDatabaseHas('news_category_pivot', [
                'news_id'          => $news->getKey(),
                'news_category_id' => $category->getKey(),
            ]);
        }

        foreach ($tags as $tag) {
            $this->assertDatabaseHas('news_tag_pivot', [
                'news_id'     => $news->getKey(),
                'news_tag_id' => $tag->getKey(),
            ]);
        }
    }
}
