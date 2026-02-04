<?php

declare(strict_types=1);

namespace App\Filament\Pages\Imports;

use App\Filament\Imports\SubscriberImporter;

final class ImportSubscribers extends CsvImportPage
{
    protected static ?string $slug = 'data-import-export/subscribers';

    protected static function getImporterClass(): string
    {
        return SubscriberImporter::class;
    }

    protected static function getImportLabel(): string
    {
        return __('admin.subscribers_import');
    }
}
