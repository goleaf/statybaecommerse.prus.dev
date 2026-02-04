<?php

declare(strict_types=1);

namespace App\Filament\Pages\Imports;

use App\Filament\Imports\UserImporter;

final class ImportUsers extends CsvImportPage
{
    protected static ?string $slug = 'data-import-export/users';

    protected static function getImporterClass(): string
    {
        return UserImporter::class;
    }

    protected static function getImportLabel(): string
    {
        return __('admin.users_import');
    }
}
