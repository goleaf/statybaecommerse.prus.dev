<?php

declare(strict_types=1);

namespace App\Filament\Resources\Countries\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\Countries\CountryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCountries extends ListRecords
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
