<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AdminUser;

final class AdminUserSeeder extends BaseSeeder
{
    /**
     * @var array<int, array{email: string, name: string, password: string}>
     */
    private const ADMIN_ACCOUNTS = [
        [
            'email'    => 'admin@example.com',
            'name'     => 'Administrator',
            'password' => 'Admin123!',
        ],
        [
            'email'    => 'eegidia@gmail.com',
            'name'     => 'Egidijus Kalinauskas',
            'password' => '47077ca8d1099D@',
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $allowedEmails = array_column(self::ADMIN_ACCOUNTS, 'email');

        // Remove any legacy administrator accounts so only curated credentials remain available.
        AdminUser::query()
            ->whereNotIn('email', $allowedEmails)
            ->each(static function (AdminUser $admin): void {
                // Detach permissions before deletion to avoid pivot records hanging around between seed runs.
                $admin->roles()->detach();

                $admin->delete();
            });

        foreach (self::ADMIN_ACCOUNTS as $account) {
            $admin = AdminUser::query()->firstOrNew(['email' => $account['email']]);

            // Keep the guard credentials up to date so repeated seed runs refresh passwords and verification flags.
            $admin->fill([
                'name' => $account['name'],
                // Use a strong seed password so SecurePasswordHandling validation passes.
                'password'          => $account['password'],
                'email_verified_at' => now(),
            ]);

            if ($admin->isDirty()) {
                $admin->save();
            }
        }

        $this->command?->info('Admin guard accounts ready: ' . implode(', ', $allowedEmails));
    }
}
