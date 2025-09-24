<?php

declare(strict_types=1);

namespace App\Filament\Resources\VariantAttributeValueResource\Pages;

use App\Filament\Resources\VariantAttributeValueResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

final class ListVariantAttributeValues extends ListRecords
{
    protected static string $resource = VariantAttributeValueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
