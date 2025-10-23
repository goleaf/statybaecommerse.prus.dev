<?php

declare(strict_types=1);

namespace App\Filament\Resources\CollectionResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\CollectionResource;
use Filament\Actions;
use App\Filament\Pages\Support\BaseListRecords;

final class ListCollections extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = CollectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
