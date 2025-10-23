<?php

declare(strict_types=1);

namespace App\Filament\Resources\RecommendationAnalytics\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\RecommendationAnalytics\RecommendationAnalyticsResource;
use Filament\Actions\CreateAction;

final class ListRecommendationAnalytics extends BaseListRecords
{
    
    protected static string $resource = RecommendationAnalyticsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
