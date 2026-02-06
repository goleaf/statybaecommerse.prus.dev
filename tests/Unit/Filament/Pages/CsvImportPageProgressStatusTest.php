<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Pages;

use App\Filament\Imports\CategoryImporter;
use App\Filament\Pages\Imports\CsvImportPage;
use Filament\Actions\Imports\Models\Import;
use ReflectionMethod;

it('keeps status running when import counters are zero and import is not completed', function (): void {
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

    $method = new ReflectionMethod($page, 'resolveImportStatus');
    $method->setAccessible(true);

    $import = new Import;
    $import->completed_at = null;

    expect($method->invoke($page, $import, 0, 0))->toBe('running')
        ->and($method->invoke($page, $import, 5, 0))->toBe('running')
        ->and($method->invoke($page, $import, 5, 5))->toBe('completed');
});
