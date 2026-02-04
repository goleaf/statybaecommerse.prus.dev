<?php

declare(strict_types=1);

namespace App\Filament\Pages\Imports;

use App\Filament\Imports\DiscountImporter;

final class ImportDiscounts extends CsvImportPage
{
    protected static ?string $slug = 'data-import-export/discounts';

    protected static function getImporterClass(): string
    {
        return DiscountImporter::class;
    }

    protected static function getImportLabel(): string
    {
        return __('admin.discounts_import');
    }
}
