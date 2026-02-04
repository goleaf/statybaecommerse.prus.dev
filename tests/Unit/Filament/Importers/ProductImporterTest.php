<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Importers;

use App\Filament\Imports\ProductImporter;
use App\Models\Product;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Models\Import;

it('has correct model', function () {
    $importer = new ProductImporter(new Import, [], []);
    expect($importer->getModel())->toBe(Product::class);
});

it('has required columns', function () {
    $columns = ProductImporter::getColumns();

    $columnNames = collect($columns)->map(fn (ImportColumn $column) => $column->getName())->toArray();

    expect($columnNames)->toContain('name')
        ->toContain('sku')
        ->toContain('price')
        ->toContain('manage_stock')
        ->toContain('stock_quantity');
});

it('has correct notification body', function () {
    $import = new Import;
    $import->successful_rows = 5;

    $body = ProductImporter::getCompletedNotificationBody($import);

    expect($body)->toContain('Your product import has completed')
        ->toContain('5 rows imported');
});
