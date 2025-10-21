<?php

declare(strict_types=1);

namespace App\Filament\Resources\MenuItemResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\MenuItemResource;
use Filament\Actions\CreateAction;

final class ListMenuItems extends BaseListRecords
{
    protected static string $resource = MenuItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
