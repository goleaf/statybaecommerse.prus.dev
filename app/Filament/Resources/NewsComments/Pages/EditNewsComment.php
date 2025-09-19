<?php

namespace App\Filament\Resources\NewsComments\Pages;

use App\Filament\Resources\NewsComments\NewsCommentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditNewsComment extends EditRecord
{
    protected static string $resource = NewsCommentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
