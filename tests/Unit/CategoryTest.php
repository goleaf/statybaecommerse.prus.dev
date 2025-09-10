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

    public function test_category_can_have_parent(): void
    {
        $parent = Category::factory()->create(['name' => 'Parent Category']);
        $child = Category::factory()->create([
            'name' => 'Child Category',
            'parent_id' => $parent->id,
        ]);

        $this->assertInstanceOf(Category::class, $child->parent);
        $this->assertEquals($parent->id, $child->parent->id);
    }

    public function test_category_can_have_children(): void
    {
        $parent = Category::factory()->create();
        $children = Category::factory()->count(3)->create(['parent_id' => $parent->id]);

        $this->assertCount(3, $parent->children);
        $this->assertInstanceOf(Category::class, $parent->children->first());
    }

    public function test_category_can_have_many_products(): void
    {
        $category = Category::factory()->create();
        $products = Product::factory()->count(5)->create();

        $category->products()->attach($products->pluck('id'));

        $this->assertCount(5, $category->products);
        $this->assertInstanceOf(Product::class, $category->products->first());
    }

    public function test_category_cache_flush_on_save(): void
    {
        // Mock cache to test if flush is called
        \Illuminate\Support\Facades\Cache::shouldReceive('forget')
            ->atLeast()
            ->once();

        $category = Category::factory()->create();
        $category->save();
    }

    public function test_category_soft_deletes(): void
    {
        $category = Category::factory()->create();
        $categoryId = $category->id;

        $category->delete();

        $this->assertSoftDeleted('categories', ['id' => $categoryId]);
        $this->assertNotNull($category->fresh()->deleted_at);
    }

    public function test_category_fillable_attributes(): void
    {
        $category = new Category();
        
        $expectedFillable = [
            'name',
            'slug',
            'description',
            'parent_id',
            'sort_order',
            'is_enabled',
            'is_visible',
            'seo_title',
            'seo_description',
        ];

        $this->assertEquals($expectedFillable, $category->getFillable());
    }

    public function test_category_casts(): void
    {
        $category = Category::factory()->create([
            'is_visible' => true,
            'sort_order' => 5,
        ]);

        $this->assertIsBool($category->is_visible);
        $this->assertIsInt($category->sort_order);
    }
}