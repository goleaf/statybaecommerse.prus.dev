<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserProductInteractions\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\UserProductInteractions\UserProductInteractionResource;
use Filament\Actions\CreateAction;

class ListUserProductInteractions extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = UserProductInteractionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
