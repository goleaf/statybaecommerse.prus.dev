<?php declare(strict_types=1);

namespace App\Filament\Resources\RecommendationConfigResourceSimple\Pages;

use App\Filament\Resources\RecommendationConfigResourceSimple;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

final class ListRecommendationConfigSimples extends ListRecords
{
    protected static string $resource = RecommendationConfigResourceSimple::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
