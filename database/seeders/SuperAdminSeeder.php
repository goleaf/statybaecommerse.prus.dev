<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('app.admin_email', env('ADMIN_EMAIL', 'admin@example.com'));
        $password = env('ADMIN_PASSWORD', 'password');

        /** @var User $user */
        $user = User::query()->firstOrCreate([
            'email' => $email,
        ], [
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'password' => Hash::make($password),
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
        ]);

        if (! $user->hasRole(config('shopper.core.users.admin_role', 'administrator'))) {
            $user->assignRole(config('shopper.core.users.admin_role', 'administrator'));
        }
    }
}
