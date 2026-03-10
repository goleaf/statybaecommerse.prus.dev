<?php

declare(strict_types=1);

namespace Tests\Feature\Frontend;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class SearchControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_results_include_visible_products_even_when_is_active_flag_is_false(): void
    {
        app()->setLocale('lt');

        $table = (new Product)->getTable();

        $state = [
            'name'         => 'Kniedė Bralo aliuminis/ plienas standartinė(įvairūsdydžiai)',
            'slug'         => 'kniede-bralo-aliuminis-plienas-standartine-test',
            'sku'          => 'BRL-KNIEDE-449',
            'status'       => 'published',
            'is_enabled'   => true,
            'published_at' => now()->subDay(),
        ];

        if (Schema::hasTable($table) && Schema::hasColumn($table, 'is_active')) {
            $state['is_active'] = false;
        }

        $product = Product::factory()->state($state)->create();

        $response = $this->get(route('frontend.search.index', [
            'q' => 'Kniedė Bralo aliuminis/ plienas standartinė',
        ]));

        $response->assertOk()
            ->assertSee($product->name);
    }

    public function test_search_suggestions_can_match_product_sku_for_visible_products(): void
    {
        app()->setLocale('lt');

        $table = (new Product)->getTable();

        $state = [
            'name'         => 'Kniedė Bralo aliuminis/ plienas standartinė',
            'slug'         => 'kniede-bralo-aliuminis-plienas-standartine-suggestions-test',
            'sku'          => 'BRL-SKU-SEARCH-449',
            'status'       => 'published',
            'is_enabled'   => true,
            'published_at' => now()->subDay(),
        ];

        if (Schema::hasTable($table) && Schema::hasColumn($table, 'is_active')) {
            $state['is_active'] = false;
        }

        $product = Product::factory()->state($state)->create();

        $response = $this->getJson(route('frontend.search.suggestions', ['q' => 'BRL-SKU-SEARCH-449']));

        $response->assertOk()
            ->assertJsonFragment([
                'id'   => $product->id,
                'name' => $product->name,
            ]);
    }

    public function test_search_filters_by_category_slug(): void
    {
        app()->setLocale('lt');

        $matchingCategory = Category::factory()->create([
            'name' => 'Elektriniai įrankiai',
            'slug' => 'elektriniai-irankiai',
        ]);
        $otherCategory = Category::factory()->create([
            'name' => 'Saugos priemonės',
            'slug' => 'saugos-priemones',
        ]);

        $matchingProduct = Product::factory()->create([
            'name'         => 'Slug Filter Hammer A',
            'slug'         => 'slug-filter-hammer-a',
            'status'       => 'published',
            'is_enabled'   => true,
            'published_at' => now()->subDay(),
        ]);
        $matchingProduct->categories()->attach($matchingCategory);

        $otherProduct = Product::factory()->create([
            'name'         => 'Slug Filter Hammer B',
            'slug'         => 'slug-filter-hammer-b',
            'status'       => 'published',
            'is_enabled'   => true,
            'published_at' => now()->subDay(),
        ]);
        $otherProduct->categories()->attach($otherCategory);

        $response = $this->get(route('frontend.search.index', [
            'q'        => 'Slug Filter Hammer',
            'category' => $matchingCategory->slug,
        ]));

        $response->assertOk();

        /** @var LengthAwarePaginator<int, Product> $products */
        $products = $response->viewData('products');
        $visibleProductIds = collect($products->items())->pluck('id')->all();

        $this->assertContains($matchingProduct->getKey(), $visibleProductIds);
        $this->assertNotContains($otherProduct->getKey(), $visibleProductIds);
    }

    public function test_search_redirects_numeric_category_query_to_slug(): void
    {
        app()->setLocale('lt');

        $category = Category::factory()->create([
            'name' => 'Matavimo įranga',
            'slug' => 'matavimo-iranga',
        ]);

        $response = $this->get(route('frontend.search.index', [
            'q'        => 'mat',
            'category' => (string) $category->getKey(),
        ]));

        $response->assertStatus(301);

        $location = (string) $response->headers->get('Location');
        $this->assertSame('/search', parse_url($location, PHP_URL_PATH));

        parse_str((string) parse_url($location, PHP_URL_QUERY), $query);
        $this->assertSame('mat', $query['q'] ?? null);
        $this->assertSame($category->slug, $query['category'] ?? null);
    }

    public function test_search_page_renders_homepage_category_menu_in_sidebar(): void
    {
        app()->setLocale('lt');

        Category::factory()->create([
            'name'       => 'Statybinės medžiagos',
            'slug'       => 'statybines-medziagos',
            'is_active'  => true,
            'is_visible' => true,
        ]);

        $response = $this->get(route('frontend.search.index', [
            'q' => 'as',
        ]));

        $response->assertOk()
            ->assertSee('category-sidebar', false)
            ->assertSee('Statybinės medžiagos')
            ->assertDontSee('id="search-category"', false)
            ->assertDontSee(__('frontend.search.refine_title'));
    }
}
