<?php declare(strict_types=1);

namespace App\Filament\Resources\VariantAttributeValueResource\Pages;

use App\Filament\Resources\VariantAttributeValueResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

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

