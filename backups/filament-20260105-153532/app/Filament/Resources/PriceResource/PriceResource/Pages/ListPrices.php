<?php

declare(strict_types=1);

namespace App\Filament\Resources\PriceResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\PriceResource;
use Filament\Actions;

final class ListPrices extends BaseListRecords
{
    protected static string $resource = PriceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
