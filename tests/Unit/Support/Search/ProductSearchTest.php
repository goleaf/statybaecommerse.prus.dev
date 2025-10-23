<?php

declare(strict_types=1);

use App\Models\Product;
use App\Support\Search\ProductSearch;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

uses()->group('searchable-input');

beforeEach(function (): void {
    RefreshDatabaseState::$migrated = true;

    Schema::withoutForeignKeyConstraints(static function (): void {
        Schema::dropIfExists('products');
    });

    Schema::create('products', function (Blueprint $table): void {
        $table->id();
        $table->string('sku')->nullable();
        $table->string('barcode')->nullable();
        $table->json('name')->nullable();
        $table->decimal('price', 10, 2)->nullable();
        $table->boolean('is_active')->default(true);
        $table->boolean('is_visible')->default(true);
        $table->string('status')->nullable();
        $table->timestamp('published_at')->nullable();
        $table->timestamps();
    });
});

it('unit: returns formatted product labels for free text search', function (): void {
    $product = Product::unguarded(fn () => Product::create([
        'sku'          => 'TEST-SKU',
        'name'         => ['en' => 'Makita Hammer', 'lt' => 'Makita Plaktukas'],
        'is_active'    => true,
        'is_visible'   => true,
        'status'       => 'published',
        'published_at' => Carbon::now()->subDay(),
        'updated_at'   => Carbon::now(),
    ]));

    $results = ProductSearch::byFreeText('Makita');

    expect($results)
        ->toHaveCount(1)
        ->and($results[0])
        ->toContain('TEST-SKU');
});

it('unit: returns search result metadata for product lookups', function (): void {
    $product = Product::unguarded(fn () => Product::create([
        'sku'          => 'META-001',
        'price'        => 19.99,
        'name'         => ['en' => 'Bosch Drill', 'lt' => 'Bosch Gręžtuvas'],
        'is_active'    => true,
        'is_visible'   => true,
        'status'       => 'published',
        'published_at' => Carbon::now()->subDay(),
        'updated_at'   => Carbon::now(),
    ]));

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
