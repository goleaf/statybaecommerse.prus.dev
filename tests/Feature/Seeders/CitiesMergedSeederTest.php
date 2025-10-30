<?php

declare(strict_types=1);

namespace Tests\Feature\Seeders;

use App\Models\City;
use App\Models\Country;
use App\Models\Translations\CityTranslation;
use App\Support\Locales;
use Database\Seeders\Cities\CitiesMergedSeeder;
use Database\Seeders\CountrySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Tests\TestCase;

final class CitiesMergedSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_curated_countries_receive_city_rows(): void
    {
        // Act: hydrate countries and then execute the merged city seeder so curated datasets land in the database.
        $this->seed(CountrySeeder::class);
        $this->seed(CitiesMergedSeeder::class);

        // Determine the ISO2 codes represented by the individual city seeders so we can assert coverage dynamically.
        $isoTargets = $this->discoverSeederIsoCodes();

        foreach ($isoTargets as $iso2) {
            $country = Country::query()->withoutGlobalScopes()->where('cca2', $iso2)->first();

            $this->assertNotNull(
                $country,
                sprintf('Country with ISO2 [%s] should exist before asserting city counts.', $iso2)
            );

            $cityCount = City::query()
                ->withoutGlobalScopes()
                ->where('country_id', $country->getKey())
                ->count();

            // Each curated country must surface at least one city record to satisfy storefront dropdowns.
            $this->assertGreaterThan(
                0,
                $cityCount,
                sprintf('Expected cities for country [%s] to be seeded.', $iso2)
            );
        }
    }

    public function test_city_slugs_are_unique_per_country(): void
    {
        // Arrange: run the canonical seeding pipeline so the assertions inspect the merged dataset.
        $this->seed(CountrySeeder::class);
        $this->seed(CitiesMergedSeeder::class);

        $duplicateCount = City::query()
            ->withoutGlobalScopes()
            ->selectRaw('COUNT(*) as aggregate')
            ->groupBy('country_id', 'slug')
            ->havingRaw('COUNT(*) > 1')
            ->count();

        // Upserts must not generate duplicate slug + country combinations.
        $this->assertSame(0, $duplicateCount, 'City slugs should remain unique per country.');
    }

    public function test_cities_expose_translations_for_every_supported_locale(): void
    {
        // Arrange: execute the seeders to populate baseline city and translation tables.
        $this->seed(CountrySeeder::class);
        $this->seed(CitiesMergedSeeder::class);

        $locales = Locales::supported();
        $expectedTranslations = City::query()->withoutGlobalScopes()->count() * count($locales);

        $actualTranslations = CityTranslation::query()
            ->whereIn('locale', $locales)
            ->count();

        // Every city should expose a translation row for each supported locale to keep dropdowns and analytics consistent.
        $this->assertSame(
            $expectedTranslations,
            $actualTranslations,
            'Each city should provide translated metadata for all supported locales.'
        );
    }

    /**
     * Discover the ISO2 codes exposed by the curated city seeder classes.
     *
     * @return array<int, string>
     */
    private function discoverSeederIsoCodes(): array
    {
        $files = Collection::make(glob(database_path('seeders/cities/*CitiesSeeder.php')));

        return $files
            ->map(static fn (string $file): string => 'Database\\Seeders\\Cities\\' . basename($file, '.php'))
            ->filter(static fn (string $class): bool => class_exists($class) && method_exists($class, 'iso2') && method_exists($class, 'data'))
            ->reject(static fn (string $class): bool => Str::endsWith($class, 'CitiesMergedSeeder'))
            ->reject(static fn (string $class): bool => Str::endsWith($class, 'CitySeederToolkit'))
            ->map(static fn (string $class): string => Str::upper((string) $class::iso2()))
            ->unique()
            ->values()
            ->all();
    }
}
