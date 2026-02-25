<?php

declare(strict_types=1);

namespace App\Filament\Resources\DiscountRedemptionResource\Pages;

use App\Filament\Resources\DiscountRedemptionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDiscountRedemptions extends ListRecords
{
    protected static string $resource = DiscountRedemptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
