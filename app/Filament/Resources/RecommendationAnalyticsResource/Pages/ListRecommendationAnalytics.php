<?php

declare(strict_types=1);

namespace App\Filament\Resources\RecommendationAnalyticsResource\Pages;

use App\Filament\Resources\RecommendationAnalyticsResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

final class ListRecommendationAnalytics extends ListRecords
{
    protected static string $resource = RecommendationAnalyticsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
