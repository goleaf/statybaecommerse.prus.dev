<?php

declare(strict_types=1);

namespace Database\Seeders\Cities;

use App\Support\Locales;
use Illuminate\Database\Seeder;

final class SloveniaCitiesSeeder extends Seeder
{
    public static function iso2(): string
    {
        // Expose the ISO2 country code so the city toolkit can resolve the related country record.
        return 'SI';
    }

    /**
     * @return iterable<array<string, mixed>>
     */
    public static function data(): iterable
    {
        // The dataset is preserved verbatim from the legacy seeder to keep curated ordering intact.
        return [
            ['code' => 'SI-LJU', 'slug' => 'ljubljana', 'name' => ['lt' => 'Liubliana', 'en' => 'Ljubljana'], 'description' => 'Capital of ', 'is_capital' => true, 'latitude' => 46.0569, 'longitude' => 14.5058, 'population' => 279631],
            ['code' => 'SI-MAR', 'slug' => 'maribor', 'name' => ['lt' => 'Mariboras', 'en' => 'Maribor'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 46.5547, 'longitude' => 15.6467, 'population' => 112065],
            ['code' => 'SI-CEL', 'slug' => 'celje', 'name' => ['lt' => 'Celjė', 'en' => 'Celje'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 46.2309, 'longitude' => 15.2606, 'population' => 37872],
            ['code' => 'SI-KRA', 'slug' => 'kranj', 'name' => ['lt' => 'Kranjas', 'en' => 'Kranj'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 46.2389, 'longitude' => 14.3556, 'population' => 37547],
            ['code' => 'SI-VEL', 'slug' => 'velenje', 'name' => ['lt' => 'Velenjė', 'en' => 'Velenje'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 46.3619, 'longitude' => 15.1100, 'population' => 25000],
            ['code' => 'SI-KOP', 'slug' => 'koper', 'name' => ['lt' => 'Koperis', 'en' => 'Koper'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 45.5481, 'longitude' => 13.7301, 'population' => 25000],
            ['code' => 'SI-NOV', 'slug' => 'novo-mesto', 'name' => ['lt' => 'Novo Mestas', 'en' => 'Novo Mesto'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 45.8044, 'longitude' => 15.1689, 'population' => 23000],
            ['code' => 'SI-PTU', 'slug' => 'ptuj', 'name' => ['lt' => 'Ptujas', 'en' => 'Ptuj'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 46.4200, 'longitude' => 15.8700, 'population' => 18000],
            ['code' => 'SI-TRN', 'slug' => 'trbovlje', 'name' => ['lt' => 'Trbovljė', 'en' => 'Trbovlje'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 46.1500, 'longitude' => 15.0500, 'population' => 16000],
            ['code' => 'SI-KAM', 'slug' => 'kamnik', 'name' => ['lt' => 'Kamnikas', 'en' => 'Kamnik'], 'description' => 'Capital of ', 'is_capital' => false, 'latitude' => 46.2258, 'longitude' => 14.6122, 'population' => 14000],
        ];
    }

    public function run(): void
    {
        $locales = Locales::supported();

        // Centralise insert/update logic through the shared toolkit for consistency across countries.
        CitySeederToolkit::upsertForCountry(self::iso2(), self::data(), $locales);
    }
}
