<?php

declare(strict_types=1);

namespace App\Filament\Resources\NewsTagResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\NewsTagResource;
use Filament\Actions;

final class ListNewsTags extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = NewsTagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
