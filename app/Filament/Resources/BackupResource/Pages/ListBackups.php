<?php declare(strict_types=1);

namespace App\Filament\Resources\BackupResource\Pages;

use App\Filament\Resources\BackupResource;
use Filament\Resources\Pages\ListRecords;

final class ListBackups extends ListRecords
{
    protected static string $resource = BackupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions are defined in the resource table headerActions
        ];
    }
}
