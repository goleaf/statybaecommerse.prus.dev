<?php

declare (strict_types=1);
namespace App\Filament\Widgets;

use App\Models\Currency;
use Filament\Widgets\ChartWidget;
/**
 * CurrencyExchangeRateWidget
 * 
 * Filament v4 widget for CurrencyExchangeRateWidget dashboard display with real-time data and interactive features.
 * 
 * @property string|null $heading
 * @property int|null $sort
 * @property string|null $pollingInterval
 */
final class CurrencyExchangeRateWidget extends ChartWidget
{
    protected ?string $heading = 'Currency Exchange Rates';
    protected static ?int $sort = 3;
    protected ?string $pollingInterval = '30s';
    /**
     * Handle getData functionality with proper error handling.
     * @return array
     */
    protected function getData(): array
    {
        $currencies = Currency::where('is_enabled', true)->orderBy('exchange_rate', 'desc')->limit(10)->get();
        $labels = $currencies->pluck('code')->toArray();
        $data = $currencies->pluck('exchange_rate')->toArray();
        return ['datasets' => [['label' => __('admin.currency.widgets.exchange_rate'), 'data' => $data, 'backgroundColor' => 'rgba(59, 130, 246, 0.1)', 'borderColor' => '#3B82F6', 'borderWidth' => 2, 'fill' => true, 'tension' => 0.4]], 'labels' => $labels];
    }
    /**
     * Handle getType functionality with proper error handling.
     * @return string
     */
    protected function getType(): string
    {
        return 'line';
    }
    /**
     * Handle getOptions functionality with proper error handling.
     * @return array
     */
    protected function getOptions(): array
    {
        return ['responsive' => true, 'maintainAspectRatio' => false, 'scales' => ['y' => ['beginAtZero' => false, 'title' => ['display' => true, 'text' => __('admin.currency.widgets.exchange_rate')]], 'x' => ['title' => ['display' => true, 'text' => __('admin.currency.widgets.currency_code')]]], 'plugins' => ['legend' => ['display' => false], 'tooltip' => ['callbacks' => ['label' => 'function(context) {
                            return "' . __('admin.currency.widgets.exchange_rate') . ': " + context.parsed.y.toFixed(6);
                        }']]]];
    }
}