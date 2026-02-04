<?php

declare(strict_types=1);

namespace App\Filament\Imports;

use Filament\Actions\Imports\Importer;

abstract class BaseImporter extends Importer
{
    public function getJobConnection(): ?string
    {
        return 'sync';
    }

    /**
     * @return array<string, array<string>>
     */
    public static function getColumnGroups(): array
    {
        return [
            'General' => [],
        ];
    }
}
