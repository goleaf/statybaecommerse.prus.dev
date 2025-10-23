<?php

declare(strict_types=1);

namespace App\Filament\Resources\RecommendationAnalytics\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\RecommendationAnalytics\RecommendationAnalyticsResource;
use Filament\Actions\CreateAction;

final class ListRecommendationAnalytics extends ListRecords
{
    use HasResizableColumns;

    protected static string $resource = RecommendationAnalyticsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
