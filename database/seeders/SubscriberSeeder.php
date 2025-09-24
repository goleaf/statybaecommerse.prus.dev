<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Subscriber;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class SubscriberSeeder extends Seeder
{
    public function run(): void
    {
        // Create sample companies and users first
        $companies = [
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
        ];

        $sources = ['website', 'admin', 'import', 'api', 'social', 'referral', 'event', 'other'];
        $interests = ['products', 'news', 'promotions', 'events', 'blog', 'technical', 'business', 'support'];
        $statuses = ['active', 'inactive', 'unsubscribed'];

        // Create 50 random subscribers
        for ($i = 1; $i <= 50; $i++) {
            $firstName = fake('lt_LT')->firstName();
            $lastName = fake('lt_LT')->lastName();
            $email = strtolower($firstName.'.'.$lastName.'@'.fake()->domainName());

            // Sometimes create a user account for the subscriber
            $user = null;
            if (fake()->boolean(30)) { // 30% chance to have a user account
                $user = User::create([
                    'name' => $firstName.' '.$lastName,
                    'email' => $email,
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]);
            }

            $subscriberData = [
                'user_id' => $user?->id,
                'email' => $email,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'phone' => fake()->optional(0.7)->phoneNumber(),
                'company' => fake()->optional(0.6)->randomElement($companies),
                'job_title' => fake()->optional(0.5)->jobTitle(),
                'interests' => fake()->randomElements($interests, fake()->numberBetween(1, 4)),
                'source' => fake()->randomElement($sources),
                'status' => fake()->randomElement($statuses),
                'subscribed_at' => fake()->dateTimeBetween('-2 years', 'now'),
                'unsubscribed_at' => fake()->optional(0.2)->dateTimeBetween('-1 year', 'now'),
                'last_email_sent_at' => fake()->optional(0.8)->dateTimeBetween('-6 months', 'now'),
                'email_count' => fake()->numberBetween(0, 25),
                'metadata' => [
                    'ip_address' => fake()->ipv4(),
                    'user_agent' => fake()->userAgent(),
                    'referrer' => fake()->optional(0.3)->url(),
                    'utm_source' => fake()->optional(0.4)->randomElement(['google', 'facebook', 'linkedin', 'direct']),
                    'utm_campaign' => fake()->optional(0.3)->randomElement(['summer_sale', 'newsletter_signup', 'product_launch']),
                ],
            ];

            // Adjust unsubscribed_at based on status
            if ($subscriberData['status'] === 'unsubscribed') {
                $subscriberData['unsubscribed_at'] = fake()->dateTimeBetween($subscriberData['subscribed_at'], 'now');
            } else {
                $subscriberData['unsubscribed_at'] = null;
            }

            Subscriber::create($subscriberData);
        }

        // Create some subscribers linked to existing users
        $existingUsers = User::whereNotIn('email', Subscriber::pluck('email'))->take(10)->get();
        foreach ($existingUsers as $user) {
            $nameParts = explode(' ', $user->name);
            $firstName = $nameParts[0] ?? 'Unknown';
            $lastName = $nameParts[1] ?? 'User';

            Subscriber::create([
                'user_id' => $user->id,
                'email' => $user->email,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'phone' => fake()->optional(0.7)->phoneNumber(),
                'company' => fake()->optional(0.6)->randomElement($companies),
                'job_title' => fake()->optional(0.5)->jobTitle(),
                'interests' => fake()->randomElements($interests, fake()->numberBetween(1, 4)),
                'source' => 'website',
                'status' => 'active',
                'subscribed_at' => fake()->dateTimeBetween('-1 year', 'now'),
                'email_count' => fake()->numberBetween(0, 15),
                'metadata' => [
                    'ip_address' => fake()->ipv4(),
                    'user_agent' => fake()->userAgent(),
                    'utm_source' => 'website',
                ],
            ]);
        }

        $this->command->info('Created 50+ subscribers with realistic data');
    }
}
