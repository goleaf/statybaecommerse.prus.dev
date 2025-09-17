<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Translations\CountryTranslation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class SimpleCountrySeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing data
        DB::table('country_translations')->delete();
        DB::table('countries')->delete();

        // Create Lithuania
        $lithuania = Country::create([
            'cca2' => 'LT',
            'cca3' => 'LTU',
            'phone_calling_code' => '370',
            'flag' => '🇱🇹',
            'region' => 'Europe',
            'subregion' => 'Northern Europe',
            'latitude' => 55.169438,
            'longitude' => 23.881275,
            'currencies' => ['EUR'],
            'is_enabled' => true,
            'sort_order' => 1,
        ]);

        // Create translations for Lithuania
        CountryTranslation::create([
            'country_id' => $lithuania->id,
            'locale' => 'lt',
            'name' => 'Lietuva',
            'name_official' => 'Lietuvos Respublika',
        ]);

        CountryTranslation::create([
            'country_id' => $lithuania->id,
            'locale' => 'en',
            'name' => 'Lithuania',
            'name_official' => 'Republic of Lithuania',
        ]);

        // Create Germany
        $germany = Country::create([
            'cca2' => 'DE',
            'cca3' => 'DEU',
            'phone_calling_code' => '49',
            'flag' => '🇩🇪',
            'region' => 'Europe',
            'subregion' => 'Central Europe',
            'latitude' => 51.165691,
            'longitude' => 10.451526,
            'currencies' => ['EUR'],
            'is_enabled' => true,
            'sort_order' => 2,
        ]);

        // Create translations for Germany
        CountryTranslation::create([
            'country_id' => $germany->id,
            'locale' => 'lt',
            'name' => 'Vokietija',
            'name_official' => 'Vokietijos Federacinė Respublika',
        ]);

        CountryTranslation::create([
            'country_id' => $germany->id,
            'locale' => 'en',
            'name' => 'Germany',
            'name_official' => 'Federal Republic of Germany',
        ]);

        // Create United States
        $usa = Country::create([
            'cca2' => 'US',
            'cca3' => 'USA',
            'phone_calling_code' => '1',
            'flag' => '🇺🇸',
            'region' => 'Americas',
            'subregion' => 'North America',
            'latitude' => 37.09024,
            'longitude' => -95.712891,
            'currencies' => ['USD'],
            'is_enabled' => true,
            'sort_order' => 3,
        ]);

        // Create translations for USA
        CountryTranslation::create([
            'country_id' => $usa->id,
            'locale' => 'lt',
            'name' => 'Jungtinės Amerikos Valstijos',
            'name_official' => 'Amerikos Jungtinės Valstijos',
        ]);

        CountryTranslation::create([
            'country_id' => $usa->id,
            'locale' => 'en',
            'name' => 'United States',
            'name_official' => 'United States of America',
        ]);

        echo 'Created '.Country::count().' countries with '.CountryTranslation::count()." translations.\n";
    }
}
