<?php

declare(strict_types=1);

namespace App\Filament\Resources\VariantAttributeValueResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\VariantAttributeValueResource;
use Filament\Actions;
use App\Filament\Pages\Support\BaseListRecords;

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
