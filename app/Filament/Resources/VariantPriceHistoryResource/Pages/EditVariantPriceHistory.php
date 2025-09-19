<?php declare(strict_types=1);

namespace App\Filament\Resources\VariantPriceHistoryResource\Pages;

use App\Filament\Resources\VariantPriceHistoryResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions;

final class EditVariantPriceHistory extends EditRecord
{
    protected static string $resource = VariantPriceHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

