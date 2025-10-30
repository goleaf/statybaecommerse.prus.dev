<?php

declare(strict_types=1);

namespace Database\Seeders\Cities;

use App\Support\Locales;
use Illuminate\Database\Seeder;

final class RomaniaCitiesSeeder extends Seeder
{
    public static function iso2(): string
    {
        // Expose the ISO2 country code so the city toolkit can resolve the related country record.
        return 'RO';
    }

    /**
     * @return iterable<array<string, mixed>>
     */
    public static function data(): iterable
    {
        // The dataset is preserved verbatim from the legacy seeder to keep curated ordering intact.
        return [
            ['code' => 'RO-BUC', 'slug' => 'bucharest', 'name' => ['lt' => 'Bukareštas', 'en' => 'Bucharest'], 'description' => 'Capital of ', 'is_capital' => true, 'latitude' => 44.4268, 'longitude' => 26.1025, 'population' => 1883425],
            ['code' => 'RO-CLU', 'slug' => 'cluj-napoca', 'name' => ['lt' => 'Klujas-Napoka', 'en' => 'Cluj-Napoca'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 46.7712, 'longitude' => 23.6236, 'population' => 324576],
            ['code' => 'RO-TIM', 'slug' => 'timisoara', 'name' => ['lt' => 'Timišoara', 'en' => 'Timișoara'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 45.7471, 'longitude' => 21.2087, 'population' => 319279],
            ['code' => 'RO-IAS', 'slug' => 'iasi', 'name' => ['lt' => 'Jašis', 'en' => 'Iași'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 47.1585, 'longitude' => 27.6014, 'population' => 290422],
            ['code' => 'RO-CON', 'slug' => 'constanta', 'name' => ['lt' => 'Konstanca', 'en' => 'Constanța'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 44.1598, 'longitude' => 28.6348, 'population' => 283872],
            ['code' => 'RO-CRA', 'slug' => 'craiova', 'name' => ['lt' => 'Kraijova', 'en' => 'Craiova'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 44.3302, 'longitude' => 23.7949, 'population' => 269506],
            ['code' => 'RO-GAL', 'slug' => 'galati', 'name' => ['lt' => 'Galacis', 'en' => 'Galați'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 45.4353, 'longitude' => 28.0080, 'population' => 249432],
            ['code' => 'RO-PLO', 'slug' => 'ploiesti', 'name' => ['lt' => 'Ploještis', 'en' => 'Ploiești'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 44.9419, 'longitude' => 26.0225, 'population' => 209945],
            ['code' => 'RO-BRA', 'slug' => 'brasov', 'name' => ['lt' => 'Brašovas', 'en' => 'Brașov'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 45.6427, 'longitude' => 25.5887, 'population' => 253200],
            ['code' => 'RO-BRA', 'slug' => 'braila', 'name' => ['lt' => 'Braila', 'en' => 'Brăila'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 45.2667, 'longitude' => 27.9833, 'population' => 180302],
        ];
    }

    public function run(): void
    {
        $locales = Locales::supported();

        // Centralise insert/update logic through the shared toolkit for consistency across countries.
        CitySeederToolkit::upsertForCountry(self::iso2(), self::data(), $locales);
    }
}
