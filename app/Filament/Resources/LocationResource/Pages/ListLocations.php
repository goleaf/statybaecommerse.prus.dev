<?php

declare(strict_types=1);

namespace App\Filament\Resources\LocationResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\LocationResource;
use Filament\Actions;
use App\Filament\Pages\Support\BaseListRecords;

final class ListLocations extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = LocationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
