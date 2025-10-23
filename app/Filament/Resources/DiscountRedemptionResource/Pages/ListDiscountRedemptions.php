<?php

declare(strict_types=1);

namespace App\Filament\Resources\DiscountRedemptionResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\DiscountRedemptionResource;
use Filament\Actions;

final class ListDiscountRedemptions extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = DiscountRedemptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
