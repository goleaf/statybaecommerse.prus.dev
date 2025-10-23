<?php

declare(strict_types=1);

namespace App\Filament\Resources\NewsTags\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\NewsTags\NewsTagResource;
use Filament\Actions\CreateAction;

class ListNewsTags extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = NewsTagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
