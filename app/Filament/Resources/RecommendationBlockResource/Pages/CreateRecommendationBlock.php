<?php

declare(strict_types=1);

namespace App\Filament\Resources\RecommendationBlockResource\Pages;

use App\Filament\Resources\RecommendationBlockResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateRecommendationBlock extends CreateRecord
{
    protected static string $resource = RecommendationBlockResource::class;
}
