<?php

declare(strict_types=1);

use App\Models\Product;
use App\Support\Search\ProductSearch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class)->group('searchable-input');

it('unit: returns formatted product labels for free text search', function (): void {
    $product = Product::factory()->create([
        'sku'          => 'TEST-SKU',
        'name'         => ['en' => 'Makita Hammer', 'lt' => 'Makita Plaktukas'],
        'is_visible'   => true,
        'is_enabled'   => true,
        'status'       => 'published',
        'published_at' => Carbon::now()->subDay(),
        'updated_at'   => Carbon::now(),
    ]);

    $results = ProductSearch::byFreeText('Makita');

    expect($results)
        ->toHaveCount(1)
        ->and($results[0])
        ->toContain('TEST-SKU');
});

it('unit: returns search result metadata for product lookups', function (): void {
    $product = Product::factory()->create([
        'sku'          => 'META-001',
        'price'        => 19.99,
        'name'         => ['en' => 'Bosch Drill', 'lt' => 'Bosch Gręžtuvas'],
        'is_visible'   => true,
        'is_enabled'   => true,
        'status'       => 'published',
        'published_at' => Carbon::now()->subDay(),
        'updated_at'   => Carbon::now(),
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
