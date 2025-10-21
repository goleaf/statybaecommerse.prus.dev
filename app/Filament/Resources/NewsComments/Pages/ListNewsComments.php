<?php

declare(strict_types=1);

namespace App\Filament\Resources\NewsComments\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\NewsComments\NewsCommentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListNewsComments extends ListRecords
{
    use HasResizableColumns;

    protected static string $resource = NewsCommentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
