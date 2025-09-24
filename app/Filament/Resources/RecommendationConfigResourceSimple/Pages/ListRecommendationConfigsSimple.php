<?php

declare(strict_types=1);

namespace App\Filament\Resources\RecommendationConfigResourceSimple\Pages;

use App\Filament\Resources\RecommendationConfigResourceSimple;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

final class ListRecommendationConfigsSimple extends ListRecords
{
    protected static string $resource = RecommendationConfigResourceSimple::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
