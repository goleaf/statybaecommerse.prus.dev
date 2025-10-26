<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final class SystemUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = (string) Config::get('attribution.system_user_email', '');

        if ($email === '') {
            return;
        }

        $user = User::query()->firstOrCreate(
            ['email' => $email],
            [
                'name'              => (string) Config::get('attribution.system_user_name', 'System User'),
                'password'          => Hash::make(Str::random(40)),
                'email_verified_at' => now(),
                'is_admin'          => true,
                'is_active'         => true,
            ]
        );

        Config::set('attribution.system_user_id', $user->getKey());
    }
}
