<?php declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

final class EnhancedEcommerceOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make(__('admin.widgets.total_revenue'), $this->getTotalRevenue())
                ->description($this->getRevenueChange())
                ->descriptionIcon($this->getRevenueIcon())
                ->chart($this->getRevenueChart())
                ->color($this->getRevenueColor()),
            Stat::make(__('admin.widgets.total_orders'), $this->getTotalOrders())
                ->description($this->getOrdersChange())
                ->descriptionIcon($this->getOrdersIcon())
                ->chart($this->getOrdersChart())
                ->color($this->getOrdersColor()),
            Stat::make(__('admin.widgets.total_customers'), $this->getTotalCustomers())
                ->description($this->getCustomersChange())
                ->descriptionIcon($this->getCustomersIcon())
                ->chart($this->getCustomersChart())
                ->color($this->getCustomersColor()),
            Stat::make(__('admin.widgets.average_order_value'), $this->getAverageOrderValue())
                ->description($this->getAovChange())
                ->descriptionIcon($this->getAovIcon())
                ->chart($this->getAovChart())
                ->color($this->getAovColor()),
            Stat::make(__('admin.widgets.total_products'), $this->getTotalProducts())
                ->description($this->getProductsChange())
                ->descriptionIcon($this->getProductsIcon())
                ->color('info'),
            Stat::make(__('admin.widgets.average_rating'), $this->getAverageRating())
                ->description($this->getRatingChange())
                ->descriptionIcon($this->getRatingIcon())
                ->color($this->getRatingColor()),
        ];
    }

    public function getTotalRevenue(): string
    {
        $revenue = Order::where('status', 'completed')
            ->sum('total');

        return '€' . number_format($revenue, 2);
    }

    private function getRevenueChange(): string
    {
        $currentMonth = Order::where('status', 'completed')
            ->whereMonth('created_at', now()->month)
            ->sum('total');

        $previousMonth = Order::where('status', 'completed')
            ->whereMonth('created_at', now()->subMonth()->month)
            ->sum('total');

        if ($previousMonth == 0) {
            return __('admin.widgets.no_previous_data');
        }

        $change = (($currentMonth - $previousMonth) / $previousMonth) * 100;

        return ($change >= 0 ? '+' : '') . number_format($change, 1) . '% ' . __('admin.widgets.from_last_month');
    }

    private function getRevenueIcon(): string
    {
        $currentMonth = Order::where('status', 'completed')
            ->whereMonth('created_at', now()->month)
            ->sum('total');

        $previousMonth = Order::where('status', 'completed')
            ->whereMonth('created_at', now()->subMonth()->month)
            ->sum('total');

        return $currentMonth >= $previousMonth ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down';
    }

    private function getRevenueColor(): string
    {
        $currentMonth = Order::where('status', 'completed')
            ->whereMonth('created_at', now()->month)
            ->sum('total');

        $previousMonth = Order::where('status', 'completed')
            ->whereMonth('created_at', now()->subMonth()->month)
            ->sum('total');

        return $currentMonth >= $previousMonth ? 'success' : 'danger';
    }

    private function getRevenueChart(): array
    {
        return Order::where('status', 'completed')
            ->selectRaw('DATE(created_at) as date, SUM(total) as revenue')
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('revenue')
            ->toArray();
    }

    private function getTotalOrders(): string
    {
        return number_format(Order::count());
    }

    private function getOrdersChange(): string
    {
        $currentMonth = Order::whereMonth('created_at', now()->month)->count();
        $previousMonth = Order::whereMonth('created_at', now()->subMonth()->month)->count();

        if ($previousMonth == 0) {
            return __('admin.widgets.no_previous_data');
        }

        $change = (($currentMonth - $previousMonth) / $previousMonth) * 100;

        return ($change >= 0 ? '+' : '') . number_format($change, 1) . '% ' . __('admin.widgets.from_last_month');
    }

    private function getOrdersIcon(): string
    {
        $currentMonth = Order::whereMonth('created_at', now()->month)->count();
        $previousMonth = Order::whereMonth('created_at', now()->subMonth()->month)->count();

        return $currentMonth >= $previousMonth ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down';
    }

    private function getOrdersColor(): string
    {
        $currentMonth = Order::whereMonth('created_at', now()->month)->count();
        $previousMonth = Order::whereMonth('created_at', now()->subMonth()->month)->count();

        return $currentMonth >= $previousMonth ? 'success' : 'danger';
    }

    private function getOrdersChart(): array
    {
        return Order::selectRaw('DATE(created_at) as date, COUNT(*) as orders')
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('orders')
            ->toArray();
    }

    public function getTotalCustomers(): string
    {
        return number_format(User::count());
    }

    private function getCustomersChange(): string
    {
        $currentMonth = User::where('is_admin', false)
            ->whereMonth('created_at', now()->month)
            ->count();

        $previousMonth = User::where('is_admin', false)
            ->whereMonth('created_at', now()->subMonth()->month)
            ->count();

        if ($previousMonth == 0) {
            return __('admin.widgets.no_previous_data');
        }

        $change = (($currentMonth - $previousMonth) / $previousMonth) * 100;

        return ($change >= 0 ? '+' : '') . number_format($change, 1) . '% ' . __('admin.widgets.from_last_month');
    }

    private function getCustomersIcon(): string
    {
        $currentMonth = User::where('is_admin', false)
            ->whereMonth('created_at', now()->month)
            ->count();

        $previousMonth = User::where('is_admin', false)
            ->whereMonth('created_at', now()->subMonth()->month)
            ->count();

        return $currentMonth >= $previousMonth ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down';
    }

    private function getCustomersColor(): string
    {
        $currentMonth = User::where('is_admin', false)
            ->whereMonth('created_at', now()->month)
            ->count();

        $previousMonth = User::where('is_admin', false)
            ->whereMonth('created_at', now()->subMonth()->month)
            ->count();

        return $currentMonth >= $previousMonth ? 'success' : 'danger';
    }

    private function getCustomersChart(): array
    {
        return User::where('is_admin', false)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as customers')
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('customers')
            ->toArray();
    }

    private function getAverageOrderValue(): string
    {
        $avgValue = Order::where('status', 'completed')
            ->avg('total');

        return '€' . number_format($avgValue ?? 0, 2);
    }

    private function getAovChange(): string
    {
        $currentMonth = Order::where('status', 'completed')
            ->whereMonth('created_at', now()->month)
            ->avg('total');

        $previousMonth = Order::where('status', 'completed')
            ->whereMonth('created_at', now()->subMonth()->month)
            ->avg('total');

        if (!$previousMonth) {
            return __('admin.widgets.no_previous_data');
        }

        $change = (($currentMonth - $previousMonth) / $previousMonth) * 100;

        return ($change >= 0 ? '+' : '') . number_format($change, 1) . '% ' . __('admin.widgets.from_last_month');
    }

    private function getAovIcon(): string
    {
        $currentMonth = Order::where('status', 'completed')
            ->whereMonth('created_at', now()->month)
            ->avg('total');

        $previousMonth = Order::where('status', 'completed')
            ->whereMonth('created_at', now()->subMonth()->month)
            ->avg('total');

        return ($currentMonth ?? 0) >= ($previousMonth ?? 0) ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down';
    }

    private function getAovColor(): string
    {
        $currentMonth = Order::where('status', 'completed')
            ->whereMonth('created_at', now()->month)
            ->avg('total');

        $previousMonth = Order::where('status', 'completed')
            ->whereMonth('created_at', now()->subMonth()->month)
            ->avg('total');

        return ($currentMonth ?? 0) >= ($previousMonth ?? 0) ? 'success' : 'danger';
    }

    private function getAovChart(): array
    {
        return Order::where('status', 'completed')
            ->selectRaw('DATE(created_at) as date, AVG(total) as aov')
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('aov')
            ->toArray();
    }

    private function getTotalProducts(): string
    {
        return number_format(Product::where('is_visible', true)->count());
    }

    private function getProductsChange(): string
    {
        $currentMonth = Product::whereMonth('created_at', now()->month)->count();
        $previousMonth = Product::whereMonth('created_at', now()->subMonth()->month)->count();

        if ($previousMonth == 0) {
            return __('admin.widgets.no_previous_data');
        }

        $change = (($currentMonth - $previousMonth) / $previousMonth) * 100;

        return ($change >= 0 ? '+' : '') . number_format($change, 1) . '% ' . __('admin.widgets.from_last_month');
    }

    private function getProductsIcon(): string
    {
        $currentMonth = Product::whereMonth('created_at', now()->month)->count();
        $previousMonth = Product::whereMonth('created_at', now()->subMonth()->month)->count();

        return $currentMonth >= $previousMonth ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down';
    }

    private function getAverageRating(): string
    {
        $avgRating = Review::avg('rating');

        return number_format($avgRating ?? 0, 1) . '/5';
    }

    private function getRatingChange(): string
    {
        $currentMonth = Review::whereMonth('created_at', now()->month)->avg('rating');
        $previousMonth = Review::whereMonth('created_at', now()->subMonth()->month)->avg('rating');

        if (!$previousMonth) {
            return __('admin.widgets.no_previous_data');
        }

        $change = (($currentMonth - $previousMonth) / $previousMonth) * 100;

        return ($change >= 0 ? '+' : '') . number_format($change, 1) . '% ' . __('admin.widgets.from_last_month');
    }

    private function getRatingIcon(): string
    {
        $currentMonth = Review::whereMonth('created_at', now()->month)->avg('rating');
        $previousMonth = Review::whereMonth('created_at', now()->subMonth()->month)->avg('rating');

        return ($currentMonth ?? 0) >= ($previousMonth ?? 0) ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down';
    }

    private function getRatingColor(): string
    {
        $avgRating = Review::avg('rating');

        if ($avgRating >= 4.5) {
            return 'success';
        } elseif ($avgRating >= 3.5) {
            return 'warning';
        } else {
            return 'danger';
        }
    }
}
