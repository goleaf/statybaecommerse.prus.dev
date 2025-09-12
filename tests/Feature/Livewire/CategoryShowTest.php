<?php declare(strict_types=1);

namespace Tests\Feature\Livewire;

use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\Brand;
use App\Livewire\Pages\Category\Show;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CategoryShowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a visible category
        $this->category = Category::factory()->create([
            'is_visible' => true,
            'name' => 'Test Category',
            'slug' => 'test-category',
        ]);
    }

    public function test_can_mount_category_show_component(): void
    {
        Livewire::test(Show::class, ['category' => $this->category])
            ->assertSet('category.id', $this->category->id)
            ->assertSee('Test Category');
    }

    public function test_cannot_mount_invisible_category(): void
    {
        $invisibleCategory = Category::factory()->create(['is_visible' => false]);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        
        Livewire::test(Show::class, ['category' => $invisibleCategory]);
    }

    public function test_loads_media_and_translations_relationships(): void
    {
        Livewire::test(Show::class, ['category' => $this->category])
            ->assertSet('category.id', $this->category->id);
        
        // Verify that the category has been loaded with relationships
        $this->assertTrue($this->category->relationLoaded('media'));
        $this->assertTrue($this->category->relationLoaded('translations'));
    }

    public function test_displays_products_in_category(): void
    {
        $brand = Brand::factory()->create();
        $products = Product::factory()->count(3)->create([
            'brand_id' => $brand->id,
            'is_visible' => true,
        ]);

        // Attach products to category
        $this->category->products()->attach($products->pluck('id'));

        Livewire::test(Show::class, ['category' => $this->category])
            ->assertSee($products->first()->name)
            ->assertSee($products->last()->name);
    }

    public function test_only_shows_visible_products(): void
    {
        $brand = Brand::factory()->create();
        $visibleProduct = Product::factory()->create([
            'brand_id' => $brand->id,
            'is_visible' => true,
        ]);
        $hiddenProduct = Product::factory()->create([
            'brand_id' => $brand->id,
            'is_visible' => false,
        ]);

        // Attach both products to category
        $this->category->products()->attach([$visibleProduct->id, $hiddenProduct->id]);

        Livewire::test(Show::class, ['category' => $this->category])
            ->assertSee($visibleProduct->name)
            ->assertDontSee($hiddenProduct->name);
    }

    public function test_products_are_paginated(): void
    {
        $brand = Brand::factory()->create();
        $products = Product::factory()->count(15)->create([
            'brand_id' => $brand->id,
            'is_visible' => true,
        ]);

        // Attach products to category
        $this->category->products()->attach($products->pluck('id'));

        Livewire::test(Show::class, ['category' => $this->category])
            ->assertSee('Test Category')
            ->assertSee($products->first()->name);
    }

    public function test_can_change_sort_order(): void
    {
        $brand = Brand::factory()->create();
        $product1 = Product::factory()->create([
            'brand_id' => $brand->id,
            'is_visible' => true,
            'name' => 'Product A',
            'created_at' => now()->subDay(),
        ]);
        $product2 = Product::factory()->create([
            'brand_id' => $brand->id,
            'is_visible' => true,
            'name' => 'Product B',
            'created_at' => now(),
        ]);

        // Attach products to category
        $this->category->products()->attach([$product1->id, $product2->id]);

        $component = Livewire::test(Show::class, ['category' => $this->category])
            ->assertSet('sortBy', 'created_at')
            ->assertSet('sortDirection', 'desc');

        // Test sorting by name ascending
        $component->set('sortBy', 'name')
            ->set('sortDirection', 'asc')
            ->assertSet('sortBy', 'name')
            ->assertSet('sortDirection', 'asc');
    }

    public function test_renders_with_correct_layout(): void
    {
        Livewire::test(Show::class, ['category' => $this->category])
            ->assertViewIs('livewire.pages.category.show')
            ->assertLayout('components.layouts.base');
    }

    public function test_passes_category_title_to_layout(): void
    {
        Livewire::test(Show::class, ['category' => $this->category])
            ->assertSee('Test Category');
    }

    public function test_products_property_returns_paginated_results(): void
    {
        $brand = Brand::factory()->create();
        $products = Product::factory()->count(5)->create([
            'brand_id' => $brand->id,
            'is_visible' => true,
        ]);

        // Attach products to category
        $this->category->products()->attach($products->pluck('id'));

        $component = Livewire::test(Show::class, ['category' => $this->category]);
        
        $productsProperty = $component->get('products');
        
        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $productsProperty);
        $this->assertCount(5, $productsProperty->items());
    }

    public function test_products_are_loaded_with_brand_and_media(): void
    {
        $brand = Brand::factory()->create();
        $product = Product::factory()->create([
            'brand_id' => $brand->id,
            'is_visible' => true,
        ]);

        // Attach product to category
        $this->category->products()->attach($product->id);

        $component = Livewire::test(Show::class, ['category' => $this->category]);
        
        $productsProperty = $component->get('products');
        $firstProduct = $productsProperty->items()[0];
        
        // Verify that products are loaded with brand and media relationships
        $this->assertTrue($firstProduct->relationLoaded('brand'));
        $this->assertTrue($firstProduct->relationLoaded('media'));
    }
}
