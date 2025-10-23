<?php

declare(strict_types=1);

namespace App\Filament\Resources\VariantPriceHistoryResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\VariantPriceHistoryResource;
use Filament\Actions;
use App\Filament\Pages\Support\BaseListRecords;

final class ListVariantPriceHistories extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = VariantPriceHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
