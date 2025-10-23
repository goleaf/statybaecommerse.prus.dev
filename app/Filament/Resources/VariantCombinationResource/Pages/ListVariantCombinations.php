<?php

declare(strict_types=1);

namespace App\Filament\Resources\VariantCombinationResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\VariantCombinationResource;
use Filament\Actions;

final class ListVariantCombinations extends BaseListRecords
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
