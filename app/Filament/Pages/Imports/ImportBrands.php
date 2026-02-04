<?php

declare(strict_types=1);

namespace App\Filament\Pages\Imports;

use App\Filament\Imports\BrandImporter;

final class ImportBrands extends CsvImportPage
{
    protected static ?string $slug = 'data-import-export/brands';

    protected static function getImporterClass(): string
    {
        return BrandImporter::class;
    }

    protected static function getImportLabel(): string
    {
        return __('admin.brands_import');
    }
}
