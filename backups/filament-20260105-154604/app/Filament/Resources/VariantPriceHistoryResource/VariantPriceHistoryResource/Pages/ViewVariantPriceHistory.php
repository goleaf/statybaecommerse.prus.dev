<?php

declare(strict_types=1);

namespace App\Filament\Resources\VariantPriceHistoryResource\Pages;

use App\Filament\Resources\VariantPriceHistoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

final class ViewVariantPriceHistory extends ViewRecord
{
    protected static string $resource = VariantPriceHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
