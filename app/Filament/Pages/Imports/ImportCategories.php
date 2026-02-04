<?php

declare(strict_types=1);

namespace App\Filament\Pages\Imports;

use App\Filament\Imports\CategoryImporter;

final class ImportCategories extends CsvImportPage
{
    protected static ?string $slug = 'data-import-export/categories';

    protected static function getImporterClass(): string
    {
        return CategoryImporter::class;
    }

    protected static function getImportLabel(): string
    {
        return __('admin.categories_import');
    }
}
