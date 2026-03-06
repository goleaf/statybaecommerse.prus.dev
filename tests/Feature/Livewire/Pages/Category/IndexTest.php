<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Pages\Category;

use App\Livewire\Pages\Category\Index;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class IndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_renders_successfully(): void
    {
        Livewire::test(Index::class)
            ->assertStatus(200)
            ->assertSet('isIndex', true);
    }

    public function test_localized_categories_route_renders_when_visible_categories_exist(): void
    {
        Category::factory()->create([
            'is_visible' => true,
        ]);

        $response = $this->get('/lt/categories');

        $response->assertSuccessful();
    }

    public function test_it_filters_categories_by_selected_brand_checkboxes(): void
    {
        $selectedBrand = Brand::factory()->create();
        $otherBrand = Brand::factory()->create();

        $matchingCategory = Category::factory()->create([
            'name'       => 'Matching Brand Category',
            'is_visible' => true,
        ]);

        $otherCategory = Category::factory()->create([
            'name'       => 'Other Brand Category',
            'is_visible' => true,
        ]);

        $selectedBrandProduct = Product::factory()->create([
            'brand_id' => $selectedBrand->id,
        ]);
        $selectedBrandProduct->categories()->attach($matchingCategory->id);

        $otherBrandProduct = Product::factory()->create([
            'brand_id' => $otherBrand->id,
        ]);
        $otherBrandProduct->categories()->attach($otherCategory->id);

        $component = Livewire::test(Index::class)
            ->set('selectedBrandIds', [$selectedBrand->id]);

        /** @var \Illuminate\Contracts\Pagination\LengthAwarePaginator<int, \App\Models\Category> $filteredCategories */
        $filteredCategories = $component->instance()->categories;
        $filteredIds = $filteredCategories->getCollection()->pluck('id')->all();

        $this->assertContains($matchingCategory->id, $filteredIds);
        $this->assertNotContains($otherCategory->id, $filteredIds);
    }
}
