<?php

declare(strict_types=1);

namespace App\Filament\Resources\CurrencyResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\CurrencyResource;
use Filament\Actions;

final class ListCurrencies extends BaseListRecords
{
    protected static string $resource = CurrencyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
