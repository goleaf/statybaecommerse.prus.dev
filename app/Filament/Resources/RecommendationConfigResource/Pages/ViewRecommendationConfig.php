<?php

declare(strict_types=1);

namespace App\Filament\Resources\RecommendationConfigResource\Pages;

use App\Filament\Resources\RecommendationConfigResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

final class ViewRecommendationConfig extends ViewRecord
{
    protected static string $resource = RecommendationConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
