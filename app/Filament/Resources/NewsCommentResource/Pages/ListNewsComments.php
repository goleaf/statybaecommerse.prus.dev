<?php

declare(strict_types=1);

namespace App\Filament\Resources\NewsCommentResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\NewsCommentResource;
use Filament\Actions;

class ListNewsComments extends BaseListRecords
{
    
    protected static string $resource = NewsCommentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
