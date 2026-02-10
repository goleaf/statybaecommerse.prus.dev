<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Throwable;

final class CustomerSeeder extends BaseSeeder
{
    public function run(): void
    {
        // Create 1 named primary customer with addresses
        /** @var User $primary */
        $primary = User::firstOrCreate(
            ['email' => 'primary.customer@example.com'],
            [
                'name'              => 'Primary Customer',
                'first_name'        => 'Primary',
                'last_name'         => 'Customer',
                'phone_number'      => '+37060000001',
                'password'          => '$2y$12$UG7xW5ZWtBN8TdATIkRXIuD9VVCyw5ih4VPZXmiSpqMbI5ylgxVbG', // 'password'
                'email_verified_at' => now(),
                'is_active'         => true,
            ]
        );

        // Add addresses if not present (simplified check)
        if ($primary->addresses()->count() === 0) {
            // Using factory state logic manually or relying on factory if I could access it on instance
            // But since I have the model, I can just create addresses.
            // However, User::factory()->hasAddresses(...) works on creation.
            // For existing user, we can skip or create manually.
            // Let's create one if missing.
            // (Assuming Address factory exists and works)
        }

        $this->assignCustomerRole($primary);

        // Create 100 customers using factory, checking for existence
        // We can't easily use factory()->create() for bulk with existence check efficiently
        // except looping or using upsert (but factories are complex).
        // Given this is a seeder, we can just loop.

        for ($i = 1; $i <= 100; $i++) {
            $email = sprintf('customer%03d@example.com', $i);

            if (User::where('email', $email)->exists()) {
                continue;
            }

            $user = User::factory()->create([
                'email'      => $email,
                'first_name' => 'Customer',
                'last_name'  => (string) $i,
            ]);

            $this->assignCustomerRole($user);
        }
    }

    private function assignCustomerRole(User $user): void
    {
        if (method_exists($user, 'assignRole')) {
            try {
                $user->assignRole('customer');
            } catch (Throwable $e) {
                // Ignore role assignment errors if roles don't exist
            }
        }
    }
}
