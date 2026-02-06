<?php

declare(strict_types=1);

namespace Database\Seeders;

use function class_exists;

use Database\Seeders\Cities\CitiesMergedSeeder;

use function gc_collect_cycles;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Optimized full-profile seeder that executes only the core, production-relevant fixtures.
 */
final class OptimizedFullSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * @var array<int, class-string<Seeder>>
     */
    private const SEEDERS = [
        CurrencySeeder::class,
        CountrySeeder::class,
        CitiesMergedSeeder::class,
        AdminAuthorizationSeeder::class,
        AdminUserSeeder::class,
        CustomerGroupSeeder::class,
        AttributeSeeder::class,
        AttributeValueSeeder::class,
        BrandSeeder::class,
        CategorySeeder::class,
        CollectionSeeder::class,
        FeatureFlagSeeder::class,
        SettingsSeeder::class,
        TurboEcommerceSeeder::class,
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
            foreach (self::SEEDERS as $seederClass) {
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
}
