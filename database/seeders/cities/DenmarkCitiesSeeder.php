<?php

declare(strict_types=1);

namespace Database\Seeders\Cities;

use App\Models\City;
use App\Models\Country;
use App\Models\Translations\CityTranslation;
use App\Models\Zone;
use Illuminate\Database\Seeder;

final class DenmarkCitiesSeeder extends Seeder
{
    public function run(): void
    {
        $denmark = Country::where('cca2', 'DK')->first();
        $euZone = Zone::where('code', 'EU')->first();

        // Regions are no longer used in the database schema
        
        $cities = [
            // Capital Region
            [
                'name' => 'Copenhagen',
                'code' => 'DK-84-COP',
                'is_capital' => true,
                'is_default' => true,
                'latitude' => 55.6761,
                'longitude' => 12.5683,
                'population' => 1346485,
                'postal_codes' => ['1000', '1001', '1002'],
                'translations' => [
                    'lt' => ['name' => 'Kopenhaga', 'description' => 'Danijos sostinė'],
                    'en' => ['name' => 'Copenhagen', 'description' => 'Capital of Denmark']
            ],
            ],
            [
                'name' => 'Frederiksberg',
                'code' => 'DK-84-FRE',
                'latitude' => 55.6777,
                'longitude' => 12.5350,
                'population' => 104000,
                'postal_codes' => ['2000'],
                'translations' => [
                    'lt' => ['name' => 'Frederiksbergas', 'description' => 'Kopenhagos priemiestis'],
                    'en' => ['name' => 'Frederiksberg', 'description' => 'Copenhagen suburb']
            ],
            ],
            [
                'name' => 'Hillerød',
                'code' => 'DK-84-HIL',
                'latitude' => 55.9333,
                'longitude' => 12.3000,
                'population' => 32000,
                'postal_codes' => ['3400'],
                'translations' => [
                    'lt' => ['name' => 'Hilerodas', 'description' => 'Karališkojo rūmo miestas'],
                    'en' => ['name' => 'Hillerød', 'description' => 'Royal palace city']
            ],
            ],
            [
                'name' => 'Helsingør',
                'code' => 'DK-84-HEL',
                'latitude' => 56.0333,
                'longitude' => 12.6167,
                'population' => 47000,
                'postal_codes' => ['3000'],
                'translations' => [
                    'lt' => ['name' => 'Helsingoras', 'description' => 'Uostamiesčis'],
                    'en' => ['name' => 'Helsingør', 'description' => 'Port city']
            ],
            ],

            // Central Jutland
            [
                'name' => 'Aarhus',
                'code' => 'DK-82-AAR',
                'latitude' => 56.1572,
                'longitude' => 10.2107,
                'population' => 285273,
                'postal_codes' => ['8000'],
                'translations' => [
                    'lt' => ['name' => 'Orhusas', 'description' => 'Antrasis didžiausias Danijos miestas'],
                    'en' => ['name' => 'Aarhus', 'description' => 'Second largest city in Denmark']
            ],
            ],
            [
                'name' => 'Randers',
                'code' => 'DK-82-RAN',
                'latitude' => 56.4600,
                'longitude' => 10.0364,
                'population' => 62000,
                'postal_codes' => ['8900'],
                'translations' => [
                    'lt' => ['name' => 'Randersas', 'description' => 'Pramonės miestas'],
                    'en' => ['name' => 'Randers', 'description' => 'Industrial city']
            ],
            ],
            [
                'name' => 'Silkeborg',
                'code' => 'DK-82-SIL',
                'latitude' => 56.1833,
                'longitude' => 9.5500,
                'population' => 50000,
                'postal_codes' => ['8600'],
                'translations' => [
                    'lt' => ['name' => 'Silkeborgas', 'description' => 'Gamtos miestas'],
                    'en' => ['name' => 'Silkeborg', 'description' => 'Nature city']
            ],
            ],
            [
                'name' => 'Viborg',
                'code' => 'DK-82-VIB',
                'latitude' => 56.4500,
                'longitude' => 9.4000,
                'population' => 40000,
                'postal_codes' => ['8800'],
                'translations' => [
                    'lt' => ['name' => 'Viborgas', 'description' => 'Istorinis miestas'],
                    'en' => ['name' => 'Viborg', 'description' => 'Historic city']
            ],
            ],

            // South Denmark
            [
                'name' => 'Odense',
                'code' => 'DK-83-ODE',
                'latitude' => 55.3959,
                'longitude' => 10.3883,
                'population' => 180863,
                'postal_codes' => ['5000'],
                'translations' => [
                    'lt' => ['name' => 'Odensė', 'description' => 'Anderseno miestas'],
                    'en' => ['name' => 'Odense', 'description' => 'Andersen city']
            ],
            ],
            [
                'name' => 'Esbjerg',
                'code' => 'DK-83-ESB',
                'latitude' => 55.4667,
                'longitude' => 8.4500,
                'population' => 72000,
                'postal_codes' => ['6700'],
                'translations' => [
                    'lt' => ['name' => 'Esbjergas', 'description' => 'Uostamiesčis'],
                    'en' => ['name' => 'Esbjerg', 'description' => 'Port city']
            ],
            ],
            [
                'name' => 'Kolding',
                'code' => 'DK-83-KOL',
                'latitude' => 55.4900,
                'longitude' => 9.4700,
                'population' => 61000,
                'postal_codes' => ['6000'],
                'translations' => [
                    'lt' => ['name' => 'Koldingas', 'description' => 'Pramonės miestas'],
                    'en' => ['name' => 'Kolding', 'description' => 'Industrial city']
            ],
            ],
            [
                'name' => 'Vejle',
                'code' => 'DK-83-VEJ',
                'latitude' => 55.7167,
                'longitude' => 9.5333,
                'population' => 58000,
                'postal_codes' => ['7100'],
                'translations' => [
                    'lt' => ['name' => 'Vejlė', 'description' => 'Pramonės centras'],
                    'en' => ['name' => 'Vejle', 'description' => 'Industrial center']
            ],
            ],
            [
                'name' => 'Horsens',
                'code' => 'DK-83-HOR',
                'latitude' => 55.8600,
                'longitude' => 9.8500,
                'population' => 61000,
                'postal_codes' => ['8700'],
                'translations' => [
                    'lt' => ['name' => 'Horsenas', 'description' => 'Pramonės miestas'],
                    'en' => ['name' => 'Horsens', 'description' => 'Industrial city']
            ],
            ],

            // Zealand
            [
                'name' => 'Roskilde',
                'code' => 'DK-85-ROS',
                'latitude' => 55.6500,
                'longitude' => 12.0833,
                'population' => 52000,
                'postal_codes' => ['4000'],
                'translations' => [
                    'lt' => ['name' => 'Roskilė', 'description' => 'Muzikos festivalio miestas'],
                    'en' => ['name' => 'Roskilde', 'description' => 'Music festival city']
            ],
            ],
            [
                'name' => 'Næstved',
                'code' => 'DK-85-NAE',
                'latitude' => 55.2333,
                'longitude' => 11.7667,
                'population' => 44000,
                'postal_codes' => ['4700'],
                'translations' => [
                    'lt' => ['name' => 'Nestvedas', 'description' => 'Pramonės miestas'],
                    'en' => ['name' => 'Næstved', 'description' => 'Industrial city']
            ],
            ],
            [
                'name' => 'Slagelse',
                'code' => 'DK-85-SLA',
                'latitude' => 55.4000,
                'longitude' => 11.3500,
                'population' => 33000,
                'postal_codes' => ['4200'],
                'translations' => [
                    'lt' => ['name' => 'Slagelsė', 'description' => 'Istorinis miestas'],
                    'en' => ['name' => 'Slagelse', 'description' => 'Historic city']
            ],
            ],
            [
                'name' => 'Holbæk',
                'code' => 'DK-85-HOL',
                'latitude' => 55.7167,
                'longitude' => 11.7167,
                'population' => 28000,
                'postal_codes' => ['4300'],
                'translations' => [
                    'lt' => ['name' => 'Holbekas', 'description' => 'Uostamiesčis'],
                    'en' => ['name' => 'Holbæk', 'description' => 'Port city']
            ],
            ],

            // North Jutland
            [
                'name' => 'Aalborg',
                'code' => 'DK-81-AAL',
                'latitude' => 57.0500,
                'longitude' => 9.9167,
                'population' => 120000,
                'postal_codes' => ['9000'],
                'translations' => [
                    'lt' => ['name' => 'Olborgas', 'description' => 'Šiaurės Jutlandijos sostinė'],
                    'en' => ['name' => 'Aalborg', 'description' => 'Capital of North Jutland']
            ],
            ],
            [
                'name' => 'Hjørring',
                'code' => 'DK-81-HJO',
                'latitude' => 57.4667,
                'longitude' => 9.9833,
                'population' => 25000,
                'postal_codes' => ['9800'],
                'translations' => [
                    'lt' => ['name' => 'Hjoringas', 'description' => 'Pramonės miestas'],
                    'en' => ['name' => 'Hjørring', 'description' => 'Industrial city']
            ],
            ],
            [
                'name' => 'Frederikshavn',
                'code' => 'DK-81-FRE',
                'latitude' => 57.4333,
                'longitude' => 10.5333,
                'population' => 23000,
                'postal_codes' => ['9900'],
                'translations' => [
                    'lt' => ['name' => 'Frederikshavnas', 'description' => 'Uostamiesčis'],
                    'en' => ['name' => 'Frederikshavn', 'description' => 'Port city']
            ],
            ],
            [
                'name' => 'Thisted',
                'code' => 'DK-81-THI',
                'latitude' => 56.9500,
                'longitude' => 8.7000,
                'population' => 13000,
                'postal_codes' => ['7700'],
                'translations' => [
                    'lt' => ['name' => 'Tistedas', 'description' => 'Kaimo miestas'],
                    'en' => ['name' => 'Thisted', 'description' => 'Rural city']
            ],
            ],

            // Additional major cities
            [
                'name' => 'Herning',
                'code' => 'DK-82-HER',
                'latitude' => 56.1333,
                'longitude' => 8.9833,
                'population' => 50000,
                'postal_codes' => ['7400'],
                'translations' => [
                    'lt' => ['name' => 'Hernigas', 'description' => 'Tekstilės pramonės centras'],
                    'en' => ['name' => 'Herning', 'description' => 'Textile industry center']
            ],
            ],
            [
                'name' => 'Haderslev',
                'code' => 'DK-83-HAD',
                'latitude' => 55.2500,
                'longitude' => 9.5000,
                'population' => 22000,
                'postal_codes' => ['6100'],
                'translations' => [
                    'lt' => ['name' => 'Haderslevas', 'description' => 'Istorinis miestas'],
                    'en' => ['name' => 'Haderslev', 'description' => 'Historic city']
            ],
            ],
            [
                'name' => 'Svendborg',
                'code' => 'DK-83-SVE',
                'latitude' => 55.0667,
                'longitude' => 10.6000,
                'population' => 27000,
                'postal_codes' => ['5700'],
                'translations' => [
                    'lt' => ['name' => 'Svendborgas', 'description' => 'Uostamiesčis'],
                    'en' => ['name' => 'Svendborg', 'description' => 'Port city']
            ],
            ],
            [
                'name' => 'Nykøbing Falster',
                'code' => 'DK-85-NYK',
                'latitude' => 54.7667,
                'longitude' => 11.8667,
                'population' => 17000,
                'postal_codes' => ['4800'],
                'translations' => [
                    'lt' => ['name' => 'Nykøbing Falster', 'description' => 'Falsterio miestas'],
                    'en' => ['name' => 'Nykøbing Falster', 'description' => 'Falster city']
            ],
            ],
            [
                'name' => 'Ringsted',
                'code' => 'DK-85-RIN',
                'latitude' => 55.4500,
                'longitude' => 11.8000,
                'population' => 22000,
                'postal_codes' => ['4100'],
                'translations' => [
                    'lt' => ['name' => 'Ringstedas', 'description' => 'Istorinis miestas'],
                    'en' => ['name' => 'Ringsted', 'description' => 'Historic city']
            ],
            ],
            // Additional Capital Region cities
            [
                'name' => 'Hillerød',
                'code' => 'DK-84-HIL',
                'latitude' => 55.9333,
                'longitude' => 12.3000,
                'population' => 32000,
                'postal_codes' => ['3400'],
                'translations' => [
                    'lt' => ['name' => 'Hilerodas', 'description' => 'Frederiksborg pilies miestas'],
                    'en' => ['name' => 'Hillerød', 'description' => 'Frederiksborg Castle city']
            ],
            ],
            [
                'name' => 'Helsingør',
                'code' => 'DK-84-HEL',
                'latitude' => 56.0333,
                'longitude' => 12.6167,
                'population' => 47000,
                'postal_codes' => ['3000'],
                'translations' => [
                    'lt' => ['name' => 'Helsingoras', 'description' => 'Hamleto miestas'],
                    'en' => ['name' => 'Helsingør', 'description' => 'Hamlet city']
            ],
            ],
            [
                'name' => 'Rødovre',
                'code' => 'DK-84-ROD',
                'latitude' => 55.6833,
                'longitude' => 12.4500,
                'population' => 38000,
                'postal_codes' => ['2610'],
                'translations' => [
                    'lt' => ['name' => 'Rødovrė', 'description' => 'Kopenhagos priemiestis'],
                    'en' => ['name' => 'Rødovre', 'description' => 'Copenhagen suburb']
            ],
            ],
            // Additional Central Jutland cities
            [
                'name' => 'Viborg',
                'code' => 'DK-82-VIB',
                'latitude' => 56.4500,
                'longitude' => 9.4000,
                'population' => 40000,
                'postal_codes' => ['8800'],
                'translations' => [
                    'lt' => ['name' => 'Viborgas', 'description' => 'Vidurio Jutlandijos sostinė'],
                    'en' => ['name' => 'Viborg', 'description' => 'Central Jutland capital']
            ],
            ],
            [
                'name' => 'Holstebro',
                'code' => 'DK-82-HOL',
                'latitude' => 56.3667,
                'longitude' => 8.6167,
                'population' => 35000,
                'postal_codes' => ['7500'],
                'translations' => [
                    'lt' => ['name' => 'Holstebras', 'description' => 'Pramonės miestas'],
                    'en' => ['name' => 'Holstebro', 'description' => 'Industrial city']
            ],
            ],
            [
                'name' => 'Herning',
                'code' => 'DK-82-HER',
                'latitude' => 56.1333,
                'longitude' => 8.9667,
                'population' => 50000,
                'postal_codes' => ['7400'],
                'translations' => [
                    'lt' => ['name' => 'Herningas', 'description' => 'Tekstilės pramonės centras'],
                    'en' => ['name' => 'Herning', 'description' => 'Textile industry center']
            ],
            ],
            [
                'name' => 'Silkeborg',
                'code' => 'DK-82-SIL',
                'latitude' => 56.1833,
                'longitude' => 9.5500,
                'population' => 45000,
                'postal_codes' => ['8600'],
                'translations' => [
                    'lt' => ['name' => 'Silkeborgas', 'description' => 'Gamtos miestas'],
                    'en' => ['name' => 'Silkeborg', 'description' => 'Nature city']
            ],
            ],
            [
                'name' => 'Randers',
                'code' => 'DK-82-RAN',
                'latitude' => 56.4500,
                'longitude' => 10.0333,
                'population' => 62000,
                'postal_codes' => ['8900'],
                'translations' => [
                    'lt' => ['name' => 'Randersas', 'description' => 'Gudeno miestas'],
                    'en' => ['name' => 'Randers', 'description' => 'Gudenå city']
            ],
            ],
            // Additional South Denmark cities
            [
                'name' => 'Vejle',
                'code' => 'DK-83-VEJ',
                'latitude' => 55.7167,
                'longitude' => 9.5333,
                'population' => 58000,
                'postal_codes' => ['7100'],
                'translations' => [
                    'lt' => ['name' => 'Vejlė', 'description' => 'Pramonės miestas'],
                    'en' => ['name' => 'Vejle', 'description' => 'Industrial city']
            ],
            ],
            [
                'name' => 'Kolding',
                'code' => 'DK-83-KOL',
                'latitude' => 55.4833,
                'longitude' => 9.4667,
                'population' => 61000,
                'postal_codes' => ['6000'],
                'translations' => [
                    'lt' => ['name' => 'Koldingas', 'description' => 'Sienos miestas'],
                    'en' => ['name' => 'Kolding', 'description' => 'Border city']
            ],
            ],
            [
                'name' => 'Horsens',
                'code' => 'DK-83-HOR',
                'latitude' => 55.8667,
                'longitude' => 9.8500,
                'population' => 59000,
                'postal_codes' => ['8700'],
                'translations' => [
                    'lt' => ['name' => 'Horsensas', 'description' => 'Universitetų miestas'],
                    'en' => ['name' => 'Horsens', 'description' => 'University city']
            ],
            ],
            [
                'name' => 'Fredericia',
                'code' => 'DK-83-FRE',
                'latitude' => 55.5667,
                'longitude' => 9.7500,
                'population' => 41000,
                'postal_codes' => ['7000'],
                'translations' => [
                    'lt' => ['name' => 'Fredericia', 'description' => 'Uostamiesčis'],
                    'en' => ['name' => 'Fredericia', 'description' => 'Port city']
            ],
            ],
            [
                'name' => 'Esbjerg',
                'code' => 'DK-83-ESB',
                'latitude' => 55.4667,
                'longitude' => 8.4500,
                'population' => 72000,
                'postal_codes' => ['6700'],
                'translations' => [
                    'lt' => ['name' => 'Esbjergas', 'description' => 'Vakarų pajūrio miestas'],
                    'en' => ['name' => 'Esbjerg', 'description' => 'West coast city']
            ],
            ],
            // Additional Zealand cities
            [
                'name' => 'Næstved',
                'code' => 'DK-85-NAE',
                'latitude' => 55.2333,
                'longitude' => 11.7667,
                'population' => 43000,
                'postal_codes' => ['4700'],
                'translations' => [
                    'lt' => ['name' => 'Næstvedas', 'description' => 'Pramonės miestas'],
                    'en' => ['name' => 'Næstved', 'description' => 'Industrial city']
            ],
            ],
            [
                'name' => 'Køge',
                'code' => 'DK-85-KOE',
                'latitude' => 55.4500,
                'longitude' => 12.1833,
                'population' => 38000,
                'postal_codes' => ['4600'],
                'translations' => [
                    'lt' => ['name' => 'Køgė', 'description' => 'Istorinis miestas'],
                    'en' => ['name' => 'Køge', 'description' => 'Historic city']
            ],
            ],
            [
                'name' => 'Slagelse',
                'code' => 'DK-85-SLA',
                'latitude' => 55.4000,
                'longitude' => 11.3500,
                'population' => 33000,
                'postal_codes' => ['4200'],
                'translations' => [
                    'lt' => ['name' => 'Slagelsė', 'description' => 'Trelleborg pilies miestas'],
                    'en' => ['name' => 'Slagelse', 'description' => 'Trelleborg Castle city']
            ],
            ],
            [
                'name' => 'Holbæk',
                'code' => 'DK-85-HOL',
                'latitude' => 55.7167,
                'longitude' => 11.7167,
                'population' => 28000,
                'postal_codes' => ['4300'],
                'translations' => [
                    'lt' => ['name' => 'Holbækas', 'description' => 'Uostamiesčis'],
                    'en' => ['name' => 'Holbæk', 'description' => 'Port city']
            ],
            ],
            // Additional North Jutland cities
            [
                'name' => 'Hjørring',
                'code' => 'DK-81-HJO',
                'latitude' => 57.4667,
                'longitude' => 9.9833,
                'population' => 25000,
                'postal_codes' => ['9800'],
                'translations' => [
                    'lt' => ['name' => 'Hjørringas', 'description' => 'Šiaurės miestas'],
                    'en' => ['name' => 'Hjørring', 'description' => 'Northern city']
            ],
            ],
            [
                'name' => 'Frederikshavn',
                'code' => 'DK-81-FRE',
                'latitude' => 57.4333,
                'longitude' => 10.5333,
                'population' => 23000,
                'postal_codes' => ['9900'],
                'translations' => [
                    'lt' => ['name' => 'Frederikshavnas', 'description' => 'Uostamiesčis'],
                    'en' => ['name' => 'Frederikshavn', 'description' => 'Port city']
            ],
            ],
            [
                'name' => 'Brønderslev',
                'code' => 'DK-81-BRO',
                'latitude' => 57.2667,
                'longitude' => 9.9500,
                'population' => 12000,
                'postal_codes' => ['9700'],
                'translations' => [
                    'lt' => ['name' => 'Brønderslevas', 'description' => 'Žemės ūkio miestas'],
                    'en' => ['name' => 'Brønderslev', 'description' => 'Agricultural city']
            ],
            ],
            [
                'name' => 'Thisted',
                'code' => 'DK-81-THI',
                'latitude' => 56.9500,
                'longitude' => 8.7000,
                'population' => 13000,
                'postal_codes' => ['7700'],
                'translations' => [
                    'lt' => ['name' => 'Thistedas', 'description' => 'Pajūrio miestas'],
                    'en' => ['name' => 'Thisted', 'description' => 'Coastal city']
            ],
            ],
            [
                'name' => 'Læsø',
                'code' => 'DK-81-LAE',
                'latitude' => 57.2667,
                'longitude' => 11.0000,
                'population' => 1800,
                'postal_codes' => ['9940'],
                'translations' => [
                    'lt' => ['name' => 'Læsø', 'description' => 'Salos miestas'],
                    'en' => ['name' => 'Læsø', 'description' => 'Island city']
            ],
            ],
        ];

        foreach ($cities as $cityData) {
            $city = City::updateOrCreate(
                ['code' => $cityData['code']],
                [
                    'name' => $cityData['name'],
                    'slug' => \Str::slug($cityData['name'] . '-' . $cityData['code']),
                    'is_enabled' => true,
                    'is_default' => $cityData['is_default'] ?? false,
                    'is_capital' => $cityData['is_capital'] ?? false,
                    'country_id' => $denmark->id,
                    'zone_id' => $euZone?->id,
                    'level' => 1,
                    'latitude' => $cityData['latitude'],
                    'longitude' => $cityData['longitude'],
                    'population' => $cityData['population'],
                    'postal_codes' => $cityData['postal_codes'],
                    'sort_order' => 0,
                ]
            );

            // Create translations
            foreach ($cityData['translations'] as $locale => $translation) {
                CityTranslation::updateOrCreate(
                    [
                        'city_id' => $city->id,
                        'locale' => $locale
            ],
                    [
                        'name' => $translation['name'],
                        'description' => $translation['description'],
                    ]
                );
            }
        }
    }
}
