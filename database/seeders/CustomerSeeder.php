<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

final class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        // Create 1 named primary customer with addresses
        /** @var User $primary */
        $primary = User::factory()
            ->state([
                'email' => 'primary.customer@example.com',
                'first_name' => 'Primary',
                'last_name' => 'Customer',
            ])
            ->hasAddresses(fake()->numberBetween(1, 3))
            ->create();

        $this->assignCustomerRole($primary);

        // Create 100 customers using factory
        User::factory()
            ->count(100)
            ->sequence(fn ($sequence) => [
                'email' => sprintf('customer%03d@example.com', $sequence->index + 1),
                'first_name' => 'Customer',
                'last_name' => (string) ($sequence->index + 1),
            ])
            ->create()
            ->each(fn (User $user) => $this->assignCustomerRole($user));
    }

    private function assignCustomerRole(User $user): void
    {
        if (method_exists($user, 'assignRole')) {
            try {
                $user->assignRole('customer');
            } catch (\Throwable $e) {
                // Ignore role assignment errors if roles don't exist
            }
        }
    }
}
