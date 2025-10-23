<?php

declare(strict_types=1);

namespace App\Filament\Resources\FeatureFlags\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\FeatureFlags\FeatureFlagResource;
use Filament\Actions\CreateAction;

class ListFeatureFlags extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = FeatureFlagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
