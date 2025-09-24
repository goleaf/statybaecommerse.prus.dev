<?php

declare(strict_types=1);

namespace App\Filament\Resources\VariantPriceHistoryResource\Pages;

use App\Filament\Resources\VariantPriceHistoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

final class ListVariantPriceHistories extends ListRecords
{
    protected static string $resource = VariantPriceHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
