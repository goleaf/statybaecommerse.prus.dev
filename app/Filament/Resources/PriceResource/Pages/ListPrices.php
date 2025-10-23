<?php

declare(strict_types=1);

namespace App\Filament\Resources\PriceResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\PriceResource;
use Filament\Actions;
use App\Filament\Pages\Support\BaseListRecords;

final class ListPrices extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = PriceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
