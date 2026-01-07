<?php

declare(strict_types=1);

namespace App\Filament\Resources\RecommendationBlockResource\Pages;

use App\Filament\Resources\RecommendationBlockResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

final class EditRecommendationBlock extends EditRecord
{
    protected static string $resource = RecommendationBlockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Actions\Action::make('toggle_active')
                ->label(__('recommendation_blocks.actions.toggle_active'))
                ->icon('heroicon-o-power')
                ->color('warning')
                ->action(function (): void {
                    $this->record->is_active = ! $this->record->is_active;
                    $this->record->save();
                }),
            Actions\Action::make('set_default')
                ->label(__('recommendation_blocks.actions.set_default'))
                ->icon('heroicon-o-star')
                ->color('success')
                ->visible(fn () => ! $this->record->is_default)
                ->action(function (): void {
                    $this->record->is_default = true;
                    $this->record->save();
                }),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
