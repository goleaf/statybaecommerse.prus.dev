<?php declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

final class EnhancedEcommerceOverview extends BaseWidget
{
    protected ?string $pollingInterval = '15s';
    public static ?int $sort = null;

    public function getStats(): array
    {
        return [
            Stat::make('Total Revenue', $this->getTotalRevenue())
                ->description('Total revenue from completed orders')
                ->descriptionIcon('heroicon-m-currency-euro')
                ->color('success'),
            Stat::make('Total Orders', $this->getTotalOrders())
                ->description('Total number of orders')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('primary'),
            Stat::make('Customers', $this->getTotalCustomers())
                ->description('Total number of customers')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),
            Stat::make('Average Order Value', $this->getAverageOrderValue())
                ->description('Average value per order')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('warning'),
            Stat::make('Active Products', $this->getTotalProducts())
                ->description('Total visible products')
                ->descriptionIcon('heroicon-m-cube')
                ->color('success'),
            Stat::make('Average Rating', $this->getAverageRating())
                ->description('Average product rating')
                ->descriptionIcon('heroicon-m-star')
                ->color('warning'),
        ];
    }

    public function getTotalRevenue(): string
    {
        $total = Order::query()
            ->where('status', 'completed')
            ->sum('total');

        return '€' . number_format($total, 2);
    }

    public function getTotalOrders(): string
    {
        return (string) Order::query()->count();
    }

    public function getTotalCustomers(): string
    {
        return (string) User::query()->count();
    }

    public function getAverageOrderValue(): string
    {
        $completedOrders = Order::query()
            ->where('status', 'completed')
            ->get();

        if ($completedOrders->isEmpty()) {
            return '€0.00';
        }

        $average = $completedOrders->avg('total');
        return '€' . number_format($average, 2);
    }

    public function getTotalProducts(): string
    {
        return (string) Product::query()
            ->where('is_visible', true)
            ->count();
    }

    public function getAverageRating(): string
    {
        $average = Review::query()->avg('rating');

        if ($average === null) {
            return '0.0/5';
        }

        return number_format($average, 1) . '/5';
    }

    public function getRevenueChange(): string
    {
        $currentMonth = Order::query()
            ->where('status', 'completed')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total');

        $previousMonth = Order::query()
            ->where('status', 'completed')
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->sum('total');

        if ($previousMonth == 0) {
            return $currentMonth > 0 ? '+100%' : '0%';
        }

        $change = (($currentMonth - $previousMonth) / $previousMonth) * 100;
        return ($change >= 0 ? '+' : '') . number_format($change, 1) . '%';
    }

    public function getOrdersChange(): string
    {
        $currentMonth = Order::query()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $previousMonth = Order::query()
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();

        if ($previousMonth == 0) {
            return $currentMonth > 0 ? '+100%' : '0%';
        }

        $change = (($currentMonth - $previousMonth) / $previousMonth) * 100;
        return ($change >= 0 ? '+' : '') . number_format($change, 1) . '%';
    }

    public function getRevenueIcon(): string
    {
        $change = $this->getRevenueChange();
        return str_starts_with($change, '+') ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down';
    }

    public function getOrdersIcon(): string
    {
        $change = $this->getOrdersChange();
        return str_starts_with($change, '+') ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down';
    }

    public function getRevenueColor(): string
    {
        $change = $this->getRevenueChange();
        return str_starts_with($change, '+') ? 'success' : 'danger';
    }

    public function getOrdersColor(): string
    {
        $change = $this->getOrdersChange();
        return str_starts_with($change, '+') ? 'success' : 'danger';
    }
}
