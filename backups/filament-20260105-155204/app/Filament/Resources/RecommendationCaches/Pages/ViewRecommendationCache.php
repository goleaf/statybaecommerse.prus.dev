<?php

declare(strict_types=1);

namespace App\Filament\Resources\RecommendationCaches\Pages;

use App\Filament\Resources\RecommendationCaches\RecommendationCacheResource;
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
