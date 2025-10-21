<?php

namespace App\Filament\Resources\UserProductInteractions\Pages;

use App\Filament\Resources\UserProductInteractions\UserProductInteractionResource;
use Filament\Actions\CreateAction;
use App\Filament\Pages\Support\BaseListRecords;

class ListUserProductInteractions extends BaseListRecords
{
    protected static string $resource = UserProductInteractionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
