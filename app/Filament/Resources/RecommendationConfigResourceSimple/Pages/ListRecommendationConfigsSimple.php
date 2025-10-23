<?php

declare(strict_types=1);

namespace App\Filament\Resources\RecommendationConfigResourceSimple\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\RecommendationConfigResourceSimple;
use Filament\Actions;
use App\Filament\Pages\Support\BaseListRecords;

final class ListRecommendationConfigsSimple extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = RecommendationConfigResourceSimple::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
