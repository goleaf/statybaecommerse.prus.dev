<?php

declare(strict_types=1);

namespace Database\Seeders\Cities;

use App\Support\Locales;
use Illuminate\Database\Seeder;

final class BulgariaCitiesSeeder extends Seeder
{
    public static function iso2(): string
    {
        // Expose the ISO2 country code so the city toolkit can resolve the related country record.
        return 'BG';
    }

    /**
     * @return iterable<array<string, mixed>>
     */
    public static function data(): iterable
    {
        // The dataset is preserved verbatim from the legacy seeder to keep curated ordering intact.
        return [
            ['code' => 'BG-SOF', 'slug' => 'sofia', 'name' => ['lt' => 'Sofija', 'en' => 'Sofia'], 'description' => 'Capital of ', 'is_capital' => true, 'latitude' => 42.6977, 'longitude' => 23.3219, 'population' => 1241675],
            ['code' => 'BG-PLO', 'slug' => 'plovdiv', 'name' => ['lt' => 'Plovdivas', 'en' => 'Plovdiv'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 42.1354, 'longitude' => 24.7453, 'population' => 346893],
            ['code' => 'BG-VAR', 'slug' => 'varna', 'name' => ['lt' => 'Varna', 'en' => 'Varna'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 43.2141, 'longitude' => 27.9147, 'population' => 335177],
            ['code' => 'BG-BUR', 'slug' => 'burgas', 'name' => ['lt' => 'Burgasas', 'en' => 'Burgas'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 42.5048, 'longitude' => 27.4626, 'population' => 203017],
            ['code' => 'BG-RUS', 'slug' => 'ruse', 'name' => ['lt' => 'Rusė', 'en' => 'Ruse'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 43.8564, 'longitude' => 25.9564, 'population' => 144936],
            ['code' => 'BG-STA', 'slug' => 'stara-zagora', 'name' => ['lt' => 'Stara Zagora', 'en' => 'Stara Zagora'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 42.4258, 'longitude' => 25.6344, 'population' => 138272],
            ['code' => 'BG-PLE', 'slug' => 'pleven', 'name' => ['lt' => 'Plevenas', 'en' => 'Pleven'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 43.4170, 'longitude' => 24.6067, 'population' => 106954],
            ['code' => 'BG-SLI', 'slug' => 'sliven', 'name' => ['lt' => 'Slivenas', 'en' => 'Sliven'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 42.6858, 'longitude' => 26.3292, 'population' => 91000],
            ['code' => 'BG-DOB', 'slug' => 'dobrich', 'name' => ['lt' => 'Dobričas', 'en' => 'Dobrich'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 43.5726, 'longitude' => 27.8273, 'population' => 90000],
            ['code' => 'BG-SHU', 'slug' => 'shumen', 'name' => ['lt' => 'Šumenas', 'en' => 'Shumen'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 43.2706, 'longitude' => 26.9361, 'population' => 80000],
        ];
    }

    public function run(): void
    {
        $locales = Locales::supported();

        // Centralise insert/update logic through the shared toolkit for consistency across countries.
        CitySeederToolkit::upsertForCountry(self::iso2(), self::data(), $locales);
    }
}
