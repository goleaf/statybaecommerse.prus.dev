<?php

declare(strict_types=1);

namespace App\Filament\Resources\RecommendationBlockResource\Pages;

use App\Filament\Resources\RecommendationBlockResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

final class ListRecommendationBlocks extends ListRecords
{
    protected static string $resource = RecommendationBlockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
