<?php

declare(strict_types=1);

namespace App\Filament\Resources\VariantPriceHistoryResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\VariantPriceHistoryResource;
use Filament\Actions;

final class ListVariantPriceHistories extends BaseListRecords
{
    protected static string $resource = VariantPriceHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
