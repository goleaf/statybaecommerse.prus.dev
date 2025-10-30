<?php

declare(strict_types=1);

namespace Database\Seeders\Cities;

use App\Support\Locales;
use Illuminate\Database\Seeder;

final class SlovakiaCitiesSeeder extends Seeder
{
    public static function iso2(): string
    {
        // Expose the ISO2 country code so the city toolkit can resolve the related country record.
        return 'SK';
    }

    /**
     * @return iterable<array<string, mixed>>
     */
    public static function data(): iterable
    {
        // The dataset is preserved verbatim from the legacy seeder to keep curated ordering intact.
        return [
            ['code' => 'SK-BRA', 'slug' => 'bratislava', 'name' => ['lt' => 'Bratislava', 'en' => 'Bratislava'], 'description' => 'Capital of ', 'is_capital' => true, 'latitude' => 48.1486, 'longitude' => 17.1077, 'population' => 475503],
            ['code' => 'SK-KOS', 'slug' => 'kosice', 'name' => ['lt' => 'Košice', 'en' => 'Košice'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 48.7164, 'longitude' => 21.2611, 'population' => 238593],
            ['code' => 'SK-PRE', 'slug' => 'presov', 'name' => ['lt' => 'Prešovas', 'en' => 'Prešov'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 49.0017, 'longitude' => 21.2394, 'population' => 88898],
            ['code' => 'SK-NIT', 'slug' => 'nitra', 'name' => ['lt' => 'Nitros', 'en' => 'Nitra'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 48.3069, 'longitude' => 18.0845, 'population' => 78489],
            ['code' => 'SK-ZIL', 'slug' => 'zilina', 'name' => ['lt' => 'Žilina', 'en' => 'Žilina'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 49.2231, 'longitude' => 18.7394, 'population' => 81515],
            ['code' => 'SK-BAN', 'slug' => 'banska-bystrica', 'name' => ['lt' => 'Banská Bystrica', 'en' => 'Banská Bystrica'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 48.7353, 'longitude' => 19.1458, 'population' => 78455],
            ['code' => 'SK-TRE', 'slug' => 'trnava', 'name' => ['lt' => 'Trnava', 'en' => 'Trnava'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 48.3764, 'longitude' => 17.5881, 'population' => 65000],
            ['code' => 'SK-MAR', 'slug' => 'martin', 'name' => ['lt' => 'Martinas', 'en' => 'Martin'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 49.0667, 'longitude' => 18.9167, 'population' => 54000],
            ['code' => 'SK-TRE', 'slug' => 'trencin', 'name' => ['lt' => 'Trenčinas', 'en' => 'Trenčín'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 48.8944, 'longitude' => 18.0406, 'population' => 55000],
            ['code' => 'SK-POP', 'slug' => 'poprad', 'name' => ['lt' => 'Popradas', 'en' => 'Poprad'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 49.0614, 'longitude' => 20.2978, 'population' => 52000],
        ];
    }

    public function run(): void
    {
        $locales = Locales::supported();

        // Centralise insert/update logic through the shared toolkit for consistency across countries.
        CitySeederToolkit::upsertForCountry(self::iso2(), self::data(), $locales);
    }
}
