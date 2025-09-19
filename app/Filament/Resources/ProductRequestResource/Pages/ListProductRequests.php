<?php declare(strict_types=1);

namespace App\Filament\Resources\ProductRequestResource\Pages;

use App\Filament\Resources\ProductRequestResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

final class ListProductRequests extends ListRecords
{
    protected static string $resource = ProductRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

