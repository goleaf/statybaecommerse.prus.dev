<?php

declare(strict_types=1);

use App\Filament\Imports\ProductImporter;
use App\Models\Product;
use App\Models\User;
use Filament\Actions\Imports\Jobs\ImportCsv;
use Filament\Actions\Imports\Models\FailedImportRow;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('fails unmatched sync rows when require_existing_sync_match is enabled', function (): void {
    $user = User::factory()->admin()->create();

    Product::factory()->create([
        'name' => 'Existing Product',
        'sku'  => 'EXISTING-001',
    ]);

    $import = new Import;
    $import->user()->associate($user);
    $import->file_name = 'product-import.csv';
    $import->file_path = base_path('storage/imports/product-import.csv');
    $import->importer = ProductImporter::class;
    $import->total_rows = 1;
    $import->save();

    $columns = collect(ProductImporter::getColumns())->map->getName()->values();
    $row = $columns->mapWithKeys(fn (string $name) => [$name => ''])->all();
    $row['name'] = 'New Product Name';
    $row['sku'] = 'MISSING-001';
    $row['price'] = '9.99';

    $columnMap = $columns->mapWithKeys(fn (string $name) => [$name => $name])->all();
    $options = [
        'should_sync'                 => true,
        'sync_keys'                   => [['field' => 'sku']],
        'require_existing_sync_match' => true,
    ];

    (new ImportCsv($import, [$row], $columnMap, $options))->handle();

    $import->refresh();

    expect($import->successful_rows)->toBe(0)
        ->and(FailedImportRow::query()->where('import_id', $import->getKey())->count())->toBe(1)
        ->and(Product::withoutGlobalScopes()->where('sku', 'MISSING-001')->exists())->toBeFalse()
        ->and(Product::withoutGlobalScopes()->count())->toBe(1);
});
