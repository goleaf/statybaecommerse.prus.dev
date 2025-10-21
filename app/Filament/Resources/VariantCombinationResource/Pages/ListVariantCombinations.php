<?php

declare(strict_types=1);

namespace App\Filament\Resources\VariantCombinationResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\VariantCombinationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

final class ListVariantCombinations extends ListRecords
{
    use HasResizableColumns;

    protected static string $resource = VariantCombinationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
