<?php declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_can_be_created(): void
    {
        $category = Category::factory()->create([
            'name' => 'Test Category',
            'slug' => 'test-category',
            'is_visible' => true,
        ]);

        $this->assertDatabaseHas('categories', [
            'name' => 'Test Category',
            'slug' => 'test-category',
            'is_visible' => true,
        ]);
    }

    public function test_category_has_parent_relationship(): void
    {
        $parentCategory = Category::factory()->create();
        $childCategory = Category::factory()->create(['parent_id' => $parentCategory->id]);

        $this->assertInstanceOf(Category::class, $childCategory->parent);
        $this->assertEquals($parentCategory->id, $childCategory->parent->id);
    }

    public function test_category_has_children_relationship(): void
    {
        $parentCategory = Category::factory()->create();
        $childCategory1 = Category::factory()->create(['parent_id' => $parentCategory->id]);
        $childCategory2 = Category::factory()->create(['parent_id' => $parentCategory->id]);

        $this->assertCount(2, $parentCategory->children);
        $this->assertInstanceOf(Category::class, $parentCategory->children->first());
    }

    public function test_category_can_have_many_products(): void
    {
        $category = Category::factory()->create();
        $products = Product::factory()->count(3)->create();

        $category->products()->attach($products->pluck('id'));

        $this->assertCount(3, $category->products);
        $this->assertInstanceOf(Product::class, $category->products->first());
    }

    public function test_category_has_media_relationship(): void
    {
        $category = Category::factory()->create();

        // Test that category implements HasMedia
        $this->assertInstanceOf(\Spatie\MediaLibrary\HasMedia::class, $category);
        
        // Test that category can handle media
        $this->assertTrue(method_exists($category, 'registerMediaCollections'));
        $this->assertTrue(method_exists($category, 'registerMediaConversions'));
        $this->assertTrue(method_exists($category, 'media'));
    }

    public function test_category_has_translations_relationship(): void
    {
        $category = Category::factory()->create();

        // Test that category has translations relationship
        $this->assertTrue(method_exists($category, 'translations'));
        $this->assertTrue(method_exists($category, 'trans'));
    }

    public function test_category_scope_active(): void
    {
        $visibleCategory = Category::factory()->create(['is_visible' => true]);
        $hiddenCategory = Category::factory()->create(['is_visible' => false]);

        $activeCategories = Category::active()->get();

        $this->assertTrue($activeCategories->contains($visibleCategory));
        $this->assertFalse($activeCategories->contains($hiddenCategory));
    }

    public function test_category_scope_root(): void
    {
        $rootCategory = Category::factory()->create(['parent_id' => null]);
        $childCategory = Category::factory()->create(['parent_id' => $rootCategory->id]);

        $rootCategories = Category::root()->get();

        $this->assertTrue($rootCategories->contains($rootCategory));
        $this->assertFalse($rootCategories->contains($childCategory));
    }

    public function test_category_scope_ordered(): void
    {
        $category1 = Category::factory()->create(['sort_order' => 2]);
        $category2 = Category::factory()->create(['sort_order' => 1]);
        $category3 = Category::factory()->create(['sort_order' => 3]);

        $orderedCategories = Category::ordered()->get();

        $this->assertEquals($category2->id, $orderedCategories->first()->id);
        $this->assertEquals($category3->id, $orderedCategories->last()->id);
    }

    public function test_category_get_full_name_attribute(): void
    {
        $parentCategory = Category::factory()->create(['name' => 'Parent']);
        $childCategory = Category::factory()->create([
            'name' => 'Child',
            'parent_id' => $parentCategory->id
        ]);

        $fullName = $childCategory->getFullNameAttribute();
        
        // Should include parent name and child name
        $this->assertStringContainsString('Parent', $fullName);
        $this->assertStringContainsString('Child', $fullName);
    }

    public function test_category_get_breadcrumb_attribute(): void
    {
        $parentCategory = Category::factory()->create(['name' => 'Parent']);
        $childCategory = Category::factory()->create([
            'name' => 'Child',
            'parent_id' => $parentCategory->id
        ]);

        $breadcrumb = $childCategory->getBreadcrumbAttribute();

        $this->assertIsArray($breadcrumb);
        $this->assertCount(2, $breadcrumb);
        $this->assertEquals('Parent', $breadcrumb[0]['name']);
        $this->assertEquals('Child', $breadcrumb[1]['name']);
    }

    public function test_category_casts_work_correctly(): void
    {
        $category = Category::factory()->create([
            'is_visible' => true,
            'is_enabled' => false,
            'show_in_menu' => true,
            'sort_order' => 5,
            'product_limit' => 100,
        ]);

        $this->assertIsBool($category->is_visible);
        $this->assertIsBool($category->is_enabled);
        $this->assertIsBool($category->show_in_menu);
        $this->assertIsInt($category->sort_order);
        $this->assertIsInt($category->product_limit);
    }

    public function test_category_route_key_name(): void
    {
        $category = new Category();
        $this->assertEquals('slug', $category->getRouteKeyName());
    }
}