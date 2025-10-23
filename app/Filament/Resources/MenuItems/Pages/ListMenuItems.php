<?php

declare(strict_types=1);

namespace App\Filament\Resources\MenuItems\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\MenuItems\MenuItemResource;
use Filament\Actions\CreateAction;

class ListMenuItems extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = MenuItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
