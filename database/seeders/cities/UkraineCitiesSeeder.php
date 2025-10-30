<?php

declare(strict_types=1);

namespace Database\Seeders\Cities;

use App\Support\Locales;
use Illuminate\Database\Seeder;

final class UkraineCitiesSeeder extends Seeder
{
    public static function iso2(): string
    {
        // Expose the ISO2 country code so the city toolkit can resolve the related country record.
        return 'UA';
    }

    /**
     * @return iterable<array<string, mixed>>
     */
    public static function data(): iterable
    {
        // The dataset is preserved verbatim from the legacy seeder to keep curated ordering intact.
        return [
            ['code' => 'UA-KIE', 'slug' => 'kyiv', 'name' => ['lt' => 'Kijevas', 'en' => 'Kyiv'], 'description' => 'Capital of ', 'is_capital' => true, 'latitude' => 50.4501, 'longitude' => 30.5234, 'population' => 2967360],
            ['code' => 'UA-KHA', 'slug' => 'kharkiv', 'name' => ['lt' => 'Charkivas', 'en' => 'Kharkiv'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 49.9935, 'longitude' => 36.2304, 'population' => 1441057],
            ['code' => 'UA-ODS', 'slug' => 'odessa', 'name' => ['lt' => 'Odesa', 'en' => 'Odesa'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 46.4825, 'longitude' => 30.7233, 'population' => 1015826],
            ['code' => 'UA-DON', 'slug' => 'dnipro', 'name' => ['lt' => 'Dniepropetrovskas', 'en' => 'Dnipro'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 48.4647, 'longitude' => 35.0462, 'population' => 976525],
            ['code' => 'UA-DON', 'slug' => 'donetsk', 'name' => ['lt' => 'Doneckas', 'en' => 'Donetsk'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 48.0159, 'longitude' => 37.8028, 'population' => 905364],
            ['code' => 'UA-ZAP', 'slug' => 'zaporizhzhia', 'name' => ['lt' => 'Zaporožė', 'en' => 'Zaporizhzhia'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 47.8388, 'longitude' => 35.1396, 'population' => 722713],
            ['code' => 'UA-LVI', 'slug' => 'lviv', 'name' => ['lt' => 'Lvovas', 'en' => 'Lviv'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 49.8397, 'longitude' => 24.0297, 'population' => 717273],
            ['code' => 'UA-KRI', 'slug' => 'kryvyi-rih', 'name' => ['lt' => 'Kryvyj Rihas', 'en' => 'Kryvyi Rih'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 47.9105, 'longitude' => 33.3918, 'population' => 612750],
            ['code' => 'UA-MYK', 'slug' => 'mykolaiv', 'name' => ['lt' => 'Mykolajivas', 'en' => 'Mykolaiv'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 46.9750, 'longitude' => 31.9946, 'population' => 480080],
            ['code' => 'UA-MAR', 'slug' => 'mariupol', 'name' => ['lt' => 'Mariupolis', 'en' => 'Mariupol'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 47.0961, 'longitude' => 37.5562, 'population' => 431859],
        ];
    }

    public function run(): void
    {
        $locales = Locales::supported();

        // Centralise insert/update logic through the shared toolkit for consistency across countries.
        CitySeederToolkit::upsertForCountry(self::iso2(), self::data(), $locales);
    }
}
