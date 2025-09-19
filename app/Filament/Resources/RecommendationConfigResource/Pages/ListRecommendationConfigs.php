<?php

declare(strict_types=1);

namespace App\Filament\Resources\RecommendationConfigResource\Pages;

use App\Filament\Resources\RecommendationConfigResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

final class ListRecommendationConfigs extends ListRecords
{
    protected static string $resource = RecommendationConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
