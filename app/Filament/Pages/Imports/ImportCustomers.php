<?php

declare(strict_types=1);

namespace App\Filament\Pages\Imports;

use App\Filament\Imports\CustomerImporter;

final class ImportCustomers extends CsvImportPage
{
    protected static ?string $slug = 'data-import-export/customers';

    protected static function getImporterClass(): string
    {
        return CustomerImporter::class;
    }

    protected static function getImportLabel(): string
    {
        return __('admin.customers_import');
    }
}
