<?php

namespace App\Filament\Resources\UserProductInteractions\Pages;

use App\Filament\Resources\UserProductInteractions\UserProductInteractionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUserProductInteraction extends EditRecord
{
    protected static string $resource = UserProductInteractionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
