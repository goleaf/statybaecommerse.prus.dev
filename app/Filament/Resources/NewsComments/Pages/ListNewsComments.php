<?php

namespace App\Filament\Resources\NewsComments\Pages;

use App\Filament\Resources\NewsComments\NewsCommentResource;
use Filament\Actions\CreateAction;
use App\Filament\Pages\Support\BaseListRecords;

class ListNewsComments extends BaseListRecords
{
    protected static string $resource = NewsCommentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
