<?php

declare(strict_types=1);

namespace App\Filament\Resources\MenuResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\MenuResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

final class ListMenus extends ListRecords
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
