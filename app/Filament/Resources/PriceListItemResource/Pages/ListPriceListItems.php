<?php

declare(strict_types=1);

namespace App\Filament\Resources\PriceListItemResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\PriceListItemResource;
use Filament\Actions;
use App\Filament\Pages\Support\BaseListRecords;

final class ListPriceListItems extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = PriceListItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(), // Provide a quick language toggle for the grid view.
            Actions\CreateAction::make(),
        ];
    }
}
