<?php declare(strict_types=1);

namespace App\Filament\Resources\AnalyticsResource\Pages;

use App\Filament\Resources\AnalyticsResource;
use App\Filament\Widgets\StatsWidget;
use App\Filament\Widgets\OrdersChartWidget;
use App\Filament\Widgets\TopSellingProductsWidget;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;
use Filament\Notifications\Notification;

final class AnalyticsDashboard extends ListRecords
{
    protected static string $resource = AnalyticsResource::class;

    public function getTitle(): string
    {
        return __('analytics.analytics_dashboard');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('export_report')
                ->label(__('analytics.export_report'))
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->action(function () {
                    // Export analytics report logic
                    Notification::make()
                        ->title(__('analytics.report_exported_successfully'))
                        ->success()
                        ->send();
                }),
            Actions\Action::make('refresh_data')
                ->label(__('analytics.refresh_data'))
                ->icon('heroicon-o-arrow-path')
                ->action(function () {
                    // Clear analytics cache
                    cache()->tags(['analytics'])->flush();
                    Notification::make()
                        ->title(__('analytics.data_refreshed_successfully'))
                        ->success()
                        ->send();
                }),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            StatsWidget::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            OrdersChartWidget::class,
            TopSellingProductsWidget::class,
        ];
    }

    public function getWidgetData(): array
    {
        return [
            'dateRange' => [
                'start' => now()->subDays(30),
                'end' => now(),
            ],
        ];
    }
}
