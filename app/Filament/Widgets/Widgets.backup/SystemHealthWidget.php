<?php declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Illuminate\Support\Facades\DB as Database;

final class SystemHealthWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected static bool $isLazy = true;

    protected function getStats(): array
    {
        $lowStockProducts = Product::where('stock_quantity', '<=', 5)->count();
        $outOfStockProducts = Product::where('stock_quantity', '<=', 0)->count();
        $pendingOrders = Order::where('status', 'pending')->count();
        $failedOrders = Order::where('payment_status', 'failed')->count();

        // Database health check
        $dbConnected = true;
        try {
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            $dbConnected = false;
        }

        return [
            Stat::make(__('admin.stats.low_stock_products'), $lowStockProducts)
                ->description(__('admin.stats.products_need_restock'))
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($lowStockProducts > 10 ? 'danger' : ($lowStockProducts > 0 ? 'warning' : 'success')),
            Stat::make(__('admin.stats.pending_orders'), $pendingOrders)
                ->description(__('admin.stats.orders_need_processing'))
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingOrders > 5 ? 'warning' : 'success'),
            Stat::make(__('admin.stats.system_status'), $dbConnected ? __('admin.stats.healthy') : __('admin.stats.issues'))
                ->description($dbConnected ? __('admin.stats.all_systems_operational') : __('admin.stats.database_connection_issue'))
                ->descriptionIcon($dbConnected ? 'heroicon-m-check-circle' : 'heroicon-m-x-circle')
                ->color($dbConnected ? 'success' : 'danger'),
            Stat::make(__('admin.stats.failed_payments'), $failedOrders)
                ->description(__('admin.stats.orders_payment_failed'))
                ->descriptionIcon('heroicon-m-credit-card')
                ->color($failedOrders > 0 ? 'danger' : 'success'),
        ];
    }
}
