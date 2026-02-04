<?php

declare(strict_types=1);

namespace App\Filament\Pages\Imports;

use App\Filament\Imports\PriceImporter;

final class ImportPrices extends CsvImportPage
{
    protected static ?string $slug = 'data-import-export/prices';

    protected static function getImporterClass(): string
    {
        return PriceImporter::class;
    }

    protected static function getImportLabel(): string
    {
        return __('admin.prices_import');
    }
}
