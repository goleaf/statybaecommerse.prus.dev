<?php

declare(strict_types=1);

namespace Database\Seeders\Cities;

use App\Support\Locales;
use Illuminate\Database\Seeder;

final class CroatiaCitiesSeeder extends Seeder
{
    public static function iso2(): string
    {
        // Expose the ISO2 country code so the city toolkit can resolve the related country record.
        return 'HR';
    }

    /**
     * @return iterable<array<string, mixed>>
     */
    public static function data(): iterable
    {
        // The dataset is preserved verbatim from the legacy seeder to keep curated ordering intact.
        return [
            ['code' => 'HR-ZAG', 'slug' => 'zagreb', 'name' => ['lt' => 'Zagrebas', 'en' => 'Zagreb'], 'description' => 'Capital of ', 'is_capital' => true, 'latitude' => 45.8150, 'longitude' => 15.9819, 'population' => 806341],
            ['code' => 'HR-SPL', 'slug' => 'split', 'name' => ['lt' => 'Splitas', 'en' => 'Split'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 43.5081, 'longitude' => 16.4402, 'population' => 178102],
            ['code' => 'HR-RIJ', 'slug' => 'rijeka', 'name' => ['lt' => 'Rijeka', 'en' => 'Rijeka'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 45.3271, 'longitude' => 14.4422, 'population' => 128624],
            ['code' => 'HR-OSI', 'slug' => 'osijek', 'name' => ['lt' => 'Osijekas', 'en' => 'Osijek'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 45.5550, 'longitude' => 18.6955, 'population' => 108048],
            ['code' => 'HR-ZAD', 'slug' => 'zadar', 'name' => ['lt' => 'Zadaras', 'en' => 'Zadar'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 44.1194, 'longitude' => 15.2314, 'population' => 75082],
            ['code' => 'HR-SLA', 'slug' => 'slavonski-brod', 'name' => ['lt' => 'Slavonski Brodas', 'en' => 'Slavonski Brod'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 45.1603, 'longitude' => 18.0156, 'population' => 59041],
            ['code' => 'HR-PUL', 'slug' => 'pula', 'name' => ['lt' => 'Pula', 'en' => 'Pula'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 44.8667, 'longitude' => 13.8500, 'population' => 57053],
            ['code' => 'HR-KAR', 'slug' => 'karlovac', 'name' => ['lt' => 'Karlovacas', 'en' => 'Karlovac'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 45.4950, 'longitude' => 15.5500, 'population' => 55000],
            ['code' => 'HR-SIS', 'slug' => 'sisak', 'name' => ['lt' => 'Sisakas', 'en' => 'Sisak'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 45.4833, 'longitude' => 16.3667, 'population' => 47000],
            ['code' => 'HR-VAR', 'slug' => 'varazdin', 'name' => ['lt' => 'Varaždinas', 'en' => 'Varaždin'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 46.3044, 'longitude' => 16.3378, 'population' => 46000],
        ];
    }

    public function run(): void
    {
        $locales = Locales::supported();

        // Centralise insert/update logic through the shared toolkit for consistency across countries.
        CitySeederToolkit::upsertForCountry(self::iso2(), self::data(), $locales);
    }
}
