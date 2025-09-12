<?php declare(strict_types=1);

use App\Models\Translations\BrandTranslation;
use App\Models\Brand;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('BrandController', function () {
    it('shows brand page with translated content', function () {
        $brand = Brand::factory()->create([
            'name' => 'Test Brand',
            'slug' => 'test-brand',
            'description' => 'Test description',
            'website' => 'https://testbrand.com',
            'is_enabled' => true,
        ]);

        $translation = BrandTranslation::factory()->create([
            'brand_id' => $brand->id,
            'locale' => 'lt',
            'name' => 'Test Brand LT',
            'slug' => 'test-brand-lt',
            'description' => 'Test aprašymas lietuvių kalba',
            'seo_title' => 'SEO Title LT',
            'seo_description' => 'SEO Description LT',
        ]);

        $product = Product::factory()->create([
            'brand_id' => $brand->id,
            'is_visible' => true,
            'published_at' => now(),
        ]);

        app()->setLocale('lt');

        $response = $this->get('/lt/brands/test-brand-lt');

        $response->assertStatus(200);
        $response->assertViewIs('brands.show');
        $response->assertViewHas('brand', $brand);
        $response->assertSee('Test Brand LT');
        $response->assertSee('Test aprašymas lietuvių kalba');
        $response->assertSee('https://testbrand.com');
    });

    it('redirects to canonical slug when accessing non-canonical slug', function () {
        $brand = Brand::factory()->create([
            'slug' => 'canonical-brand',
            'is_enabled' => true,
        ]);

        $translation = BrandTranslation::factory()->create([
            'brand_id' => $brand->id,
            'locale' => 'lt',
            'slug' => 'canonical-brand-lt',
        ]);

        app()->setLocale('lt');

        $response = $this->get('/lt/brands/canonical-brand');

        $response->assertRedirect('/lt/brands/canonical-brand-lt');
        $response->assertStatus(301);
    });

    it('returns 404 for non-existent brand', function () {
        $response = $this->get('/lt/brands/non-existent-brand');

        $response->assertStatus(404);
    });

    it('returns 404 for disabled brand', function () {
        $brand = Brand::factory()->create([
            'slug' => 'disabled-brand',
            'is_enabled' => false,
        ]);

        $response = $this->get('/lt/brands/disabled-brand');

        $response->assertStatus(404);
    });

    it('shows products for brand', function () {
        $brand = Brand::factory()->create([
            'slug' => 'brand-with-products',
            'is_enabled' => true,
        ]);

        $products = Product::factory()->count(3)->create([
            'brand_id' => $brand->id,
            'is_visible' => true,
            'published_at' => now(),
        ]);

        $response = $this->get('/lt/brands/brand-with-products');

        $response->assertStatus(200);
        $response->assertViewHas('products');

        $viewProducts = $response->viewData('products');
        expect($viewProducts)->toHaveCount(3);
    });

    it('does not show invisible products', function () {
        $brand = Brand::factory()->create([
            'slug' => 'brand-with-mixed-products',
            'is_enabled' => true,
        ]);

        $visibleProduct = Product::factory()->create([
            'brand_id' => $brand->id,
            'is_visible' => true,
            'published_at' => now(),
        ]);

        $invisibleProduct = Product::factory()->create([
            'brand_id' => $brand->id,
            'is_visible' => false,
            'published_at' => now(),
        ]);

        $response = $this->get('/lt/brands/brand-with-mixed-products');

        $response->assertStatus(200);
        $response->assertViewHas('products');

        $viewProducts = $response->viewData('products');
        expect($viewProducts)->toHaveCount(1);
        expect($viewProducts->first()->id)->toBe($visibleProduct->id);
    });

    it('does not show unpublished products', function () {
        $brand = Brand::factory()->create([
            'slug' => 'brand-with-unpublished-products',
            'is_enabled' => true,
        ]);

        $publishedProduct = Product::factory()->create([
            'brand_id' => $brand->id,
            'is_visible' => true,
            'published_at' => now(),
        ]);

        $unpublishedProduct = Product::factory()->create([
            'brand_id' => $brand->id,
            'is_visible' => true,
            'published_at' => null,
        ]);

        $response = $this->get('/lt/brands/brand-with-unpublished-products');

        $response->assertStatus(200);
        $response->assertViewHas('products');

        $viewProducts = $response->viewData('products');
        expect($viewProducts)->toHaveCount(1);
        expect($viewProducts->first()->id)->toBe($publishedProduct->id);
    });

    it('limits products to 12 per page', function () {
        $brand = Brand::factory()->create([
            'slug' => 'brand-with-many-products',
            'is_enabled' => true,
        ]);

        $products = Product::factory()->count(15)->create([
            'brand_id' => $brand->id,
            'is_visible' => true,
            'published_at' => now(),
        ]);

        $response = $this->get('/lt/brands/brand-with-many-products');

        $response->assertStatus(200);
        $response->assertViewHas('products');

        $viewProducts = $response->viewData('products');
        expect($viewProducts)->toHaveCount(12);
    });

    it('generates correct SEO title and description', function () {
        $brand = Brand::factory()->create([
            'slug' => 'seo-brand',
            'name' => 'SEO Brand',
            'description' => 'SEO Description',
            'is_enabled' => true,
        ]);

        $translation = BrandTranslation::factory()->create([
            'brand_id' => $brand->id,
            'locale' => 'lt',
            'seo_title' => 'Custom SEO Title',
            'seo_description' => 'Custom SEO Description',
        ]);

        app()->setLocale('lt');

        $response = $this->get('/lt/brands/seo-brand');

        $response->assertStatus(200);
        $response->assertViewHas('seoTitle', 'Custom SEO Title');
        $response->assertViewHas('seoDescription', 'Custom SEO Description');
    });

    it('falls back to default SEO when translation SEO is not available', function () {
        $brand = Brand::factory()->create([
            'slug' => 'fallback-seo-brand',
            'name' => 'Fallback SEO Brand',
            'description' => 'Fallback SEO Description',
            'is_enabled' => true,
        ]);

        $translation = BrandTranslation::factory()->create([
            'brand_id' => $brand->id,
            'locale' => 'lt',
            'name' => 'Translated Name',
            // No SEO fields
        ]);

        app()->setLocale('lt');

        $response = $this->get('/lt/brands/fallback-seo-brand');

        $response->assertStatus(200);
        $response->assertViewHas('seoTitle', 'Translated Name - ' . config('app.name'));
        $response->assertViewHas('seoDescription', null);
    });
});
