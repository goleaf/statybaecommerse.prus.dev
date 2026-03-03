<?php

declare(strict_types=1);

namespace Database\Seeders;

use function class_exists;
use function gc_collect_cycles;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Optimized full-profile seeder that executes only the core, production-relevant fixtures.
 */
final class OptimizedFullSeeder extends BaseSeeder
{
    use WithoutModelEvents;

    /**
     * @var array<int, class-string<Seeder>>
     */
    private const DEFAULT_SEEDERS = [
        CurrencySeeder::class,
        CountrySeeder::class,
        \Database\Seeders\Cities\CitiesMergedSeeder::class,
        AdminAuthorizationSeeder::class,
        AdminUserSeeder::class,
        NewsSeeder::class,
        CustomerGroupSeeder::class,
        ServiceSeeder::class,
        AttributeSeeder::class,
        AttributeValueSeeder::class,
        WarehouseSeeder::class,
        InventorySeeder::class,
        FeatureFlagSeeder::class,
        BrochureSeeder::class,
        SettingsSeeder::class,
    ];

    public function run(): void
    {
        DB::connection()->disableQueryLog();

        $enableFastMode = (bool) config('seeds.optimized_enables_fast_mode', true);
        $previousFastMode = (bool) config('seeds.fast_mode', false);

        if ($enableFastMode && ! $previousFastMode) {
            config()->set('seeds.fast_mode', true);
        }

        try {
            foreach ($this->standardSeeders() as $seederClass) {
                if (! class_exists($seederClass)) {
                    continue;
                }

                $this->call($seederClass);
                gc_collect_cycles();
            }
        } finally {
            config()->set('seeds.fast_mode', $previousFastMode);
        }
    }

    /**
     * @return array<int, class-string<Seeder>>
     */
    private function standardSeeders(): array
    {
        $configured = config('seeds.standard_seeders', self::DEFAULT_SEEDERS);

        if (! is_array($configured)) {
            return self::DEFAULT_SEEDERS;
        }

        $seeders = array_values(array_filter(
            $configured,
            static fn (mixed $class): bool => is_string($class) && $class !== ''
        ));

        if ($seeders === []) {
            return self::DEFAULT_SEEDERS;
        }

        return $seeders;
    }
}
