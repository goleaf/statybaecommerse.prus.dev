<?php

declare(strict_types=1);

namespace App\Filament\Pages\Imports;

use App\Filament\Imports\PartnerImporter;

final class ImportPartners extends CsvImportPage
{
    protected static ?string $slug = 'data-import-export/partners';

    protected static function getImporterClass(): string
    {
        return PartnerImporter::class;
    }

    protected static function getImportLabel(): string
    {
        return __('admin.partners_import');
    }
}
