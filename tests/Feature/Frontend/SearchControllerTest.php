<?php

declare(strict_types=1);

namespace Tests\Feature\Frontend;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

        $response = $this->get('/lt/search?q=' . urlencode('Kniedė Bralo aliuminis/ plienas standartinė'));

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
}
