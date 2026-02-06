<?php

declare(strict_types=1);

namespace Database\Seeders;

use function array_filter;
use function array_key_exists;
use function array_unique;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use function is_string;

final class DatabaseSeeder extends BaseSeeder
{
    use WithoutModelEvents;

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
                // Default to the aggregator so a legacy configuration still seeds everything.
                AllSeedersSeeder::class,
            ];
        } else {
            // Gracefully fall back to the default profile when an invalid name is provided.
            $resolvedProfile = array_key_exists($activeProfile, $profiles)
                ? $activeProfile
                : $defaultProfile;

            $seeders = $profiles[$resolvedProfile] ?? [];
        }

        $seeders = array_values(array_unique(array_filter(
            is_array($seeders) ? $seeders : [],
            static fn (mixed $seeder): bool => is_string($seeder) && class_exists($seeder)
        )));

        if ($seeders === []) {
            return;
        }

        try {
            $this->call($seeders);
        } finally {
            // Cleanup completed
        }
    }
}
