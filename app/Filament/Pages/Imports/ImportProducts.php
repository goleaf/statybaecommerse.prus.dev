<?php

declare(strict_types=1);

namespace App\Filament\Pages\Imports;

use App\Filament\Imports\ProductImporter;

final class ImportProducts extends CsvImportPage
{
    protected static ?string $slug = 'data-import-export/products';

    protected static function getImporterClass(): string
    {
        return ProductImporter::class;
    }

    protected static function getImportLabel(): string
    {
        return __('admin.products_import');
    }
}
