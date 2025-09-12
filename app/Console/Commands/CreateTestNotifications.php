<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\User;
use App\Notifications\LowStockAlert;
use App\Notifications\TestNotification;
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
            $query->whereIn('name', ['administrator', 'manager']);
        })->get();

        if ($adminUsers->isEmpty()) {
            $this->error('No admin users found. Please create admin users first.');

            return 1;
        }

        // Create various types of notifications
        $notifications = [
            [
                'title' => 'Sveiki atvykę į valdymo skydą',
                'message' => 'Jūsų e-komercijos sistema sėkmingai sukonfigūruota ir paruošta naudojimui.',
                'type' => 'success',
            ],
            [
                'title' => 'Sistemos atnaujinimas',
                'message' => 'Sistema buvo sėkmingai atnaujinta iki naujausios versijos.',
                'type' => 'info',
            ],
            [
                'title' => 'Priežiūros režimas',
                'message' => 'Sistema bus nepasiekiama dėl planuotos priežiūros nuo 02:00 iki 04:00.',
                'type' => 'warning',
            ],
            [
                'title' => 'Saugumo įspėjimas',
                'message' => 'Aptiktas įtartinas veiksmas. Prašome patikrinti savo paskyrą.',
                'type' => 'error',
            ],
        ];

        foreach ($adminUsers as $user) {
            foreach ($notifications as $notification) {
                $user->notify(new TestNotification(
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

        $this->info("Created {$totalNotifications} test notifications for ".$adminUsers->count().' admin users.');
        $this->info('Check the notification bell icon in the admin panel!');

        return 0;
    }
}
