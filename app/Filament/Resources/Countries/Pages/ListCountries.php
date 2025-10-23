<?php

declare(strict_types=1);

namespace App\Filament\Resources\Countries\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\Countries\CountryResource;
use Filament\Actions\CreateAction;

class ListCountries extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = CountryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
