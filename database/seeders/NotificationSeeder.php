<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Seeder;

final class NotificationSeeder extends Seeder
{
    private const NOTIFICATION_TEMPLATES = [
        [
            'type'     => 'order',
            'title'    => 'Naujas užsakymas',
            'message'  => 'Užsakymas #1001 sėkmingai priimtas.',
            'urgent'   => false,
            'read'     => false,
            'days_ago' => 2,
        ],
        [
            'type'     => 'system',
            'title'    => 'Sistemos pranešimas',
            'message'  => 'Platforma atnaujinta be trikdžių.',
            'urgent'   => false,
            'read'     => true,
            'days_ago' => 4,
        ],
        [
            'type'     => 'support',
            'title'    => 'Palaikymo užklausa',
            'message'  => 'Gautas klientų palaikymo pranešimas.',
            'urgent'   => true,
            'read'     => false,
            'days_ago' => 1,
        ],
        [
            'type'     => 'promotion',
            'title'    => 'Akcija prasidėjo',
            'message'  => 'Riboto laiko nuolaidos iki 25 %.',
            'urgent'   => true,
            'read'     => true,
            'days_ago' => 6,
        ],
    ];

    public function run(): void
    {
        $users = User::query()->limit(5)->get();

        if ($users->isEmpty()) {
            $this->command?->warn('Nerasta vartotojų. Praleidžiamas NotificationSeeder vykdymas.');

            return;
        }

        $users->each(function (User $user): void {
            $this->seedNotificationsForUser($user);
        });
    }

    private function seedNotificationsForUser(User $user): void
    {
        collect(self::NOTIFICATION_TEMPLATES)->each(function (array $template) use ($user): void {
            $factory = Notification::factory()
                ->forUser($user)
                ->state(fn (array $attributes): array => [
                    'data' => [
                        'title'   => $template['title'],
                        'message' => $template['message'],
                        'type'    => $template['type'],
                        'urgent'  => $template['urgent'],
                    ],
                    'created_at' => now()->subDays($template['days_ago']),
                ]);

            $factory = ($template['read'] ?? false) ? $factory->read() : $factory->unread();

            $factory->create();
        });
    }
}
