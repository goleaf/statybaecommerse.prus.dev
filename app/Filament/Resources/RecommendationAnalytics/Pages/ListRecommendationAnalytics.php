<?php

namespace App\Filament\Resources\RecommendationAnalytics\Pages;

use App\Filament\Resources\RecommendationAnalytics\RecommendationAnalyticsResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRecommendationAnalytics extends ListRecords
{
    protected static string $resource = RecommendationAnalyticsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
