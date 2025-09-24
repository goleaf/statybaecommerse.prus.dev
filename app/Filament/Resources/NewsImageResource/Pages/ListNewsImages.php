<?php

declare(strict_types=1);

namespace App\Filament\Resources\NewsImageResource\Pages;

use App\Filament\Resources\NewsImageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListNewsImages extends ListRecords
{
    protected static string $resource = NewsImageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
