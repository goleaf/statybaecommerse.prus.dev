<?php declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\NotificationService;
use Illuminate\Console\Command;

final class DemoNotifications extends Command
{
    protected $signature = 'notifications:demo';
    protected $description = 'Create demo notifications to showcase the notification system';

    public function handle(): int
    {
        $this->info('Creating demo notifications...');

        $notificationService = app(NotificationService::class);

        // Create different types of notifications
        $notifications = [
            [
                'title' => 'Naujas užsakymas',
                'message' => 'Gautas naujas užsakymas #12345 už 125.50 €',
                'type' => 'success'
            ],
            [
                'title' => 'Mažos atsargos',
                'message' => 'Prekė "Samsung Galaxy S24" turi mažiau nei 10 vienetų atsargų',
                'type' => 'warning'
            ],
            [
                'title' => 'Mokėjimo klaida',
                'message' => 'Nepavyko apdoroti mokėjimo už užsakymą #12344',
                'type' => 'error'
            ],
            [
                'title' => 'Sistemos atnaujinimas',
                'message' => 'Sistema bus atnaujinta šį vakarą nuo 23:00 iki 01:00',
                'type' => 'info'
            ],
            [
                'title' => 'Naujas klientas',
                'message' => 'Registruotas naujas klientas: jonas.petras@example.com',
                'type' => 'success'
            ],
        ];

        foreach ($notifications as $notification) {
            $notificationService->sendToAdmins(
                $notification['title'],
                $notification['message'],
                $notification['type']
            );
        }

        $this->info('Created ' . count($notifications) . ' demo notifications.');
        $this->info('Check the notification bell icon in the admin panel!');

        return 0;
    }
}
