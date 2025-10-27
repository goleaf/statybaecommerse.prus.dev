<?php

declare(strict_types=1);

namespace Database\Seeders;

use function array_key_exists;

use Illuminate\Database\Seeder;
use Spatie\Activitylog\ActivityLogStatus;

final class DatabaseSeeder extends Seeder
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
        $profiles = config('seeds.profiles', []);
        $defaultProfile = (string) config('seeds.default_profile', 'full');
        $activeProfile = (string) config('seeds.active_profile', $defaultProfile);

        if (! is_array($profiles) || $profiles === []) {
            // Bail out early with the legacy seeder order if configuration is missing.
            $seeders = [
                CurrencySeeder::class,
                AttributeSeeder::class,
                AttributeValueSeeder::class,
                AdminAuthorizationSeeder::class,
                RolesAndPermissionsSeeder::class,
                AdminUserSeeder::class,
                CustomerGroupSeeder::class,
                DemoStoreSeeder::class,
            ];
        } else {
            // Gracefully fall back to the default profile when an invalid name is provided.
            $resolvedProfile = array_key_exists($activeProfile, $profiles)
                ? $activeProfile
                : $defaultProfile;

            $seeders = $profiles[$resolvedProfile] ?? [];
        }

        /** @var ActivityLogStatus $activityLogStatus */
        $activityLogStatus = app(ActivityLogStatus::class);
        $wasLoggingDisabled = $activityLogStatus->disabled();

        if (! $wasLoggingDisabled) {
            // Temporarily suspend activity logging for cleaner seed runs without touching the logger facade.
            $activityLogStatus->disable();
        }

        try {
            $this->call($seeders);
        } finally {
            if (! $wasLoggingDisabled) {
                // Ensure logging is re-enabled even if a seeder fails midway.
                $activityLogStatus->enable();
            }
        }
    }
}
