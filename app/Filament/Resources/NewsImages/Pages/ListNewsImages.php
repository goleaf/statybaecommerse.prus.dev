<?php

namespace App\Filament\Resources\NewsImages\Pages;

use App\Filament\Resources\NewsImages\NewsImageResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListNewsImages extends ListRecords
{
    protected static string $resource = NewsImageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
