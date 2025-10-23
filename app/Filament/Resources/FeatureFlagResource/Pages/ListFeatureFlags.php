<?php

declare(strict_types=1);

namespace App\Filament\Resources\FeatureFlagResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\FeatureFlagResource;
use Filament\Actions\CreateAction;
use App\Filament\Pages\Support\BaseListRecords;

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
