<?php

declare(strict_types=1);

namespace Tests\Feature\Frontend;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

final class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_displays_featured_products_and_brands(): void
    {
        $category = Category::factory()->create(['name' => 'Elektriniai įrankiai']);
        $hiddenCategory = Category::factory()->create(['name' => 'Paslėpta kategorija', 'is_visible' => false]);

        $brand = Brand::factory()->create();

        $featuredProduct = $this->createPublishedProduct([
            'name' => 'Profesionalus gręžtuvas',
            'slug' => 'profesionalus-greztuvas',
            'brand_id' => $brand->id,
            'is_featured' => true,
        ]);
        $featuredProduct->categories()->attach($category->id);

        $hiddenProduct = $this->createPublishedProduct([
            'name' => 'Paslėptas produktas',
            'slug' => 'pasleptas-produktas',
            'status' => 'draft',
            'is_visible' => false,
            'is_featured' => true,
            'published_at' => null,
        ]);
        $hiddenProduct->categories()->attach($category->id);

        $response = $this->get(route('home'));

        $response->assertOk()
            ->assertViewIs('home.index')
            ->assertViewHas('featuredProducts', function ($products) use ($featuredProduct) {
                return $products->contains('id', $featuredProduct->id);
            })
            ->assertViewHas('featuredBrands', function ($brands) use ($brand) {
                return $brands->contains('id', $brand->id);
            })
            ->assertViewHas('categoryTree', function ($categories) use ($category, $hiddenCategory) {
                return $categories->contains('id', $category->id)
                    && $categories->contains('id', $hiddenCategory->id) === false;
            });

        $response->assertDontSee('Paslėptas produktas');
    }

    private function createPublishedProduct(array $overrides = []): Product
    {
        return Product::factory()->create(array_merge([
            'status' => 'published',
            'is_visible' => true,
            'is_enabled' => true,
            'is_featured' => $overrides['is_featured'] ?? false,
            'published_at' => Carbon::now()->subDay(),
        ], $overrides));
    }
}
