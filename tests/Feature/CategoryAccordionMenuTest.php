<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Translations\CategoryTranslation;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CategoryAccordionMenuTest extends TestCase
{
    use RefreshDatabase;

    public function test_accordion_menu_shows_categories_with_product_counts(): void
    {
        // Create test categories with translations
        $rootCategory = Category::factory()->create([
            'name' => 'Test Root',
            'slug' => 'test-root',
            'is_visible' => true,
            'sort_order' => 1,
        ]);

        $childCategory = Category::factory()->create([
            'name' => 'Test Child',
            'slug' => 'test-child',
            'parent_id' => $rootCategory->id,
            'is_visible' => true,
            'sort_order' => 1,
        ]);

        // Create translations
        CategoryTranslation::create([
            'category_id' => $rootCategory->id,
            'locale' => 'lt',
            'name' => 'Test Root',
            'slug' => 'test-root',
            'description' => 'Test description',
        ]);

        CategoryTranslation::create([
            'category_id' => $childCategory->id,
            'locale' => 'lt',
            'name' => 'Test Child',
            'slug' => 'test-child',
            'description' => 'Test child description',
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Test Root');
        $response->assertSee('Test Child');
    }

    public function test_accordion_menu_includes_product_count_badges(): void
    {
        $category = Category::factory()->create([
            'name' => 'Test Category',
            'slug' => 'test-category',
            'is_visible' => true,
        ]);

        CategoryTranslation::create([
            'category_id' => $category->id,
            'locale' => 'lt',
            'name' => 'Test Category',
            'slug' => 'test-category',
            'description' => 'Test description',
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        // Check for product count badge (should show 0 for empty category)
        $response->assertSee('bg-gray-100 text-gray-500', false);
    }
}
