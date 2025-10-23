<?php

declare(strict_types=1);

namespace App\Filament\Resources\ShippingOptionResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\ShippingOptionResource;
use Filament\Actions;
use App\Filament\Pages\Support\BaseListRecords;

final class ListShippingOptions extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = ShippingOptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
