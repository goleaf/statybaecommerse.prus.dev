<?php

namespace App\Filament\Resources\RecommendationCaches\Pages;

use App\Filament\Resources\RecommendationCaches\RecommendationCacheResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRecommendationCache extends EditRecord
{
    protected static string $resource = RecommendationCacheResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
