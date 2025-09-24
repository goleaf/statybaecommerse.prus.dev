<?php

declare(strict_types=1);

namespace App\Filament\Resources\RecommendationBlockResource\Pages;

use App\Filament\Resources\RecommendationBlockResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

final class ViewRecommendationBlock extends ViewRecord
{
    protected static string $resource = RecommendationBlockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
