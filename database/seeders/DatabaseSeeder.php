<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Spatie\Activitylog\ActivityLogStatus;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database using the configured profile.
     */
    public function run(): void
    {
        /**
         * Resolve the list of configured profiles with a defensive fallback
         * in case the configuration has been modified unexpectedly.
         */
        $profiles = Config::get('seeds.profiles', []);
        $defaultProfile = (string) Config::get('seeds.default_profile', 'full');
        $activeProfile = (string) Config::get('seeds.active_profile', $defaultProfile);

        if (! is_array($profiles) || $profiles === []) {
            // Bail out early with the legacy seeder order if configuration is missing.
            $seeders = [
                CurrencySeeder::class,
                AttributeSeeder::class,
                AttributeValueSeeder::class,
                AdminAuthorizationSeeder::class,
                RolesAndPermissionsSeeder::class,
                AdminUserSeeder::class,
                DemoStoreSeeder::class,
            ];
        } else {
            // Gracefully fall back to the default profile when an invalid name is provided.
            $resolvedProfile = Arr::has($profiles, $activeProfile)
                ? $activeProfile
                : $defaultProfile;

            $seeders = Arr::get($profiles, $resolvedProfile, []);
        }

        /** @var ActivityLogStatus $activityLogStatus */
        $activityLogStatus = app(ActivityLogStatus::class);
        $wasLoggingDisabled = $activityLogStatus->disabled();

        if (! $wasLoggingDisabled) {
            // Temporarily suspend activity logging for cleaner seed runs.
            activity()->disableLogging();
        }

        try {
            $this->call($seeders);
        } finally {
            if (! $wasLoggingDisabled) {
                // Ensure logging is re-enabled even if a seeder fails midway.
                activity()->enableLogging();
            }
        }
    }
}
