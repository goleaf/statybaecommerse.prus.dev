<?php

declare(strict_types=1);

namespace App\Filament\Resources\RecommendationCaches\Pages;

use App\Filament\Resources\RecommendationCaches\RecommendationCacheResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateRecommendationCache extends CreateRecord
{
    protected static string $resource = RecommendationCacheResource::class;
}
