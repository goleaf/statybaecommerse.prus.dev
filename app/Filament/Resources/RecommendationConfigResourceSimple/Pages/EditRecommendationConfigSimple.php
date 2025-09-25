<?php

declare(strict_types=1);

namespace App\Filament\Resources\RecommendationConfigResourceSimple\Pages;

use App\Filament\Resources\RecommendationConfigResourceSimple;
use App\Models\RecommendationConfigSimple;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

final class EditRecommendationConfigSimple extends EditRecord
{
    protected static string $resource = RecommendationConfigResourceSimple::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Action::make('toggle_active')
                ->label(fn (RecommendationConfigSimple $record): string => $record->is_active ? __('recommendation_configs_simple.deactivate') : __('recommendation_configs_simple.activate'))
                ->icon(fn (RecommendationConfigSimple $record): string => $record->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                ->color(fn (RecommendationConfigSimple $record): string => $record->is_active ? 'warning' : 'success')
                ->action(function (RecommendationConfigSimple $record): void {
                    $record->update(['is_active' => ! $record->is_active]);
                })
                ->requiresConfirmation(),
            Action::make('set_default')
                ->label(__('recommendation_configs_simple.set_default'))
                ->icon('heroicon-o-star')
                ->color('warning')
                ->visible(fn (RecommendationConfigSimple $record): bool => ! $record->is_default)
                ->action(function (RecommendationConfigSimple $record): void {
                    RecommendationConfigSimple::where('is_default', true)->update(['is_default' => false]);
                    $record->update(['is_default' => true]);
                })
                ->requiresConfirmation(),
        ];
    }
}
