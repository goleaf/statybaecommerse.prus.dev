<?php

declare(strict_types=1);

namespace App\Filament\Resources\RecommendationConfigResource\Pages;

use App\Filament\Resources\RecommendationConfigResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\Action;
use App\Models\RecommendationConfig;

final class EditRecommendationConfig extends EditRecord
{
    protected static string $resource = RecommendationConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Action::make('toggle_active')
                ->label(__('recommendation_config.actions.toggle_active'))
                ->action(function (RecommendationConfig $record): void {
                    $record->update(['is_active' => ! (bool) $record->is_active]);
                }),
            Action::make('set_default')
                ->label(__('recommendation_config.actions.set_default'))
                ->visible(fn (RecommendationConfig $record): bool => ! (bool) $record->is_default)
                ->action(function (RecommendationConfig $record): void {
                    RecommendationConfig::query()->update(['is_default' => false]);
                    $record->update(['is_default' => true]);
                }),
            Actions\DeleteAction::make(),
        ];
    }
}
