<?php

declare(strict_types=1);

namespace App\Filament\Resources\RecommendationCaches\Pages;

use App\Filament\Resources\RecommendationCaches\RecommendationCacheResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

final class ListRecommendationCaches extends ListRecords
{
    protected static string $resource = RecommendationCacheResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
