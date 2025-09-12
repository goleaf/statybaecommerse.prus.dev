<?php declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Console\Command;

final class TestNotificationsCommand extends Command
{
    protected $signature = 'notifications:test {--user-id=1 : User ID to create notifications for}';

    protected $description = 'Create test notifications for testing the notification system';

    public function handle(NotificationService $notificationService): int
    {
        $userId = (int) $this->option('user-id');
        $user = User::find($userId);

        if (!$user) {
            $this->error("User with ID {$userId} not found.");
            return 1;
        }

        $this->info("Creating test notifications for user: {$user->name}");

        // Create order notifications
        $notificationService->createOrderNotification($user, 'created', [
            'id' => 1,
            'number' => 'ORD-001',
        ]);

        $notificationService->createOrderNotification($user, 'shipped', [
            'id' => 1,
            'number' => 'ORD-001',
        ], true);

        // Create product notifications
        $notificationService->createProductNotification($user, 'low_stock', [
            'id' => 1,
            'name' => 'Test Product',
        ], true);

        $notificationService->createProductNotification($user, 'created', [
            'id' => 2,
            'name' => 'New Product',
        ]);

        // Create system notification
        $notificationService->createSystemNotification($user, 'maintenance_started', [], true);

        $this->info('Test notifications created successfully!');
        $this->info('Check the admin panel to see the notifications.');

        return 0;
    }
}
