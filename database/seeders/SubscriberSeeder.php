<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Subscriber;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

use function collect;

final class SubscriberSeeder extends Seeder
{
    public function run(): void
    {
        $companies = collect([
            'Statybos Centras UAB',
            'Lietuvos Statybos',
            'Vilniaus Statybos',
            'Kauno Statybos',
            'Klaipėdos Statybos',
            'Panevėžio Statybos',
            'Šiaulių Statybos',
            'Alytaus Statybos',
            'Marijampolės Statybos',
            'Tauragės Statybos',
        ]);

        $this->seedBaseSubscribers($companies);
        $this->seedLinkedUserSubscribers($companies);
        $this->seedAdminSubscriber($companies->first() ?? 'Statybos Centras UAB');
    }

    private function seedBaseSubscribers(\Illuminate\Support\Collection $companies): void
    {
        collect(range(1, 40))->each(function (int $index) use ($companies): void {
            $email = "subscriber{$index}@statybae.test";
            Subscriber::withTrashed()->where('email', $email)->forceDelete();

            Subscriber::factory()
                ->state([
                    'email' => $email,
                    'first_name' => "Prenumeratorius {$index}",
                    'last_name' => 'Statyba',
                    'phone' => sprintf('+370600%04d', $index),
                    'company' => $companies->get(($index - 1) % $companies->count()),
                    'job_title' => 'Projektų vadovas',
                    'interests' => $this->determineInterests($index),
                    'source' => $this->determineSource($index),
                    'status' => $index % 5 === 0 ? 'inactive' : 'active',
                    'subscribed_at' => now()->subDays($index * 2),
                    'unsubscribed_at' => null,
                    'last_email_sent_at' => now()->subDays($index),
                    'email_count' => $index % 7,
                    'metadata' => [
                        'ip_address' => "192.168.1.{$index}",
                        'user_agent' => 'Seeder/1.0',
                        'utm_source' => $index % 3 === 0 ? 'google' : 'direct',
                        'utm_campaign' => $index % 4 === 0 ? 'summer_sale' : 'newsletter_signup',
                    ],
                ])
                ->create();
        });
    }

    private function seedLinkedUserSubscribers(\Illuminate\Support\Collection $companies): void
    {
        $password = Hash::make('password');

        collect(range(1, 10))->each(function (int $index) use ($companies, $password): void {
            $email = "company.user{$index}@statybae.test";
            User::withTrashed()->where('email', $email)->forceDelete();
            Subscriber::withTrashed()->where('email', $email)->forceDelete();

            $firstName = "Įmonės vartotojas {$index}";
            $lastName = 'Statyba';

            $user = User::factory()
                ->create([
                    'email' => $email,
                    'name' => $firstName.' '.$lastName,
                    'preferred_locale' => 'lt',
                    'email_verified_at' => now(),
                    'password' => $password,
                ]);

            Subscriber::factory()
                ->for($user)
                ->state([
                    'email' => $email,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'company' => $companies->get(($index - 1) % $companies->count()),
                    'status' => 'active',
                    'interests' => ['business', 'products', 'support'],
                    'subscribed_at' => now()->subDays($index),
                    'metadata' => [
                        'ip_address' => "10.0.0.{$index}",
                        'user_agent' => 'Seeder/1.0',
                    ],
                ])
                ->create();
        });
    }

    private function seedAdminSubscriber(string $company): void
    {
        User::withTrashed()->where('email', 'admin@example.com')->forceDelete();
        Subscriber::withTrashed()->where('email', 'admin@example.com')->forceDelete();

        $password = Hash::make('password');

        $admin = User::factory()
            ->admin()
            ->create([
                'email' => 'admin@example.com',
                'name' => 'Super Administrator',
                'password' => $password,
            ]);

        Subscriber::factory()
            ->for($admin)
            ->state([
                'email' => 'admin@example.com',
                'first_name' => 'Super',
                'last_name' => 'Administrator',
                'company' => $company,
                'status' => 'active',
                'interests' => ['business', 'support', 'promotions'],
                'subscribed_at' => now(),
            ])
            ->create();
    }

    private function determineInterests(int $index): array
    {
        $interestSets = [
            ['products', 'promotions'],
            ['news', 'events'],
            ['technical', 'support'],
            ['business', 'products', 'news'],
        ];

        return $interestSets[$index % count($interestSets)];
    }

    private function determineSource(int $index): string
    {
        return match ($index % 5) {
            0 => 'website',
            1 => 'admin',
            2 => 'api',
            3 => 'social',
            default => 'referral',
        };
    }
}
