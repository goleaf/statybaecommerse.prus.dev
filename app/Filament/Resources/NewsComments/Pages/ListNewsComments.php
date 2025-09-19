<?php

namespace App\Filament\Resources\NewsComments\Pages;

use App\Filament\Resources\NewsComments\NewsCommentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListNewsComments extends ListRecords
{
    protected static string $resource = NewsCommentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
