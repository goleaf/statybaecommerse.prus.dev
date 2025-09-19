<?php

namespace App\Filament\Resources\NewsTags\Pages;

use App\Filament\Resources\NewsTags\NewsTagResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditNewsTag extends EditRecord
{
    protected static string $resource = NewsTagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
