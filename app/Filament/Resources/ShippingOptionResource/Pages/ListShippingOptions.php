<?php

declare(strict_types=1);

namespace App\Filament\Resources\ShippingOptionResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\ShippingOptionResource;
use Filament\Actions;

final class ListShippingOptions extends BaseListRecords
{
    protected static string $resource = ShippingOptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
