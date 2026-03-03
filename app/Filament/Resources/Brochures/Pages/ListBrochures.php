<?php

declare(strict_types=1);

namespace App\Filament\Resources\Brochures\Pages;

use App\Filament\Resources\Brochures\BrochureResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

final class ListBrochures extends ListRecords
{
    protected static string $resource = BrochureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
