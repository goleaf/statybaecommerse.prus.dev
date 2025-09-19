<?php

namespace App\Filament\Resources\NewsImages\Pages;

use App\Filament\Resources\NewsImages\NewsImageResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditNewsImage extends EditRecord
{
    protected static string $resource = NewsImageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
