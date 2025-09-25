<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use UnitEnum;

final class EnhancedEcommerceOverview extends StatsOverviewWidget
{
    /**
     * @var string|null
     */
    /** @var string|\BackedEnum|null */
    protected static $navigationIcon = 'heroicon-o-presentation-chart-line';

    /**
     * @var string|null
     */
    protected static $navigationLabel = 'Enh. E-commerce Overview';

    /**
     * @var string|null
     */
    /** @var UnitEnum|string|null */
    protected static $navigationGroup = 'Dashboard';

    protected string $maxHeight = '32rem';

    protected string $pollingInterval = '15s';

    protected function getStats(): array
    {
        $translations = __('analytics.enhanced_overview');

        return [
            Stat::make($translations['total_revenue'], $this->getTotalRevenue())->description(
                $translations['change_since_last_month'].': '.$this->formatDelta($this->getRevenueDelta())
            ),
            Stat::make($translations['total_orders'], $this->getTotalOrders())->description(
                $translations['change_since_last_month'].': '.$this->formatDelta($this->getOrderDelta())
            ),
            Stat::make($translations['total_customers'], $this->getTotalCustomers()),
            Stat::make($translations['average_order_value'], $this->getAverageOrderValue()),
            Stat::make($translations['total_products'], $this->getTotalProducts()),
            Stat::make($translations['average_rating'], $this->getAverageRating()),
        ];
    }

    public function getTotalRevenue(): string
    {
        $total = Order::query()->where('status', '!=', 'cancelled')->sum('total');

        return app_money_format($total ?? 0, currency: 'EUR');
    }

    public function getTotalOrders(): string
    {
        return (string) Order::count();
    }

    public function getTotalCustomers(): string
    {
        return (string) User::count();
    }

    public function getAverageOrderValue(): string
    {
        $totalOrders = Order::count();

        if ($totalOrders === 0) {
            return app_money_format(0, currency: 'EUR');
        }

        $total = Order::query()->where('status', '!=', 'cancelled')->sum('total');

        return app_money_format($total / $totalOrders, currency: 'EUR');
    }

    public function getTotalProducts(): string
    {
        return (string) Product::query()->where('is_visible', true)->count();
    }

    public function getAverageRating(): string
    {
        $average = (float) Review::query()->avg('rating');

        return number_format($average, 1).'/5';
    }

    private function getRevenueDelta(): float
    {
        $current = Order::query()
            ->where('status', '!=', 'cancelled')
            ->whereBetween('created_at', [now()->startOfMonth(), now()])
            ->sum('total');

        $previous = Order::query()
            ->where('status', '!=', 'cancelled')
            ->whereBetween('created_at', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()])
            ->sum('total');

        return $this->calculateDelta($current, $previous);
    }

    private function getOrderDelta(): float
    {
        $current = Order::query()->whereBetween('created_at', [now()->startOfMonth(), now()])->count();

        $previous = Order::query()
            ->whereBetween('created_at', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()])
            ->count();

        return $this->calculateDelta($current, $previous);
    }

    private function calculateDelta(float $current, float $previous): float
    {
        if ($previous === 0.0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return (($current - $previous) / $previous) * 100;
    }

    private function formatDelta(float $delta): string
    {
        return sprintf('%+.1f%%', $delta);
    }
}
