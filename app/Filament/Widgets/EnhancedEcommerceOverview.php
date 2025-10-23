<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

final class EnhancedEcommerceOverview extends StatsOverviewWidget
{
    /**
     * @var string|\BackedEnum|null Navigation icon override for Filament menus.
     */
    protected static $navigationIcon = 'heroicon-o-presentation-chart-line';

    /**
     * @var string|null Friendly label for the widget entry.
     */
    protected static $navigationLabel = 'Enh. E-commerce Overview';

    /**
     * @var string|null Dashboard group keeps analytics widgets together.
     */
    protected static $navigationGroup = 'Dashboard';

    protected string $maxHeight = '32rem';

    protected ?string $pollingInterval = '15s';

    protected function getStats(): array
    {
        $translations = __('analytics.enhanced_overview');

        return [
            Stat::make($translations['total_revenue'], $this->getTotalRevenue())->description(
                $translations['change_since_last_month'] . ': ' . $this->formatDelta($this->getRevenueDelta())
            ),
            Stat::make($translations['total_orders'], $this->getTotalOrders())->description(
                $translations['change_since_last_month'] . ': ' . $this->formatDelta($this->getOrderDelta())
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

        return number_format($average, 1) . '/5';
    }

    private function getRevenueDelta(): float
    {
        $now = Carbon::now();
        $previousMonth = $now->copy()->subMonth();

        $current = Order::query()
            ->where('status', '!=', 'cancelled')
            ->createdBetween($now->copy()->startOfMonth(), $now)
            ->sum('total');

        $previous = Order::query()
            ->where('status', '!=', 'cancelled')
            ->createdBetween($previousMonth->copy()->startOfMonth(), $previousMonth->copy()->endOfMonth())
            ->sum('total');

        return $this->calculateDelta($current, $previous);
    }

    private function getOrderDelta(): float
    {
        $now = Carbon::now();
        $previousMonth = $now->copy()->subMonth();

        $current = Order::query()->createdBetween($now->copy()->startOfMonth(), $now)->count();

        $previous = Order::query()
            ->createdBetween($previousMonth->copy()->startOfMonth(), $previousMonth->copy()->endOfMonth())
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
