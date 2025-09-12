<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Translations\CategoryTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CategoryNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_sidebar_shows_all_categories(): void
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

        $response = $this->get('/' . app()->getLocale());

        $response->assertStatus(200);
        $response->assertSee('Test Root');
        $response->assertSee('Test Child');
    }

    public function test_category_navigation_links_have_correct_parameters(): void
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

        $response = $this->get('/' . app()->getLocale());

        $response->assertStatus(200);
        // Check that the link uses the correct route parameter
        $response->assertSee('href="' . route('localized.categories.show', ['locale' => app()->getLocale(), 'category' => 'test-category']) . '"', false);
    }

    public function test_category_show_route_works_with_correct_parameter(): void
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

        $response = $this->get(route('localized.categories.show', ['locale' => app()->getLocale(), 'category' => 'test-category']));

        $response->assertStatus(200);
        $response->assertSee('Test Category');
    }

    public function test_category_show_route_fails_without_parameter(): void
    {
        $this->expectException(\Illuminate\Routing\Exceptions\UrlGenerationException::class);
        
        route('localized.categories.show', ['locale' => app()->getLocale()]);
    }
}
