<?php

declare(strict_types=1);

namespace App\Filament\Resources\NewsTagResource\Pages;

use App\Filament\Resources\NewsTagResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

final class ListNewsTags extends ListRecords
{
    protected static string $resource = NewsTagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
