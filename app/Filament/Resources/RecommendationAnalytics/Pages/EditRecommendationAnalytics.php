<?php

namespace App\Filament\Resources\RecommendationAnalytics\Pages;

use App\Filament\Resources\RecommendationAnalytics\RecommendationAnalyticsResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRecommendationAnalytics extends EditRecord
{
    protected static string $resource = RecommendationAnalyticsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
