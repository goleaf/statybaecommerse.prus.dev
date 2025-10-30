<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\AuthorizationRole;
use App\Models\AdminUser;
use Illuminate\Database\Seeder;

final class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Provision the primary admin guard accounts expected by Filament login flows.
        $accounts = [
            [
                'email' => 'superuser@example.com',
                'name' => 'Super User',
                'password' => 'admin123',
                'roles' => [
                    AuthorizationRole::SUPER_ADMIN->value,
                    AuthorizationRole::ADMIN->value,
                ],
            ],
            [
                'email' => 'admin@example.com',
                'name' => 'Administrator',
                'password' => 'admin123',
                'roles' => [
                    AuthorizationRole::ADMIN->value,
                    AuthorizationRole::ADMINISTRATOR->value,
                ],
            ],
        ];

        foreach ($accounts as $account) {
            $admin = AdminUser::query()->firstOrNew(['email' => $account['email']]);

            // Keep the guard credentials up to date so repeated seed runs refresh passwords and verification flags.
            $admin->fill([
                'name' => $account['name'],
                'password' => $account['password'],
                'email_verified_at' => now(),
            ]);

            if ($admin->isDirty()) {
                $admin->save();
            }

            // Sync the expected admin roles per account to guarantee panel access permissions.
            $admin->syncRoles($account['roles']);

            $this->command?->info(sprintf('🔐 Admin guard account ready: %s', $account['email']));
        }
    }
}
