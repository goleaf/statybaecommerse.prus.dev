<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductComparisonResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\ProductComparisonResource;
use Filament\Actions;
use App\Filament\Pages\Support\BaseListRecords;

final class ListProductComparisons extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = ProductComparisonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
