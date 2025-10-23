<?php

declare(strict_types=1);

namespace App\Filament\Resources\CityResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\CityResource;
use Filament\Actions;

final class ListCities extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = CityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
