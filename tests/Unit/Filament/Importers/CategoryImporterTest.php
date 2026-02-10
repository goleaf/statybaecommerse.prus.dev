<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Importers;

use App\Filament\Imports\CategoryImporter;
use App\Models\Category;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Models\Import;

it('has correct model', function () {
    $importer = new CategoryImporter(new Import, [], []);
    expect($importer->getModel())->toBe(Category::class);
});

it('has required columns', function () {
    $columns = CategoryImporter::getColumns();

    $columnNames = collect($columns)->map(fn (ImportColumn $column) => $column->getName())->toArray();

    expect($columnNames)->toContain('name')
        ->toContain('slug')
        ->toContain('is_visible')
        ->toContain('is_active');
});

it('has correct notification body', function () {
    $import = new Import;
    $import->successful_rows = 3;

    $body = CategoryImporter::getCompletedNotificationBody($import);

    expect($body)->toContain('Your category import has completed')
        ->toContain('3 rows imported');
});
