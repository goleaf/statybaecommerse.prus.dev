<?php

declare(strict_types=1);

namespace Database\Seeders\Cities;

use App\Support\Locales;
use Illuminate\Database\Seeder;

final class SerbiaCitiesSeeder extends Seeder
{
    public static function iso2(): string
    {
        // Expose the ISO2 country code so the city toolkit can resolve the related country record.
        return 'RS';
    }

    /**
     * @return iterable<array<string, mixed>>
     */
    public static function data(): iterable
    {
        // The dataset is preserved verbatim from the legacy seeder to keep curated ordering intact.
        return [
            ['code' => 'RS-BEG', 'slug' => 'belgrade', 'name' => ['lt' => 'Belgradas', 'en' => 'Belgrade'], 'description' => 'Capital of ', 'is_capital' => true, 'latitude' => 44.7866, 'longitude' => 20.4489, 'population' => 1378682],
            ['code' => 'RS-NOV', 'slug' => 'novi-sad', 'name' => ['lt' => 'Novi Sadas', 'en' => 'Novi Sad'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 45.2671, 'longitude' => 19.8335, 'population' => 277522],
            ['code' => 'RS-NIS', 'slug' => 'nis', 'name' => ['lt' => 'Nišas', 'en' => 'Niš'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 43.3209, 'longitude' => 21.8958, 'population' => 183164],
            ['code' => 'RS-KRA', 'slug' => 'kragujevac', 'name' => ['lt' => 'Kragujevacas', 'en' => 'Kragujevac'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 44.0167, 'longitude' => 20.9167, 'population' => 150835],
            ['code' => 'RS-SUB', 'slug' => 'subotica', 'name' => ['lt' => 'Subotica', 'en' => 'Subotica'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 46.1000, 'longitude' => 19.6667, 'population' => 97000],
            ['code' => 'RS-ZRE', 'slug' => 'zrenjanin', 'name' => ['lt' => 'Zrenjaninas', 'en' => 'Zrenjanin'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 45.3833, 'longitude' => 20.3833, 'population' => 76000],
            ['code' => 'RS-PAN', 'slug' => 'pancevo', 'name' => ['lt' => 'Pančevas', 'en' => 'Pančevo'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 44.8667, 'longitude' => 20.6500, 'population' => 76000],
            ['code' => 'RS-CAC', 'slug' => 'cacak', 'name' => ['lt' => 'Čačakas', 'en' => 'Čačak'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 43.8914, 'longitude' => 20.3497, 'population' => 73000],
            ['code' => 'RS-NOV', 'slug' => 'novi-pazar', 'name' => ['lt' => 'Novi Pazaras', 'en' => 'Novi Pazar'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 43.1367, 'longitude' => 20.5122, 'population' => 66000],
            ['code' => 'RS-KRA', 'slug' => 'kraljevo', 'name' => ['lt' => 'Kraljevas', 'en' => 'Kraljevo'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 43.7258, 'longitude' => 20.6894, 'population' => 64000],
        ];
    }

    public function run(): void
    {
        $locales = Locales::supported();

        // Centralise insert/update logic through the shared toolkit for consistency across countries.
        CitySeederToolkit::upsertForCountry(self::iso2(), self::data(), $locales);
    }
}
