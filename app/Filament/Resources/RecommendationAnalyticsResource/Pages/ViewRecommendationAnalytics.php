<?php

declare(strict_types=1);

namespace App\Filament\Resources\RecommendationAnalyticsResource\Pages;

use App\Filament\Resources\RecommendationAnalyticsResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

final class ViewRecommendationAnalytics extends ViewRecord
{
    protected static string $resource = RecommendationAnalyticsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
