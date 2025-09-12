<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\LiveNotificationService;
use Illuminate\Console\Command;

final class TestLiveNotifications extends Command
{
    protected $signature = 'notifications:test-live';

    protected $description = 'Test the live notification system with various notification types';

    public function handle(): int
    {
        $this->info('Testing live notification system...');

        $notificationService = app(LiveNotificationService::class);

        // Test different types of notifications
        $notifications = [
            [
                'title' => 'Naujas užsakymas',
                'message' => 'Gautas naujas užsakymas #12345 už 125.50 €',
                'type' => 'success',
            ],
            [
                'title' => 'Mažos atsargos',
                'message' => 'Prekė "Samsung Galaxy S24" turi mažiau nei 10 vienetų atsargų',
                'type' => 'warning',
            ],
            [
                'title' => 'Mokėjimo klaida',
                'message' => 'Nepavyko apdoroti mokėjimo už užsakymą #12344',
                'type' => 'error',
            ],
            [
                'title' => 'Sistemos atnaujinimas',
                'message' => 'Sistema bus atnaujinta šį vakarą nuo 23:00 iki 01:00',
                'type' => 'info',
            ],
            [
                'title' => 'Naujas klientas',
                'message' => 'Registruotas naujas klientas: jonas.petras@example.com',
                'type' => 'success',
            ],
        ];

        foreach ($notifications as $index => $notification) {
            $this->info('Sending notification '.($index + 1).": {$notification['title']}");

            $notificationService->sendSystemNotification(
                $notification['title'],
                $notification['message'],
                $notification['type']
            );

            // Add a small delay between notifications to see them appear one by one
            sleep(2);
        }

        $this->info('Live notification test completed!');
        $this->info('Check the notification bell icon in the admin panel to see the live updates.');

        return 0;
    }
}
