<?php

declare(strict_types=1);

namespace App\Filament\Resources\CollectionResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\CollectionResource;
use Filament\Actions;

final class ListCollections extends BaseListRecords
{
    
    protected static string $resource = CollectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
