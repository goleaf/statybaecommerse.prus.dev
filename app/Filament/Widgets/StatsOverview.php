<?php declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

final class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $totalProducts = Product::count();
        $totalOrders = Order::count();
        $totalCustomers = User::whereHas('orders')->count();
        $totalRevenue = Order::sum('total') ?? 0;

        return [
            Stat::make('Produktai iš viso', $totalProducts)
                ->description('Aktyvūs produktai')
                ->descriptionIcon('heroicon-m-cube')
                ->color('success'),
                
            Stat::make('Užsakymai iš viso', $totalOrders)
                ->description('Visi užsakymai')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('info'),
                
            Stat::make('Klientai iš viso', $totalCustomers)
                ->description('Registruoti klientai')
                ->descriptionIcon('heroicon-m-users')
                ->color('warning'),
                
            Stat::make('Pajamos iš viso', '€' . number_format($totalRevenue, 2))
                ->description('Visos pajamos')
                ->descriptionIcon('heroicon-m-currency-euro')
                ->color('success'),
        ];
    }
}
