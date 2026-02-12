<?php

declare(strict_types=1);

use App\Models\Product;
use App\Support\Search\ProductSearch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class)->group('searchable-input');

it('unit: returns formatted product labels for free text search', function (): void {
    $product = Product::query()->create([
        'slug'           => 'test-sku-product',
        'sku'            => 'TEST-SKU',
        'name'           => 'Makita Hammer',
        'price'          => 49.99,
        'manage_stock'   => true,
        'stock_quantity' => 10,
        'is_enabled'     => true,
        'status'         => 'published',
        'published_at'   => Carbon::now()->subDay(),
        'updated_at'     => Carbon::now(),
    ]);

    $results = ProductSearch::byFreeText('Makita');

    expect($results)
        ->toHaveCount(1)
        ->and($results[0])
        ->toContain('TEST-SKU');
});

it('unit: returns search result metadata for product lookups', function (): void {
    $product = Product::query()->create([
        'slug'           => 'meta-001-product',
        'sku'            => 'META-001',
        'price'          => 19.99,
        'name'           => 'Bosch Drill',
        'manage_stock'   => true,
        'stock_quantity' => 8,
        'is_enabled'     => true,
        'status'         => 'published',
        'published_at'   => Carbon::now()->subDay(),
        'updated_at'     => Carbon::now(),
    ]);

    $results = ProductSearch::complex('Bosch');

    expect($results)
        ->toHaveCount(1)
        ->and($results[0]->value())
        ->toEqual((string) $product->getKey())
        ->and($results[0]->get('sku'))
        ->toEqual('META-001')
        ->and($results[0]->get('price'))
        ->toEqual(19.99);
});

it('unit: searches legacy plain-string names without throwing', function (): void {
    Product::query()->create([
        'slug'           => 'legacy-plain-001',
        'sku'            => 'LEGACY-PLAIN-001',
        'name'           => 'Legacy Plain Name Product',
        'price'          => 12.50,
        'manage_stock'   => true,
        'stock_quantity' => 4,
        'is_enabled'     => true,
        'status'         => 'published',
        'published_at'   => Carbon::now()->subDay(),
        'updated_at'     => Carbon::now(),
    ]);

    expect(fn () => ProductSearch::complex('Legacy Plain Name'))->not->toThrow(\Throwable::class);

    $results = ProductSearch::byFreeText('LEGACY-PLAIN-001');

    expect($results)
        ->toHaveCount(1)
        ->and($results[0])
        ->toContain('LEGACY-PLAIN-001');
});
