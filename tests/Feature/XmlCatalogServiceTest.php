<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Services\XmlCatalogService;
use Illuminate\Support\Facades\Storage;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('exports and imports categories and products with translations and images', function (): void {
    Storage::fake('public');

    $cat = Category::query()->create(['name' => 'Elektronika', 'slug' => 'elektronika', 'is_enabled' => true, 'is_visible' => true]);
    $cat->updateTranslation('en', ['name' => 'Electronics']);

    $p = Product::query()->create(['name' => 'Telefonas', 'slug' => 'telefonas', 'sku' => 'T-1', 'price' => 9.99, 'is_visible' => true]);
    $p->categories()->attach($cat->id);
    $p->updateTranslation('en', ['name' => 'Phone']);
    ProductImage::query()->create(['product_id' => $p->id, 'path' => 'images/sample.jpg', 'alt_text' => 'Sample', 'sort_order' => 1]);

    $service = app(XmlCatalogService::class);
    $tmp = base_path('storage/testing-catalog.xml');
    @unlink($tmp);
    $xml = $service->export($tmp, ['only' => 'all']);
    expect($xml)->not->toBe('');
    expect(file_exists($tmp))->toBeTrue();

    Category::query()->delete();
    Product::query()->delete();
    ProductImage::query()->delete();

    $res = $service->import($tmp, ['only' => 'all']);
    expect($res['categories']['created'] + $res['products']['created'])->toBeGreaterThan(0);

    $cat2 = Category::query()->where('slug', 'elektronika')->first();
    expect($cat2)->not->toBeNull();
    expect($cat2->hasTranslationFor('en'))->toBeTrue();

    $p2 = Product::query()->where('sku', 'T-1')->first();
    expect($p2)->not->toBeNull();
    expect($p2->categories()->whereKey($cat2->id)->exists())->toBeTrue();
});
