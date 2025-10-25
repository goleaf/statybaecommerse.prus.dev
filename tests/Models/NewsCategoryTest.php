<?php

declare(strict_types=1);

namespace Tests\Models;

use App\Models\News;
use App\Models\NewsCategory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class NewsCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_fillable_and_casts_configuration_is_explicit(): void
    {
        // Instantiate a fresh model instance so we can inspect its configuration safely.
        $model = new NewsCategory;

        // Validate that the guarded attributes list remains tightly controlled for mass-assignment.
        self::assertSame([
            'name',
            'slug',
            'description',
            'is_visible',
            'parent_id',
            'sort_order',
            'color',
            'icon',
        ], $model->getFillable());

        // Confirm boolean and integer flags are automatically cast for predictable usage in code.
        $casts = $model->getCasts();
        self::assertArrayHasKey('is_visible', $casts);
        self::assertSame('boolean', $casts['is_visible']);
        self::assertArrayHasKey('sort_order', $casts);
        self::assertSame('integer', $casts['sort_order']);
    }

    public function test_slug_is_generated_from_name_when_missing(): void
    {
        // Persist the category without providing a slug so the model boot hook runs.
        $category = NewsCategory::query()->create([
            'name'        => 'My Featured Stories',
            'description' => 'Curated news roundups',
            'is_visible'  => true,
            'sort_order'  => 5,
        ])->fresh();

        // Ensure the category exists so subsequent attribute access stays type-safe for static analysis.
        self::assertNotNull($category);

        // The slug should be derived from the given name using Laravel\Str::slug behaviour.
        self::assertSame(Str::slug('My Featured Stories'), $category->getAttribute('slug'));
    }

    public function test_parent_and_children_relationships_form_hierarchy(): void
    {
        // Create a parent category so we can attach a child and verify the relationship wiring.
        $parent = NewsCategory::factory()->create();
        $child = NewsCategory::factory()->create(['parent_id' => $parent->id]);

        // Refresh the models to load their relationships from the database.
        $parent->unsetRelation('children');
        $child->unsetRelation('parent');

        // Assert the belongs-to and has-many relationships resolve correctly.
        self::assertTrue($parent->is($child->parent));
        self::assertCount(1, $parent->children);
        $firstChild = $parent->children->first();
        self::assertNotNull($firstChild);
        self::assertTrue($child->is($firstChild));
    }

    public function test_news_relationship_returns_related_articles(): void
    {
        // Build a category and a news record, then associate them via the pivot table.
        $category = NewsCategory::factory()->create();
        $news = News::factory()->create();
        $category->news()->attach($news->id);

        // Fetch the relationship and make sure the linked model is present.
        $newsItems = $category->news;
        self::assertInstanceOf(Collection::class, $newsItems);
        $firstNews = $newsItems->first();
        self::assertNotNull($firstNews);
        self::assertTrue($firstNews->is($news));
    }

    public function test_scopes_filter_and_sort_collections(): void
    {
        // Seed visible and hidden categories with varying sort orders and names.
        $visibleAlpha = NewsCategory::factory()->create([
            'name'       => 'Alpha',
            'is_visible' => true,
            'sort_order' => 2,
        ]);
        $visibleBeta = NewsCategory::factory()->create([
            'name'       => 'Beta',
            'is_visible' => true,
            'sort_order' => 1,
        ]);
        $hidden = NewsCategory::factory()->create([
            'name'       => 'Gamma',
            'is_visible' => false,
            'sort_order' => 0,
        ]);

        // The visible scope should exclude the hidden record and preserve the correct count.
        $visibleCategories = NewsCategory::query()->visible()->get();
        self::assertCount(2, $visibleCategories);
        self::assertTrue($visibleCategories->contains($visibleAlpha));
        self::assertTrue($visibleCategories->contains($visibleBeta));
        self::assertFalse($visibleCategories->contains($hidden));

        // Ordered scope must prioritise sort_order and fall back to the primary key for stability.
        $orderedBySort = NewsCategory::query()
            ->whereKey([
                $hidden->getKey(),
                $visibleBeta->getKey(),
                $visibleAlpha->getKey(),
            ])
            ->ordered()
            ->get();

        self::assertSame([
            $visibleBeta->getKey(),
            $visibleAlpha->getKey(),
        ], $orderedBySort->modelKeys());
        self::assertFalse($orderedBySort->contains($hidden));

        // OrderedByName scope should return records in alphabetical order based on the name column.
        $orderedByName = NewsCategory::query()
            ->whereKey([
                $visibleAlpha->getKey(),
                $visibleBeta->getKey(),
                $hidden->getKey(),
            ])
            ->orderedByName()
            ->get();

        self::assertSame([
            $visibleAlpha->getKey(),
            $visibleBeta->getKey(),
        ], $orderedByName->modelKeys());
        self::assertFalse($orderedByName->contains($hidden));
    }

    public function test_is_visible_helper_casts_flag_to_boolean(): void
    {
        // Create a category with the visibility flag disabled.
        $category = NewsCategory::factory()->create(['is_visible' => false]);

        // The helper should return a strict boolean regardless of the underlying storage format.
        self::assertFalse($category->isVisible());
    }
}
