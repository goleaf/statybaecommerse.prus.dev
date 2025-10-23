<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductComparisonResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\ProductComparisonResource;
use Filament\Actions;

final class ListProductComparisons extends BaseListRecords
{
    
    protected static string $resource = ProductComparisonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
