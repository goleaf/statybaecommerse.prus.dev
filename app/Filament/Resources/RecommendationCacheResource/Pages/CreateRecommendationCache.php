<?php

declare(strict_types=1);

namespace App\Filament\Resources\RecommendationCacheResource\Pages;

use App\Filament\Resources\RecommendationCacheResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateRecommendationCache extends CreateRecord
{
    protected static string $resource = RecommendationCacheResource::class;
}
