<?php

declare(strict_types=1);

use App\Filament\Imports\ProductImporter;
use App\Models\Product;
use App\Models\User;
use Filament\Actions\Imports\Jobs\ImportCsv;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

it('imports a fully mapped product row with blank optional fields', function (): void {
    $user = User::factory()->admin()->create();

    $import = new Import;
    $import->user()->associate($user);
    $import->file_name = 'product-import.csv';
    $import->file_path = base_path('storage/imports/product-import.csv');
    $import->importer = ProductImporter::class;
    $import->total_rows = 1;
    $import->save();

    $columns = collect(ProductImporter::getColumns())->map->getName()->values();
    $row = $columns->mapWithKeys(fn (string $name) => [$name => ''])->all();
    $row['name'] = 'Test Product';
    $row['price'] = '6.0';
    $row['sku'] = 'SKU-TEST-1';

    $columnMap = $columns->mapWithKeys(fn (string $name) => [$name => $name])->all();

    (new ImportCsv($import, [$row], $columnMap, []))->handle();

    $import->refresh();

    expect($import->successful_rows)->toBe(1)
        ->and($import->failedRows)->toHaveCount(0);

    $product = Product::withoutGlobalScopes()->first();

    expect($product)->not->toBeNull()
        ->and($product->name)->toBe('Test Product')
        ->and($product->slug)->toBe(Str::slug('Test Product'))
        ->and((float) $product->price)->toBe(6.0)
        ->and($product->status)->toBe('published')
        ->and($product->is_visible)->toBeTrue()
        ->and($product->is_enabled)->toBeTrue()
        ->and($product->published_at)->not->toBeNull();

    expect(Product::query()->count())->toBe(1);
});
