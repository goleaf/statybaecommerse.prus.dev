<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

final class ActivityLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some users for the activity logs
        $users = User::limit(5)->get();

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please run UserSeeder first.');

            return;
        }

        $events = ['created', 'updated', 'deleted', 'restored', 'login', 'logout', 'custom'];
        $logNames = ['default', 'auth', 'user', 'order', 'product', 'system'];
        $severities = ['low', 'medium', 'high', 'critical'];
        $categories = ['authentication', 'user_management', 'order_processing', 'product_management', 'system'];
        $deviceTypes = ['desktop', 'mobile', 'tablet'];
        $browsers = ['Chrome', 'Firefox', 'Safari', 'Edge'];
        $operatingSystems = ['Windows', 'macOS', 'Linux', 'iOS', 'Android'];
        $countries = ['LT', 'EN', 'DE', 'US', 'FR'];

        // Create 50 activity logs
        for ($i = 0; $i < 50; $i++) {
            $user = $users->random();

            ActivityLog::create([
                'log_name' => fake()->randomElement($logNames),
                'description' => fake()->sentence(),
                'event' => fake()->randomElement($events),
                'subject_type' => fake()->randomElement(['App\Models\User', 'App\Models\Product', 'App\Models\Order']),
                'subject_id' => fake()->numberBetween(1, 100),
                'causer_type' => 'App\Models\User',
                'causer_id' => $user->id,
                'properties' => [
                    'old_values' => fake()->words(3),
                    'new_values' => fake()->words(3),
                    'changes' => fake()->words(2),
                ],
                'batch_uuid' => Str::uuid(),
                'ip_address' => fake()->ipv4(),
                'user_agent' => fake()->userAgent(),
                'device_type' => fake()->randomElement($deviceTypes),
                'browser' => fake()->randomElement($browsers),
                'os' => fake()->randomElement($operatingSystems),
                'country' => fake()->randomElement($countries),
                'is_important' => fake()->boolean(20),  // 20% chance of being important
                'is_system' => fake()->boolean(30),  // 30% chance of being system
                'severity' => fake()->randomElement($severities),
                'category' => fake()->randomElement($categories),
                'notes' => fake()->optional(0.3)->sentence(),
                'created_at' => fake()->dateTimeBetween('-30 days', 'now'),
                'updated_at' => fake()->dateTimeBetween('-30 days', 'now'),
            ]);
        }

        $this->command->info('ActivityLogSeeder completed successfully.');
    }
}
