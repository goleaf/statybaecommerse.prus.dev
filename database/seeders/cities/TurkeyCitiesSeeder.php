<?php

declare(strict_types=1);

namespace Database\Seeders\Cities;

use App\Support\Locales;
use Illuminate\Database\Seeder;

final class TurkeyCitiesSeeder extends Seeder
{
    public static function iso2(): string
    {
        // Expose the ISO2 country code so the city toolkit can resolve the related country record.
        return 'TR';
    }

    /**
     * @return iterable<array<string, mixed>>
     */
    public static function data(): iterable
    {
        // The dataset is preserved verbatim from the legacy seeder to keep curated ordering intact.
        return [
            ['code' => 'TR-IST', 'slug' => 'istanbul', 'name' => ['lt' => 'Stambulas', 'en' => 'Istanbul'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 41.0082, 'longitude' => 28.9784, 'population' => 15519267],
            ['code' => 'TR-ANK', 'slug' => 'ankara', 'name' => ['lt' => 'Ankara', 'en' => 'Ankara'], 'description' => 'Capital of ', 'is_capital' => true, 'latitude' => 39.9334, 'longitude' => 32.8597, 'population' => 5503985],
            ['code' => 'TR-IZM', 'slug' => 'izmir', 'name' => ['lt' => 'Izmiras', 'en' => 'İzmir'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 38.4192, 'longitude' => 27.1287, 'population' => 4367251],
            ['code' => 'TR-BUR', 'slug' => 'bursa', 'name' => ['lt' => 'Bursa', 'en' => 'Bursa'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 40.1826, 'longitude' => 29.0665, 'population' => 3053331],
            ['code' => 'TR-ANT', 'slug' => 'antalya', 'name' => ['lt' => 'Antalija', 'en' => 'Antalya'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 36.8969, 'longitude' => 30.7133, 'population' => 2426356],
            ['code' => 'TR-ADA', 'slug' => 'adana', 'name' => ['lt' => 'Adana', 'en' => 'Adana'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 37.0000, 'longitude' => 35.3213, 'population' => 2220125],
            ['code' => 'TR-KON', 'slug' => 'konya', 'name' => ['lt' => 'Konija', 'en' => 'Konya'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 37.8667, 'longitude' => 32.4833, 'population' => 2232374],
            ['code' => 'TR-GAZ', 'slug' => 'gaziantep', 'name' => ['lt' => 'Gaziantepas', 'en' => 'Gaziantep'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 37.0662, 'longitude' => 37.3833, 'population' => 2028563],
            ['code' => 'TR-MER', 'slug' => 'mersin', 'name' => ['lt' => 'Mersinas', 'en' => 'Mersin'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 36.8000, 'longitude' => 34.6333, 'population' => 1814468],
            ['code' => 'TR-DIA', 'slug' => 'diyarbakir', 'name' => ['lt' => 'Dijarbakiras', 'en' => 'Diyarbakır'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 37.9144, 'longitude' => 40.2306, 'population' => 1750000],
        ];
    }

    public function run(): void
    {
        $locales = Locales::supported();

        // Centralise insert/update logic through the shared toolkit for consistency across countries.
        CitySeederToolkit::upsertForCountry(self::iso2(), self::data(), $locales);
    }
}
