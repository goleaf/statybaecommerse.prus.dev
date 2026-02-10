<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AdminUser;

final class AdminUserSeeder extends BaseSeeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Remove any legacy administrator accounts so the canonical credential stays authoritative.
        AdminUser::query()
            ->where('email', '<>', 'admin@example.com')
            ->each(static function (AdminUser $admin): void {
                // Detach permissions before deletion to avoid pivot records hanging around between seed runs.
                $admin->roles()->detach();

                $admin->delete();
            });

        // Provision the single administrator account expected by browser-based login checks.
        $admin = AdminUser::query()->firstOrNew(['email' => 'admin@example.com']);

        // Keep the guard credentials up to date so repeated seed runs refresh passwords and verification flags.
        $admin->fill([
            'name' => 'Administrator',
            // Use a strong seed password so SecurePasswordHandling validation passes.
            'password'          => 'Admin123!',
            'email_verified_at' => now(),
        ]);

        if ($admin->isDirty()) {
            $admin->save();
        }

        $this->command?->info('Admin guard account ready: admin@example.com');
    }
}
