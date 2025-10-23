<?php

declare(strict_types=1);

namespace App\Filament\Resources\RecommendationConfigResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\RecommendationConfigResource;
use Filament\Actions;

final class ListRecommendationConfigs extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = RecommendationConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
