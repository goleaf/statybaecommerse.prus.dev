<?php

declare(strict_types=1);

namespace App\Filament\Resources\SystemResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\SystemResource;
use Filament\Actions;

class ListSystems extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = SystemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
