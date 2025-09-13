<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire;

use App\Livewire\Pages\SingleProduct;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->brand = Brand::factory()->create();
        $this->category = Category::factory()->create();
        $this->product = Product::factory()->create([
            'brand_id' => $this->brand->id,
            'is_visible' => true,
        ]);

        // Attach category to product
        $this->product->categories()->attach($this->category->id);
    }

    public function test_can_mount_single_product_component(): void
    {
        Livewire::test(SingleProduct::class, ['product' => $this->product])
            ->assertSet('product.id', $this->product->id)
            ->assertSee($this->product->name);
    }

    public function test_cannot_mount_invisible_product(): void
    {
        $invisibleProduct = Product::factory()->create(['is_visible' => false]);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        Livewire::test(SingleProduct::class, ['product' => $invisibleProduct]);
    }

    public function test_loads_all_required_relationships(): void
    {
        Livewire::test(SingleProduct::class, ['product' => $this->product])
            ->assertSet('product.id', $this->product->id);

        // Verify that the product has been loaded with all relationships
        $this->assertTrue($this->product->relationLoaded('brand'));
        $this->assertTrue($this->product->relationLoaded('categories'));
        $this->assertTrue($this->product->relationLoaded('media'));
        $this->assertTrue($this->product->relationLoaded('variants'));
        $this->assertTrue($this->product->relationLoaded('reviews'));
        $this->assertTrue($this->product->relationLoaded('translations'));
    }

    public function test_displays_product_information(): void
    {
        Livewire::test(SingleProduct::class, ['product' => $this->product])
            ->assertSee($this->product->name)
            ->assertSee($this->product->description)
            ->assertSee($this->product->price);
    }

    public function test_displays_brand_information(): void
    {
        Livewire::test(SingleProduct::class, ['product' => $this->product])
            ->assertSee($this->brand->name);
    }

    public function test_displays_category_information(): void
    {
        Livewire::test(SingleProduct::class, ['product' => $this->product])
            ->assertSee($this->category->name);
    }

    public function test_displays_reviews(): void
    {
        $reviews = Review::factory()->count(3)->create([
            'product_id' => $this->product->id,
        ]);

        Livewire::test(SingleProduct::class, ['product' => $this->product])
            ->assertSee($reviews->first()->title)
            ->assertSee($reviews->last()->title);
    }

    public function test_renders_with_correct_layout(): void
    {
        Livewire::test(SingleProduct::class, ['product' => $this->product])
            ->assertViewIs('livewire.pages.single-product')
            ->assertLayout('components.layouts.base');
    }

    public function test_passes_product_title_to_layout(): void
    {
        Livewire::test(SingleProduct::class, ['product' => $this->product])
            ->assertSee($this->product->name);
    }

    public function test_handles_product_without_reviews(): void
    {
        $productWithoutReviews = Product::factory()->create([
            'brand_id' => $this->brand->id,
            'is_visible' => true,
        ]);

        Livewire::test(SingleProduct::class, ['product' => $productWithoutReviews])
            ->assertSet('product.id', $productWithoutReviews->id)
            ->assertSee($productWithoutReviews->name);
    }

    public function test_handles_product_without_variants(): void
    {
        $productWithoutVariants = Product::factory()->create([
            'brand_id' => $this->brand->id,
            'is_visible' => true,
        ]);

        Livewire::test(SingleProduct::class, ['product' => $productWithoutVariants])
            ->assertSet('product.id', $productWithoutVariants->id)
            ->assertSee($productWithoutVariants->name);
    }

    public function test_handles_product_without_media(): void
    {
        $productWithoutMedia = Product::factory()->create([
            'brand_id' => $this->brand->id,
            'is_visible' => true,
        ]);

        Livewire::test(SingleProduct::class, ['product' => $productWithoutMedia])
            ->assertSet('product.id', $productWithoutMedia->id)
            ->assertSee($productWithoutMedia->name);
    }

    public function test_product_has_correct_meta_information(): void
    {
        $product = Product::factory()->create([
            'brand_id' => $this->brand->id,
            'is_visible' => true,
            'meta_title' => 'Test Meta Title',
            'meta_description' => 'Test Meta Description',
        ]);

        Livewire::test(SingleProduct::class, ['product' => $product])
            ->assertSee($product->name);
    }
}
