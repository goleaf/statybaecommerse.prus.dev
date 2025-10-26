<?php

declare(strict_types=1);

namespace Tests\Models;

use App\Models\News;
use App\Models\NewsTag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionProperty;
use Tests\TestCase;

final class NewsTagTest extends TestCase
{
    use RefreshDatabase;

    public function test_news_tag_configuration_matches_expected_contract(): void
    {
        // Instantiate a fresh model instance to inspect its configuration without touching the database.
        $model = new NewsTag;

        // Confirm the fillable configuration protects against mass-assignment issues.
        self::assertSame([
            'name',
            'slug',
            'description',
            'is_visible',
            'is_active',
            'color',
            'sort_order',
        ], $model->getFillable());

        // Ensure the casts definition keeps booleans and integers strongly typed.
        self::assertSame([
            'is_visible' => 'boolean',
            'is_active'  => 'boolean',
            'sort_order' => 'integer',
        ], $model->getCasts());

        // Validate that the translation model mapping targets the dedicated translation class.
        // Inspect the protected translation model property via reflection to confirm the mapping.
        $translationProperty = new ReflectionProperty(NewsTag::class, 'translationModel');
        $translationProperty->setAccessible(true);
        self::assertSame(\App\Models\Translations\NewsTagTranslation::class, $translationProperty->getValue($model));
    }

    public function test_news_relationship_returns_related_models(): void
    {
        // Create a tag and news article so the pivot relationship can be asserted.
        $tag = NewsTag::factory()->create();
        $news = News::factory()->create();

        // Attach the news item to the tag through the defined belongsToMany relationship.
        $tag->news()->attach($news->getKey());

        // Refresh the relationship and ensure the pivot resolves the expected model instance.
        self::assertTrue($news->is($tag->news()->first()));
    }

    public function test_visible_scope_and_helper_methods(): void
    {
        // Seed a visible and hidden tag to ensure the scope filters correctly.
        $visibleTag = NewsTag::factory()->create(['is_visible' => true]);
        $hiddenTag = NewsTag::factory()->create(['is_visible' => false]);

        // Query using the scope and confirm only the visible tag is returned.
        $visibleIds = NewsTag::query()->visible()->pluck('id')->all();
        self::assertSame([$visibleTag->getKey()], $visibleIds);

        // Ensure the helper reflects the visibility state on each model instance.
        self::assertTrue($visibleTag->isVisible());
        self::assertFalse($hiddenTag->isVisible());
    }

    public function test_ordered_by_name_scope_sorts_alphabetically(): void
    {
        // Create tags with controlled names so the alphabetical order can be asserted deterministically.
        NewsTag::factory()->create(['name' => 'Charlie', 'slug' => 'charlie']);
        NewsTag::factory()->create(['name' => 'Alpha', 'slug' => 'alpha']);
        NewsTag::factory()->create(['name' => 'Bravo', 'slug' => 'bravo']);

        // Execute the custom scope and collect the ordered names for verification.
        $orderedNames = NewsTag::query()->orderedByName()->pluck('name')->all();

        // Confirm the scope sorts results alphabetically regardless of insertion order.
        self::assertSame(['Alpha', 'Bravo', 'Charlie'], $orderedNames);
    }
}
