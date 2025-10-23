<?php

declare(strict_types=1);

namespace App\Filament\Resources\VariantAttributeValueResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\VariantAttributeValueResource;
use Filament\Actions;

final class ListVariantAttributeValues extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = VariantAttributeValueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
