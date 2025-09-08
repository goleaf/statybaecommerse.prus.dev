<?php declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

final class RealTimeNotificationsWidget extends Widget
{
    protected string $view = 'filament.widgets.real-time-notifications';
    protected static ?int $sort = 5;
    protected int | string | array $columnSpan = 'full';

    public function getNotifications(): Collection
    {
        $notifications = collect();

        // New orders in last 24 hours
        $newOrders = Order::where('created_at', '>=', now()->subDay())->count();
        if ($newOrders > 0) {
            $notifications->push([
                'type' => 'info',
                'icon' => 'heroicon-o-shopping-bag',
                'title' => __('admin.notifications.new_orders'),
                'message' => __('admin.notifications.new_orders_count', ['count' => $newOrders]),
                'time' => now()->subHour()->diffForHumans(),
                'action_url' => route('filament.admin.resources.orders.index'),
            ]);
        }

        // Low stock alerts
        $lowStockCount = Product::where('stock_quantity', '<=', 5)->count();
        if ($lowStockCount > 0) {
            $notifications->push([
                'type' => 'warning',
                'icon' => 'heroicon-o-exclamation-triangle',
                'title' => __('admin.notifications.low_stock'),
                'message' => __('admin.notifications.low_stock_count', ['count' => $lowStockCount]),
                'time' => now()->subMinutes(30)->diffForHumans(),
                'action_url' => route('filament.admin.resources.products.index', ['tableFilters' => ['stock' => ['value' => 'low']]]),
            ]);
        }

        // Failed payments
        $failedPayments = Order::where('payment_status', 'failed')
            ->where('created_at', '>=', now()->subHours(6))
            ->count();
        if ($failedPayments > 0) {
            $notifications->push([
                'type' => 'danger',
                'icon' => 'heroicon-o-credit-card',
                'title' => __('admin.notifications.failed_payments'),
                'message' => __('admin.notifications.failed_payments_count', ['count' => $failedPayments]),
                'time' => now()->subHours(2)->diffForHumans(),
                'action_url' => route('filament.admin.resources.orders.index', ['tableFilters' => ['payment_status' => ['value' => 'failed']]]),
            ]);
        }

        // New customer registrations
        $newCustomers = User::where('is_admin', false)
            ->where('created_at', '>=', now()->subDay())
            ->count();
        if ($newCustomers > 0) {
            $notifications->push([
                'type' => 'success',
                'icon' => 'heroicon-o-user-plus',
                'title' => __('admin.notifications.new_customers'),
                'message' => __('admin.notifications.new_customers_count', ['count' => $newCustomers]),
                'time' => now()->subHours(4)->diffForHumans(),
                'action_url' => route('filament.admin.resources.customer-management.index'),
            ]);
        }

        return $notifications;
    }
}
