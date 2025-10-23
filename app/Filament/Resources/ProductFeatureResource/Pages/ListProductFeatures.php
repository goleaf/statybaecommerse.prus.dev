<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductFeatureResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\ProductFeatureResource;
use Filament\Actions;
use App\Filament\Pages\Support\BaseListRecords;

final class ListProductFeatures extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = ProductFeatureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
