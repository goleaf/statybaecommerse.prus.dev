<?php

declare(strict_types=1);

namespace App\Filament\Resources\AnalyticsResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\AnalyticsResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\ListRecords\Concerns\Translatable as SpatieTranslatableListRecords;

final class AnalyticsDashboard extends BaseListRecords
{
    use SpatieTranslatableListRecords;

    protected static string $resource = AnalyticsResource::class;

    /**
     * @return array<int, Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(),
            Action::make('export_report')
                ->label(__('analytics.export_report'))
                ->icon('heroicon-m-arrow-down-tray')
                ->action(function (): void {
                    // Stub export logic. Replace with actual export service call.

                    Notification::make()
                        ->success()
                        ->title(__('analytics.export_report'))
                        ->send();
                }),
            Action::make('refresh_data')
                ->label(__('analytics.refresh_data'))
                ->icon('heroicon-m-arrow-path')
                ->action(function (): void {
                    // Stub refresh logic. Replace with actual refresh service call.

                    Notification::make()
                        ->success()
                        ->title(__('analytics.refresh_data'))
                        ->send();
                }),
        ];
    }
}
