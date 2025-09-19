<?php

namespace App\Filament\Resources\UserProductInteractions\Pages;

use App\Filament\Resources\UserProductInteractions\UserProductInteractionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUserProductInteractions extends ListRecords
{
    protected static string $resource = UserProductInteractionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
