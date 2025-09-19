<?php declare(strict_types=1);

namespace App\Filament\Resources\RecommendationConfigResourceSimple\Pages;

use App\Filament\Resources\RecommendationConfigResourceSimple;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions;

final class EditRecommendationConfigSimple extends EditRecord
{
    protected static string $resource = RecommendationConfigResourceSimple::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
