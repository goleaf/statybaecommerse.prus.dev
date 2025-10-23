<?php

declare(strict_types=1);

namespace App\Filament\Resources\RecommendationCaches\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\RecommendationCaches\RecommendationCacheResource;
use Filament\Actions\CreateAction;

final class ListRecommendationCaches extends ListRecords
{
    use HasResizableColumns;

    protected static string $resource = RecommendationCacheResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
