<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 1 named user with 1-3 random addresses
        $primary = User::query()->firstOrCreate([
            'email' => 'primary.customer@example.com',
        ], [
            'first_name' => 'Primary',
            'last_name' => 'Customer',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
        ]);
        $this->createRandomAddresses($primary, random_int(1, 3));
        if (method_exists($primary, 'assignRole')) {
            try {
                $primary->assignRole('customer');
            } catch (\Throwable $e) {
            }
        }

        // Create 100 customers
        $count = 100;
        for ($i = 1; $i <= $count; $i++) {
            $email = sprintf('customer%03d@example.com', $i);
            /** @var User $user */
            $user = User::query()->firstOrCreate([
                'email' => $email,
            ], [
                'first_name' => 'Customer',
                'last_name' => (string) $i,
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
            ]);
            if (method_exists($user, 'assignRole')) {
                try {
                    $user->assignRole('customer');
                } catch (\Throwable $e) {
                }
            }
        }
    }

    private function createRandomAddresses(User $user, int $num): void
    {
        $num = max(1, min(3, $num));
        for ($i = 0; $i < $num; $i++) {
            $isDefault = $i === 0;
            $type = $i % 2 === 0 ? 'shipping' : 'billing';
            $data = [
                'type' => $type,
                'first_name' => $user->first_name ?? 'Customer',
                'last_name' => $user->last_name ?? 'User',
                'address_line_1' => 'Gedimino pr. '.random_int(1, 50),
                'city' => 'Vilnius',
                'postal_code' => '01103',
                'is_default' => $isDefault,
            ];
            // support either country_code or country if present
            try {
                if (\Schema::hasColumn('addresses', 'country_code')) {
                    $data['country_code'] = 'LT';
                }
            } catch (\Throwable $e) {
            }
            try {
                if (\Schema::hasColumn('addresses', 'country')) {
                    $data['country'] = 'LT';
                }
            } catch (\Throwable $e) {
            }
            $user->addresses()->create($data);
        }
    }
}
