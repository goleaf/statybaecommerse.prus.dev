<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
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

    private function determineProfile(): string
    {
        $configured = config('seeds.runtime_profile');

        if (! \is_string($configured) || $configured === '') {
            $configured = config('seeds.default_profile', self::PROFILE_MINIMAL);
        }

        $normalized = strtolower($configured);

        return \in_array($normalized, [self::PROFILE_MINIMAL, self::PROFILE_FULL], true)
            ? $normalized
            : self::PROFILE_MINIMAL;
    }

    /**
     * @return array<int, class-string<Seeder>>
     */
    private function seedersForProfile(string $profile): array
    {
        $profiles = config('seeds.profiles', []);
        $minimal = Arr::get($profiles, self::PROFILE_MINIMAL, []);

        if ($profile === self::PROFILE_FULL) {
            $full = array_merge($minimal, Arr::get($profiles, self::PROFILE_FULL, []));

            return $this->uniqueSeeders($full);
        }

        return $this->uniqueSeeders($minimal);
    }

    /**
     * @param array<int, class-string<Seeder>> $seeders
     * @return array<int, class-string<Seeder>>
     */
    private function uniqueSeeders(array $seeders): array
    {
        return array_values(array_unique($seeders));
    }
}
