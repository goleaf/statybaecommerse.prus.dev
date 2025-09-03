<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
        $count = (int) (env('CUSTOMER_SEED_COUNT', 25));
        for ($i = 1; $i <= max(1, $count); $i++) {
            $email = "customer{$i}@example.com";
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
                    // ignore if role doesn't exist yet
                }
            }
        }
    }
}
