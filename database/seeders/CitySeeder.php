<?php

declare(strict_types=1);

namespace Database\Seeders;

use Database\Seeders\Cities\CitiesMergedSeeder;
use Illuminate\Database\Seeder;
use RuntimeException;

/**
 * Compatibility wrapper that delegates legacy CitySeeder invocations to the
 * consolidated {@see CitiesMergedSeeder}. Historically this class embedded
 * country-specific arrays, zones, and region lookups directly which led to
 * drift from the curated per-country seeders. Keeping the class ensures
 * existing automation can continue calling `CitySeeder` while guaranteeing the
 * merged toolkit remains the single entry point.
 */
final class CitySeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            // Guard against accidental production execution just like the
            // orchestrator to protect live datasets from seed mutations.
            throw new RuntimeException('CitySeeder cannot run in production environments.');
        }

        // Ensure prerequisite countries exist; the seeder is idempotent so
        // calling it multiple times only refreshes metadata when necessary.
        $this->call(CountrySeeder::class);

        // Delegate the heavy lifting to the merged city seeder so every
        // country-specific dataset funnels through the shared toolkit.
        $this->call(CitiesMergedSeeder::class);
    }
}
