<?php

declare(strict_types=1);

namespace App\Filament\Resources\RecommendationCacheResource\Pages;

use App\Filament\Resources\RecommendationCacheResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

final class ViewRecommendationCache extends ViewRecord
{
    protected static string $resource = RecommendationCacheResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
