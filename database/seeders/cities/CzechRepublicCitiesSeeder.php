<?php

declare(strict_types=1);

namespace Database\Seeders\Cities;

use App\Support\Locales;
use Illuminate\Database\Seeder;

final class CzechRepublicCitiesSeeder extends Seeder
{
    public static function iso2(): string
    {
        // Expose the ISO2 country code so the city toolkit can resolve the related country record.
        return 'CZ';
    }

    /**
     * @return iterable<array<string, mixed>>
     */
    public static function data(): iterable
    {
        // The dataset is preserved verbatim from the legacy seeder to keep curated ordering intact.
        return [
            ['code' => 'CZ-PRG', 'slug' => 'prague', 'name' => ['lt' => 'Praha', 'en' => 'Prague'], 'description' => 'Capital of ', 'is_capital' => true, 'latitude' => 50.0755, 'longitude' => 14.4378, 'population' => 1335084],
            ['code' => 'CZ-BRN', 'slug' => 'brno', 'name' => ['lt' => 'Brno', 'en' => 'Brno'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 49.1951, 'longitude' => 16.6068, 'population' => 381346],
            ['code' => 'CZ-OST', 'slug' => 'ostrava', 'name' => ['lt' => 'Ostrava', 'en' => 'Ostrava'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 49.8209, 'longitude' => 18.2625, 'population' => 284982],
            ['code' => 'CZ-PLS', 'slug' => 'plzen', 'name' => ['lt' => 'Plzenas', 'en' => 'Plzeň'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 49.7475, 'longitude' => 13.3776, 'population' => 175219],
            ['code' => 'CZ-LIB', 'slug' => 'liberec', 'name' => ['lt' => 'Liberecas', 'en' => 'Liberec'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 50.7671, 'longitude' => 15.0566, 'population' => 104802],
            ['code' => 'CZ-OLO', 'slug' => 'olomouc', 'name' => ['lt' => 'Olomoucas', 'en' => 'Olomouc'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 49.5938, 'longitude' => 17.2509, 'population' => 100663],
            ['code' => 'CZ-CES', 'slug' => 'ceske-budejovice', 'name' => ['lt' => 'České Budějovice', 'en' => 'České Budějovice'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 48.9745, 'longitude' => 14.4747, 'population' => 95000],
            ['code' => 'CZ-HRA', 'slug' => 'hradec-kralove', 'name' => ['lt' => 'Hradec Králové', 'en' => 'Hradec Králové'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 50.2104, 'longitude' => 15.8252, 'population' => 92830],
            ['code' => 'CZ-UST', 'slug' => 'usti-nad-labem', 'name' => ['lt' => 'Ústí nad Labem', 'en' => 'Ústí nad Labem'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 50.6607, 'longitude' => 14.0322, 'population' => 92000],
            ['code' => 'CZ-PAR', 'slug' => 'pardubice', 'name' => ['lt' => 'Pardubice', 'en' => 'Pardubice'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 50.0343, 'longitude' => 15.7812, 'population' => 90000],
        ];
    }

    public function run(): void
    {
        $locales = Locales::supported();

        // Centralise insert/update logic through the shared toolkit for consistency across countries.
        CitySeederToolkit::upsertForCountry(self::iso2(), self::data(), $locales);
    }
}
