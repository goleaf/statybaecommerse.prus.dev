<?php

declare(strict_types=1);

namespace App\Filament\Resources\PriceListItemResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\PriceListItemResource;
use Filament\Actions;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\ListRecords\Concerns\Translatable as TranslatableListRecords;

final class ListPriceListItems extends BaseListRecords
{
    use TranslatableListRecords;

    protected static string $resource = PriceListItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(),
            Actions\CreateAction::make(),
        ];
    }
}
