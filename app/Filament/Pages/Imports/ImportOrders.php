<?php

declare(strict_types=1);

namespace App\Filament\Pages\Imports;

use App\Filament\Imports\OrderImporter;

final class ImportOrders extends CsvImportPage
{
    protected static ?string $slug = 'data-import-export/orders';

    protected static function getImporterClass(): string
    {
        return OrderImporter::class;
    }

    protected static function getImportLabel(): string
    {
        return __('admin.orders_import');
    }
}
