<?php

declare(strict_types=1);

namespace App\Filament\Resources\RecommendationAnalytics\Pages;

use App\Filament\Resources\RecommendationAnalytics\RecommendationAnalyticsResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

final class EditRecommendationAnalytics extends EditRecord
{
    protected static string $resource = RecommendationAnalyticsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
