<?php

declare(strict_types=1);

namespace App\Filament\Resources\MenuResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\MenuResource;
use Filament\Actions;
use App\Filament\Pages\Support\BaseListRecords;

final class ListMenus extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = MenuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
