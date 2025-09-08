<?php declare(strict_types=1);

namespace App\Filament\Resources\AnalyticsResource\Pages;

use App\Filament\Resources\AnalyticsResource;
use App\Filament\Widgets\AdvancedStatsWidget;
use App\Filament\Widgets\OrdersChartWidget;
use App\Filament\Widgets\TopSellingProductsWidget;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

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
                    $this->notify('success', __('analytics.report_exported_successfully'));
                }),
            Actions\Action::make('refresh_data')
                ->label(__('analytics.refresh_data'))
                ->icon('heroicon-o-arrow-path')
                ->action(function () {
                    // Clear analytics cache
                    cache()->tags(['analytics'])->flush();
                    $this->notify('success', __('analytics.data_refreshed_successfully'));
                }),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            AdvancedStatsWidget::class,
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
