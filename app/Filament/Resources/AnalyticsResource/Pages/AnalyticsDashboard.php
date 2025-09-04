<?php declare(strict_types=1);

namespace App\Filament\Resources\AnalyticsResource\Pages;

use App\Filament\Resources\AnalyticsResource;
use App\Filament\Widgets\AdvancedStatsWidget;
use App\Filament\Widgets\OrdersChartWidget;
use App\Filament\Widgets\TopSellingProductsWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

final class AnalyticsDashboard extends ListRecords
{
    protected static string $resource = AnalyticsResource::class;

    public function getTitle(): string
    {
        return __('Analytics Dashboard');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('export_report')
                ->label(__('Export Report'))
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->action(function () {
                    // Export analytics report logic
                    $this->notify('success', __('Report exported successfully'));
                }),
            
            Actions\Action::make('refresh_data')
                ->label(__('Refresh Data'))
                ->icon('heroicon-o-arrow-path')
                ->action(function () {
                    // Clear analytics cache
                    cache()->tags(['analytics'])->flush();
                    $this->notify('success', __('Data refreshed successfully'));
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
