<?php

declare(strict_types=1);

namespace App\Filament\Resources\RecommendationAnalytics\Pages;

use App\Filament\Resources\RecommendationAnalytics\RecommendationAnalyticsResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateRecommendationAnalytics extends CreateRecord
{
    protected static string $resource = RecommendationAnalyticsResource::class;
}
