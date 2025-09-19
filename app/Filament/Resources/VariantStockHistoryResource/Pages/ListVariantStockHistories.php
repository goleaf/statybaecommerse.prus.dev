<?php declare(strict_types=1);

namespace App\Filament\Resources\VariantStockHistoryResource\Pages;

use App\Filament\Resources\VariantStockHistoryResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

final class ListVariantStockHistories extends ListRecords
{
    protected static string $resource = VariantStockHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

