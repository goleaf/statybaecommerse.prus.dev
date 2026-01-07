<?php

declare(strict_types=1);

namespace App\Filament\Resources\RecommendationAnalyticsResource\Pages;

use App\Filament\Resources\RecommendationAnalyticsResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

final class EditRecommendationAnalytics extends EditRecord
{
    protected static string $resource = RecommendationAnalyticsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
