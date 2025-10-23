<?php

declare(strict_types=1);

namespace App\Filament\Resources\DiscountCodeResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\DiscountCodeResource;
use Filament\Actions;
use App\Filament\Pages\Support\BaseListRecords;

final class ListDiscountCodes extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = DiscountCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
