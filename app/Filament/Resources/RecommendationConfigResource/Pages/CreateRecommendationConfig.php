<?php

declare(strict_types=1);

namespace App\Filament\Resources\RecommendationConfigResource\Pages;

use App\Filament\Resources\RecommendationConfigResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

final class CreateRecommendationConfig extends CreateRecord
{
    protected static string $resource = RecommendationConfigResource::class;

}
