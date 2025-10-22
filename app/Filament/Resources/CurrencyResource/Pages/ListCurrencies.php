<?php

declare(strict_types=1);

namespace App\Filament\Resources\CurrencyResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\CurrencyResource;
use Filament\Actions;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\ListRecords\Concerns\Translatable as SpatieTranslatableListRecords;

final class ListCurrencies extends BaseListRecords
{
    use SpatieTranslatableListRecords; // Track the active locale for listing translated records.

    protected static string $resource = CurrencyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(), // Provide a quick language toggle for the grid view.
            Actions\CreateAction::make(),
        ];
    }
}
