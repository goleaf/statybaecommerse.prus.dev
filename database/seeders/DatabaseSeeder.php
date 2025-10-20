<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Activitylog\ActivityLogStatus;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        /** @var ActivityLogStatus $activityLogStatus */
        $activityLogStatus = app(ActivityLogStatus::class);
        $wasLoggingDisabled = $activityLogStatus->disabled();

        if (!$wasLoggingDisabled) {
            activity()->disableLogging();
        }

        $this->call([
            CurrencySeeder::class,
            AdminAuthorizationSeeder::class,
            RolesAndPermissionsSeeder::class,
            AdminUserSeeder::class,
            DemoStoreSeeder::class,
        ]);

        if (!$wasLoggingDisabled) {
            activity()->enableLogging();
        }
    }
}
