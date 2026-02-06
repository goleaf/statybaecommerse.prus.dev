<?php

declare(strict_types=1);

use App\Filament\Imports\BaseImporter;
use App\Models\Brand;
use App\Models\User;
use App\Services\ImportExport\CsvImportProcessor;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    if (! Schema::hasTable('imports')) {
        Schema::create('imports', function (Blueprint $table): void {
            $table->id();
            $table->timestamp('completed_at')->nullable();
            $table->string('file_name');
            $table->string('file_path');
            $table->string('importer');
            $table->unsignedInteger('processed_rows')->default(0);
            $table->unsignedInteger('total_rows');
            $table->unsignedInteger('successful_rows')->default(0);
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    if (! Schema::hasTable('failed_import_rows')) {
        Schema::create('failed_import_rows', function (Blueprint $table): void {
            $table->id();
            $table->json('data');
            $table->foreignId('import_id')->constrained('imports')->cascadeOnDelete();
            $table->text('validation_error')->nullable();
            $table->timestamps();
        });
    }
});

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

it('clamps imported counters to total rows when chunk overflows configured total', function () {
    $user = User::factory()->create();

    $import = new Import;
    $import->user()->associate($user);
    $import->file_name = 'brands.csv';
    $import->file_path = __FILE__;
    $import->importer = TestBrandImporter::class;
    $import->total_rows = 1;
    $import->processed_rows = 1;
    $import->successful_rows = 1;
    $import->save();

    $columnMap = [
        'name'      => 'name',
        'slug'      => 'slug',
        'is_active' => 'is_active',
    ];

    $importer = new TestBrandImporter($import, $columnMap, []);
    $processor = app(CsvImportProcessor::class);

    $result = $processor->processChunk($import, $importer, [[
        'name'      => 'Overflow Brand',
        'slug'      => 'overflow-brand',
        'is_active' => true,
    ]], $columnMap);

    $import->refresh();

    expect($result['processedRows'])->toBe(1)
        ->and($result['successfulRows'])->toBe(1)
        ->and($import->processed_rows)->toBe(1)
        ->and($import->successful_rows)->toBe(1);
});
