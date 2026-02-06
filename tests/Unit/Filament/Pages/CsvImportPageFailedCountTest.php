<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Pages;

use App\Filament\Imports\CategoryImporter;
use App\Filament\Pages\Imports\CsvImportPage;
use Filament\Actions\Imports\Models\Import;
use ReflectionMethod;

it('calculates failed rows from processed minus successful for progress and notifications', function (): void {
    $page = new class extends CsvImportPage
    {
        protected static function getImporterClass(): string
        {
            return CategoryImporter::class;
        }

        protected static function getImportLabel(): string
        {
            return 'Import';
        }
    };

    $method = new ReflectionMethod($page, 'calculateFailedRowsCount');
    $method->setAccessible(true);

    $initial = new Import;
    $initial->total_rows = 100;
    $initial->processed_rows = 0;
    $initial->successful_rows = 0;

    $running = new Import;
    $running->total_rows = 100;
    $running->processed_rows = 40;
    $running->successful_rows = 35;

    $completed = new Import;
    $completed->total_rows = 100;
    $completed->processed_rows = 100;
    $completed->successful_rows = 92;

    expect($method->invoke($page, $initial))->toBe(0)
        ->and($method->invoke($page, $running))->toBe(5)
        ->and($method->invoke($page, $completed))->toBe(8);
});

it('clamps invalid counters when calculating failed rows', function (): void {
    $page = new class extends CsvImportPage
    {
        protected static function getImporterClass(): string
        {
            return CategoryImporter::class;
        }

        protected static function getImportLabel(): string
        {
            return 'Import';
        }
    };

    $method = new ReflectionMethod($page, 'calculateFailedRowsCount');
    $method->setAccessible(true);

    $import = new Import;
    $import->total_rows = 10;
    $import->processed_rows = 50;
    $import->successful_rows = 25;

    expect($method->invoke($page, $import))->toBe(0);
});
