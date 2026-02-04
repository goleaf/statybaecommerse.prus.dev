<?php

declare(strict_types=1);

use App\Filament\Imports\BaseImporter;
use App\Models\Brand;
use App\Models\User;
use App\Services\ImportExport\CsvImportProcessor;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

final class TestBrandImporter extends BaseImporter
{
    protected static ?string $model = Brand::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['required', 'string']),
            ImportColumn::make('slug')
                ->requiredMapping()
                ->rules(['required', 'string']),
            ImportColumn::make('is_active')
                ->boolean()
                ->rules(['nullable', 'boolean']),
        ];
    }

    public function resolveRecord(): Brand
    {
        return new Brand;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        return 'done';
    }
}

it('imports csv rows immediately without queue jobs', function () {
    $user = User::factory()->create();

    $import = new Import;
    $import->user()->associate($user);
    $import->file_name = 'brands.csv';
    $import->file_path = __FILE__;
    $import->importer = TestBrandImporter::class;
    $import->total_rows = 1;
    $import->save();

    $columnMap = [
        'name'      => 'name',
        'slug'      => 'slug',
        'is_active' => 'is_active',
    ];

    $importer = new TestBrandImporter($import, $columnMap, []);
    $processor = app(CsvImportProcessor::class);

    $result = $processor->processChunk($import, $importer, [[
        'name'      => 'Acme',
        'slug'      => 'acme',
        'is_active' => true,
    ]], $columnMap);

    $import->refresh();

    expect($result['processedRows'])->toBe(1)
        ->and($result['successfulRows'])->toBe(1)
        ->and($result['failedRows'])->toBe(0)
        ->and($import->processed_rows)->toBe(1)
        ->and($import->successful_rows)->toBe(1)
        ->and(Brand::withoutGlobalScopes()->where('slug', 'acme')->exists())->toBeTrue();
});
