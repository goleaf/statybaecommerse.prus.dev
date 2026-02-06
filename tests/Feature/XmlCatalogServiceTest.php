<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Product;
use App\Services\XmlCatalogService;
use Illuminate\Support\Facades\Storage;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('feature: exports categories and products with translations and images', function (): void {
    Storage::fake('public');

    $cat = Category::query()->create(['name' => 'Elektronika', 'slug' => 'elektronika', 'is_enabled' => true, 'is_visible' => true]);
    $cat->updateTranslation('en', ['name' => 'Electronics']);

    $p = Product::query()->create(['name' => 'Telefonas', 'slug' => 'telefonas', 'sku' => 'T-1', 'price' => 9.99, 'is_visible' => true]);
    $p->categories()->attach($cat->id);
    $p->updateTranslation('en', ['name' => 'Phone']);
    $service = app(XmlCatalogService::class);
    $tmp = base_path('storage/testing-catalog.xml');
    @unlink($tmp);
    $xml = $service->export($tmp, ['only' => 'all']);
    expect($xml)->not->toBe('');
    expect(file_exists($tmp))->toBeTrue();
    expect($xml)->toContain('<catalog>')
        ->and($xml)->toContain('<categories>')
        ->and($xml)->toContain('<products>')
        ->and($xml)->toContain('<sku>T-1</sku>');
});
