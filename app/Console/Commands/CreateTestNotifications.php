<?php declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\AdminNotification;
use App\Notifications\LowStockAlert;
use App\Models\Product;
use Illuminate\Console\Command;

final class CreateTestNotifications extends Command
{
    protected $signature = 'notifications:create-test';
    protected $description = 'Create test notifications for the admin panel';

    public function handle(): int
    {
        $this->info('Creating test notifications...');

        // Get admin users
        $adminUsers = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['Administrator', 'Manager']);
        })->get();

        if ($adminUsers->isEmpty()) {
            $this->error('No admin users found. Please create admin users first.');
            return 1;
        }

        // Create various types of notifications
        $notifications = [
            [
                'title' => __('admin.notifications.welcome_title'),
                'message' => __('admin.notifications.welcome_message'),
                'type' => 'success'
            ],
            [
                'title' => __('admin.notifications.system_update_title'),
                'message' => __('admin.notifications.system_update_message'),
                'type' => 'info'
            ],
            [
                'title' => __('admin.notifications.maintenance_title'),
                'message' => __('admin.notifications.maintenance_message'),
                'type' => 'warning'
            ],
            [
                'title' => __('admin.notifications.security_alert_title'),
                'message' => __('admin.notifications.security_alert_message'),
                'type' => 'error'
            ],
        ];

        foreach ($adminUsers as $user) {
            foreach ($notifications as $notification) {
                $user->notify(new AdminNotification(
                    $notification['title'],
                    $notification['message'],
                    $notification['type']
                ));
            }
        }

        // Create low stock alerts if products exist
        $lowStockProducts = Product::where('stock_quantity', '<=', 10)->take(3)->get();
        
        foreach ($lowStockProducts as $product) {
            foreach ($adminUsers as $user) {
                $user->notify(new LowStockAlert($product));
            }
        }

        $totalNotifications = $adminUsers->count() * count($notifications) + ($lowStockProducts->count() * $adminUsers->count());
        
        $this->info("Created {$totalNotifications} test notifications for " . $adminUsers->count() . " admin users.");
        $this->info("Check the notification bell icon in the admin panel!");

        return 0;
    }
}
