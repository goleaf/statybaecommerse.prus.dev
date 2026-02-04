<?php

declare(strict_types=1);

namespace App\Filament\Pages\Imports;

use App\Filament\Imports\OrganizationImporter;

final class ImportOrganizations extends CsvImportPage
{
    protected static ?string $slug = 'data-import-export/organizations';

    protected static function getImporterClass(): string
    {
        return OrganizationImporter::class;
    }

    protected static function getImportLabel(): string
    {
        return __('admin.organizations_import');
    }
}
