<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Resources\CategoryResource;
use App\Filament\Resources\CategoryResource\RelationManagers\ChildrenRelationManager;
use App\Filament\Resources\CategoryResource\RelationManagers\ProductsRelationManager;
use App\Filament\Resources\CategoryResource\RelationManagers\TranslationsRelationManager;
use App\Models\Category;
use App\Models\Product;
use App\Models\Translations\CategoryTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Validate that the category admin resource honours merchandising requirements
 * such as hierarchy, translations, SEO metadata, visibility toggles, custom
 * ordering, and product assignment rules.
 */
final class CategoryResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_resource_exposes_expected_relation_managers(): void
    {
        // Ensure the Filament resource keeps the child, translation, and product panels wired up.
        $relations = CategoryResource::getRelations();

        self::assertContains(ChildrenRelationManager::class, $relations);
        self::assertContains(TranslationsRelationManager::class, $relations);
        self::assertContains(ProductsRelationManager::class, $relations);
    }

    public function test_category_supports_hierarchical_structure(): void
    {
        // Create a parent and child category pair to validate unlimited nesting behaviour.
        $parent = Category::factory()->create(['name' => 'Parent Category']);
        $child = Category::factory()->withParent($parent)->create(['name' => 'Child Category']);

        $parent->unsetRelation('children');
        $loadedChildren = $parent->children;

        self::assertTrue($loadedChildren->contains($child));
        self::assertSame('Parent Category', $child->parent?->name);
    }

    public function test_category_supports_multilingual_content(): void
    {
        // Attach an English translation and confirm the helper resolves locale-specific copy.
        $category = Category::factory()->create(['name' => 'Kategorija']);
        CategoryTranslation::query()->create([
            'category_id' => $category->id,
            'locale'      => 'en',
            'name'        => 'Category',
            'slug'        => 'category',
        ]);

        self::assertSame('Category', $category->trans('name', 'en'));
    }

    public function test_category_seo_fields_are_available(): void
    {
        // Persist SEO metadata and confirm it is directly retrievable for search optimisation.
        $category = Category::factory()->create([
            'seo_title'       => 'Meta Title',
            'seo_description' => 'Meta Description',
        ]);

        self::assertSame('Meta Title', $category->seo_title);
        self::assertSame('Meta Description', $category->seo_description);
    }

    public function test_category_visibility_flags_are_mutable(): void
    {
        // Flip the visibility toggles to prove merchandisers can hide or disable nodes when needed.
        $category = Category::factory()->create([
            'is_active'  => true,
            'is_visible' => true,
            'is_enabled' => true,
        ]);

        $category->update([
            'is_active'  => false,
            'is_visible' => false,
            'is_enabled' => false,
        ]);

        self::assertFalse($category->fresh()->is_active);
        self::assertFalse($category->fresh()->is_visible);
        self::assertFalse($category->fresh()->is_enabled);
    }

    public function test_category_children_respect_custom_sort_order(): void
    {
        // Seed child records with explicit sort orders to confirm the relation honours manual ordering.
        $parent = Category::factory()->create();
        $firstChild = Category::factory()->withParent($parent)->create(['sort_order' => 10, 'name' => 'First']);
        $secondChild = Category::factory()->withParent($parent)->create(['sort_order' => 5, 'name' => 'Second']);

        $orderedNames = $parent->fresh()->children->pluck('name')->all();

        self::assertSame(['Second', 'First'], $orderedNames);
        self::assertSame(5, $secondChild->fresh()->sort_order);
    }

    public function test_category_product_assignment_is_many_to_many(): void
    {
        // Attach multiple products and verify the pivot sync preserves many-to-many relationships.
        $category = Category::factory()->create();
        $products = Product::factory()->count(2)->create();

        $category->products()->sync($products->pluck('id'));

        self::assertCount(2, $category->fresh()->products);
        self::assertEqualsCanonicalizing($products->pluck('id')->all(), $category->products->pluck('id')->all());
    }
}
