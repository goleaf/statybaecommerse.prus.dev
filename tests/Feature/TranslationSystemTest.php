<?php declare(strict_types=1);

use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Translations\ProductTranslation;
use App\Services\MultiLanguageTabService;

it('can create product with translations', function () {
    $product = Product::factory()->create([
        'name' => 'Test Product',
        'slug' => 'test-product',
    ]);
    
    // Create translations
    ProductTranslation::create([
        'product_id' => $product->id,
        'locale' => 'en',
        'name' => 'Test Product EN',
        'slug' => 'test-product-en',
        'description' => 'English description',
    ]);
    
    ProductTranslation::create([
        'product_id' => $product->id,
        'locale' => 'lt',
        'name' => 'Testas Produktas',
        'slug' => 'testas-produktas',
        'description' => 'Lietuviškas aprašymas',
    ]);
    
    expect($product->translations)->toHaveCount(2);
    expect($product->translations->where('locale', 'en')->first()->name)->toBe('Test Product EN');
    expect($product->translations->where('locale', 'lt')->first()->name)->toBe('Testas Produktas');
});

it('multi language service returns available languages', function () {
    $languages = MultiLanguageTabService::getAvailableLanguages();
    
    expect($languages)->toBeArray();
    expect($languages)->not()->toBeEmpty();
    expect($languages[0])->toHaveKeys(['code', 'name', 'flag']);
});

it('can get language names and flags', function () {
    expect(MultiLanguageTabService::getLanguageName('en'))->toBe('English');
    expect(MultiLanguageTabService::getLanguageName('lt'))->toBe('Lietuvių');
    expect(MultiLanguageTabService::getLanguageFlag('en'))->toBe('🇬🇧');
    expect(MultiLanguageTabService::getLanguageFlag('lt'))->toBe('🇱🇹');
});

it('product has translation relationship', function () {
    $product = Product::factory()->create();
    
    expect($product->translations())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
});

it('brand has translation relationship', function () {
    $brand = Brand::factory()->create();
    
    expect($brand->translations())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
});

it('category has translation relationship', function () {
    $category = Category::factory()->create();
    
    expect($category->translations())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
});
