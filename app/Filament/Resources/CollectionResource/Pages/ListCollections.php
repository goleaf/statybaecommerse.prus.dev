<?php declare(strict_types=1);

namespace App\Filament\Resources\CollectionResource\Pages;

use App\Filament\Resources\CollectionResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

final class ListCollections extends ListRecords
{
    protected static string $resource = CollectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
