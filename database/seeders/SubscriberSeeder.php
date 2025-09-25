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

        Subscriber::query()->delete();
        User::whereIn('email', ['admin@example.com'])
            ->get()
            ->each(function (User $user): void {
                $user->subscribers()->delete();
            });

        Subscriber::factory()
            ->count(50)
            ->state(function () use ($companies): array {
                return [
                    'company' => fake()->optional(0.6)->randomElement($companies->toArray()),
                ];
            })
            ->create();

        User::factory()
            ->count(10)
            ->create()
            ->each(function (User $user) use ($companies): void {
                Subscriber::factory()
                    ->for($user)
                    ->state(fn () => [
                        'company' => fake()->optional(0.6)->randomElement($companies->toArray()),
                        'status' => 'active',
                        'subscribed_at' => now()->subDays(fake()->numberBetween(1, 365)),
                    ])
                    ->create([
                        'email' => $user->email,
                        'first_name' => explode(' ', $user->name)[0] ?? fake()->firstName(),
                        'last_name' => explode(' ', $user->name)[1] ?? fake()->lastName(),
                    ]);
            });

        $this->createFixedAdminSubscriber($companies->first() ?? 'Statybos Centras UAB');
    }

    private function createFixedAdminSubscriber(string $company): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Super Administrator',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'is_admin' => true,
            ]
        );

        Subscriber::factory()
            ->for($admin)
            ->state([
                'email' => 'admin@example.com',
                'first_name' => 'Super',
                'last_name' => 'Administrator',
                'company' => $company,
                'status' => 'active',
            ])
            ->create();
    }
}
