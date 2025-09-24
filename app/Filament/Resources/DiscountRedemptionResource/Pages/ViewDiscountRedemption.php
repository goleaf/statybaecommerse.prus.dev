<?php

declare(strict_types=1);

namespace App\Filament\Resources\DiscountRedemptionResource\Pages;

use App\Filament\Resources\DiscountRedemptionResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

final class ViewDiscountRedemption extends ViewRecord
{
    protected static string $resource = DiscountRedemptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
