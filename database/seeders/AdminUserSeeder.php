<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superUser = User::query()->firstOrNew(['email' => 'superuser@example.com']);

        $superUser->forceFill([
            'name'              => 'Super User',
            'password'          => Hash::make('password'),
            'email_verified_at' => now(),
            'is_admin'          => true,
            'is_active'         => true,
        ])->save();

        if (! $superUser->hasRole('admin')) {
            $superUser->assignRole('admin');
        }

        if (! $superUser->hasRole('administrator')) {
            $superUser->assignRole('administrator');
        }
    }
}
