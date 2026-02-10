<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Importers;

use App\Filament\Imports\BrandImporter;
use App\Models\Brand;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Models\Import;

it('has correct model', function () {
    $importer = new BrandImporter(new Import, [], []);
    expect($importer->getModel())->toBe(Brand::class);
});

it('has required columns', function () {
    $columns = BrandImporter::getColumns();

    $columnNames = collect($columns)->map(fn (ImportColumn $column) => $column->getName())->toArray();

    expect($columnNames)->toContain('name')
        ->toContain('slug')
        ->toContain('is_enabled');
});

it('has correct notification body', function () {
    $import = new Import;
    $import->successful_rows = 10;
    $import->processed_rows = 10;
    $import->total_rows = 10;

    $body = BrandImporter::getCompletedNotificationBody($import);

    expect($body)->toContain('Your brand import has completed')
        ->toContain('10 rows imported');
});

it('does not report failed rows before processing has started', function () {
    $import = new Import;
    $import->processed_rows = 0;
    $import->successful_rows = 0;
    $import->total_rows = 50;

    $body = BrandImporter::getCompletedNotificationBody($import);

    expect($body)->not->toContain('failed to import');
});
