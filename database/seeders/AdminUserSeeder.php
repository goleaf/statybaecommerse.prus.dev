<?php declare(strict_types=1);

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
            'password' => 'admin123',
            'email_verified_at' => now(),
        ]);

        if ($admin->isDirty()) {
            $admin->save();
        }

        // Sync the expected admin roles to guarantee panel access permissions after cleanup.
        $admin->syncRoles([
            AuthorizationRole::SUPER_ADMIN->value,
            AuthorizationRole::ADMIN->value,
            AuthorizationRole::ADMINISTRATOR->value,
        ]);

        $this->command?->info('🔐 Admin guard account ready: admin@example.com');
    }
}
