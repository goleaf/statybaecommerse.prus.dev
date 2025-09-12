<?php declare(strict_types=1);

namespace Database\Seeders\Cities;

use App\Models\Translations\CityTranslation;
use App\Models\City;
use App\Models\Country;
use App\Models\Region;
use App\Models\Zone;
use Illuminate\Database\Seeder;

final class NetherlandsCitiesSeeder extends Seeder
{
    public function run(): void
    {
        $netherlands = Country::where('cca2', 'NL')->first();
        $euZone = Zone::where('code', 'EU')->first();

        // Get regions (provinces)
        $northHollandRegion = Region::where('code', 'NL-NH')->first();
        $southHollandRegion = Region::where('code', 'NL-ZH')->first();
        $utrechtRegion = Region::where('code', 'NL-UT')->first();
        $northBrabantRegion = Region::where('code', 'NL-NB')->first();
        $gelderlandRegion = Region::where('code', 'NL-GE')->first();
        $overijsselRegion = Region::where('code', 'NL-OV')->first();
        $limburgRegion = Region::where('code', 'NL-LI')->first();
        $frieslandRegion = Region::where('code', 'NL-FR')->first();
        $groningenRegion = Region::where('code', 'NL-GR')->first();
        $drentheRegion = Region::where('code', 'NL-DR')->first();
        $flevolandRegion = Region::where('code', 'NL-FL')->first();
        $zeelandRegion = Region::where('code', 'NL-ZE')->first();

        $cities = [
            // North Holland
            [
                'name' => 'Amsterdam',
                'code' => 'NL-NH-AMS',
                'is_capital' => true,
                'is_default' => true,
                'region_id' => $northHollandRegion?->id,
                'latitude' => 52.3676,
                'longitude' => 4.9041,
                'population' => 873555,
                'postal_codes' => ['1000', '1001', '1002'],
                'translations' => [
                    'lt' => ['name' => 'Amsterdamas', 'description' => 'Nyderlandų sostinė'],
                    'en' => ['name' => 'Amsterdam', 'description' => 'Capital of Netherlands'],
                ],
            ],
            [
                'name' => 'Haarlem',
                'code' => 'NL-NH-HAA',
                'region_id' => $northHollandRegion?->id,
                'latitude' => 52.3792,
                'longitude' => 4.6407,
                'population' => 162864,
                'postal_codes' => ['2000'],
                'translations' => [
                    'lt' => ['name' => 'Harlemas', 'description' => 'Šiaurės Olandijos sostinė'],
                    'en' => ['name' => 'Haarlem', 'description' => 'Capital of North Holland'],
                ],
            ],
            [
                'name' => 'Zaanstad',
                'code' => 'NL-NH-ZAA',
                'region_id' => $northHollandRegion?->id,
                'latitude' => 52.4531,
                'longitude' => 4.8131,
                'population' => 156901,
                'postal_codes' => ['1500'],
                'translations' => [
                    'lt' => ['name' => 'Zaanstadas', 'description' => 'Pramonės miestas'],
                    'en' => ['name' => 'Zaanstad', 'description' => 'Industrial city'],
                ],
            ],
            [
                'name' => 'Haarlemmermeer',
                'code' => 'NL-NH-HAM',
                'region_id' => $northHollandRegion?->id,
                'latitude' => 52.3008,
                'longitude' => 4.6908,
                'population' => 157889,
                'postal_codes' => ['2130'],
                'translations' => [
                    'lt' => ['name' => 'Harlemmermeras', 'description' => 'Oro uosto miestas'],
                    'en' => ['name' => 'Haarlemmermeer', 'description' => 'Airport city'],
                ],
            ],
            // South Holland
            [
                'name' => 'Rotterdam',
                'code' => 'NL-ZH-ROT',
                'region_id' => $southHollandRegion?->id,
                'latitude' => 51.9244,
                'longitude' => 4.4777,
                'population' => 651446,
                'postal_codes' => ['3000', '3001', '3002'],
                'translations' => [
                    'lt' => ['name' => 'Roterdamas', 'description' => 'Uostamiesčis'],
                    'en' => ['name' => 'Rotterdam', 'description' => 'Port city'],
                ],
            ],
            [
                'name' => 'The Hague',
                'code' => 'NL-ZH-HAG',
                'region_id' => $southHollandRegion?->id,
                'latitude' => 52.0705,
                'longitude' => 4.3007,
                'population' => 548320,
                'postal_codes' => ['2500'],
                'translations' => [
                    'lt' => ['name' => 'Haga', 'description' => 'Vyriausybės miestas'],
                    'en' => ['name' => 'The Hague', 'description' => 'Government city'],
                ],
            ],
            [
                'name' => 'Leiden',
                'code' => 'NL-ZH-LEI',
                'region_id' => $southHollandRegion?->id,
                'latitude' => 52.1601,
                'longitude' => 4.497,
                'population' => 125000,
                'postal_codes' => ['2300'],
                'translations' => [
                    'lt' => ['name' => 'Leidenas', 'description' => 'Universitetų miestas'],
                    'en' => ['name' => 'Leiden', 'description' => 'University city'],
                ],
            ],
            [
                'name' => 'Dordrecht',
                'code' => 'NL-ZH-DOR',
                'region_id' => $southHollandRegion?->id,
                'latitude' => 51.8133,
                'longitude' => 4.6903,
                'population' => 119115,
                'postal_codes' => ['3300'],
                'translations' => [
                    'lt' => ['name' => 'Dordrechtas', 'description' => 'Istorinis miestas'],
                    'en' => ['name' => 'Dordrecht', 'description' => 'Historic city'],
                ],
            ],
            [
                'name' => 'Delft',
                'code' => 'NL-ZH-DEL',
                'region_id' => $southHollandRegion?->id,
                'latitude' => 52.0116,
                'longitude' => 4.3571,
                'population' => 103659,
                'postal_codes' => ['2600'],
                'translations' => [
                    'lt' => ['name' => 'Delfas', 'description' => 'Technologijų miestas'],
                    'en' => ['name' => 'Delft', 'description' => 'Technology city'],
                ],
            ],
            // Utrecht
            [
                'name' => 'Utrecht',
                'code' => 'NL-UT-UTR',
                'region_id' => $utrechtRegion?->id,
                'latitude' => 52.0907,
                'longitude' => 5.1214,
                'population' => 361924,
                'postal_codes' => ['3500'],
                'translations' => [
                    'lt' => ['name' => 'Utrechto', 'description' => 'Utrechto provincijos sostinė'],
                    'en' => ['name' => 'Utrecht', 'description' => 'Capital of Utrecht province'],
                ],
            ],
            [
                'name' => 'Amersfoort',
                'code' => 'NL-UT-AME',
                'region_id' => $utrechtRegion?->id,
                'latitude' => 52.1561,
                'longitude' => 5.3878,
                'population' => 158531,
                'postal_codes' => ['3800'],
                'translations' => [
                    'lt' => ['name' => 'Amersfortas', 'description' => 'Istorinis miestas'],
                    'en' => ['name' => 'Amersfoort', 'description' => 'Historic city'],
                ],
            ],
            // North Brabant
            [
                'name' => 'Eindhoven',
                'code' => 'NL-NB-EIN',
                'region_id' => $northBrabantRegion?->id,
                'latitude' => 51.4416,
                'longitude' => 5.4697,
                'population' => 238326,
                'postal_codes' => ['5600'],
                'translations' => [
                    'lt' => ['name' => 'Eindhovenas', 'description' => 'Technologijų centras'],
                    'en' => ['name' => 'Eindhoven', 'description' => 'Technology center'],
                ],
            ],
            [
                'name' => 'Tilburg',
                'code' => 'NL-NB-TIL',
                'region_id' => $northBrabantRegion?->id,
                'latitude' => 51.5555,
                'longitude' => 5.0913,
                'population' => 221947,
                'postal_codes' => ['5000'],
                'translations' => [
                    'lt' => ['name' => 'Tilburgas', 'description' => 'Universitetų miestas'],
                    'en' => ['name' => 'Tilburg', 'description' => 'University city'],
                ],
            ],
            [
                'name' => 'Breda',
                'code' => 'NL-NB-BRE',
                'region_id' => $northBrabantRegion?->id,
                'latitude' => 51.5719,
                'longitude' => 4.7683,
                'population' => 184126,
                'postal_codes' => ['4800'],
                'translations' => [
                    'lt' => ['name' => 'Breda', 'description' => 'Istorinis miestas'],
                    'en' => ['name' => 'Breda', 'description' => 'Historic city'],
                ],
            ],
            [
                'name' => 's-Hertogenbosch',
                'code' => 'NL-NB-SHE',
                'region_id' => $northBrabantRegion?->id,
                'latitude' => 51.6978,
                'longitude' => 5.3036,
                'population' => 154205,
                'postal_codes' => ['5200'],
                'translations' => [
                    'lt' => ['name' => 's-Hertogenboschas', 'description' => 'Šiaurės Brabanto sostinė'],
                    'en' => ['name' => 's-Hertogenbosch', 'description' => 'Capital of North Brabant'],
                ],
            ],
            // Gelderland
            [
                'name' => 'Nijmegen',
                'code' => 'NL-GE-NIJ',
                'region_id' => $gelderlandRegion?->id,
                'latitude' => 51.8426,
                'longitude' => 5.8606,
                'population' => 179073,
                'postal_codes' => ['6500'],
                'translations' => [
                    'lt' => ['name' => 'Nijmegenas', 'description' => 'Senovinis miestas'],
                    'en' => ['name' => 'Nijmegen', 'description' => 'Ancient city'],
                ],
            ],
            [
                'name' => 'Arnhem',
                'code' => 'NL-GE-ARN',
                'region_id' => $gelderlandRegion?->id,
                'latitude' => 51.9851,
                'longitude' => 5.8987,
                'population' => 164096,
                'postal_codes' => ['6800'],
                'translations' => [
                    'lt' => ['name' => 'Arnhemas', 'description' => 'Gelderlando sostinė'],
                    'en' => ['name' => 'Arnhem', 'description' => 'Capital of Gelderland'],
                ],
            ],
            [
                'name' => 'Apeldoorn',
                'code' => 'NL-GE-APE',
                'region_id' => $gelderlandRegion?->id,
                'latitude' => 52.2112,
                'longitude' => 5.9699,
                'population' => 163706,
                'postal_codes' => ['7300'],
                'translations' => [
                    'lt' => ['name' => 'Apeldoornas', 'description' => 'Karališkojo rūmo miestas'],
                    'en' => ['name' => 'Apeldoorn', 'description' => 'Royal palace city'],
                ],
            ],
            // Overijssel
            [
                'name' => 'Enschede',
                'code' => 'NL-OV-ENS',
                'region_id' => $overijsselRegion?->id,
                'latitude' => 52.2215,
                'longitude' => 6.8937,
                'population' => 159732,
                'postal_codes' => ['7500'],
                'translations' => [
                    'lt' => ['name' => 'Enschede', 'description' => 'Tekstilės pramonės centras'],
                    'en' => ['name' => 'Enschede', 'description' => 'Textile industry center'],
                ],
            ],
            [
                'name' => 'Zwolle',
                'code' => 'NL-OV-ZWO',
                'region_id' => $overijsselRegion?->id,
                'latitude' => 52.5168,
                'longitude' => 6.083,
                'population' => 130592,
                'postal_codes' => ['8000'],
                'translations' => [
                    'lt' => ['name' => 'Zvolė', 'description' => 'Overijselo sostinė'],
                    'en' => ['name' => 'Zwolle', 'description' => 'Capital of Overijssel'],
                ],
            ],
            // Limburg
            [
                'name' => 'Maastricht',
                'code' => 'NL-LI-MAA',
                'region_id' => $limburgRegion?->id,
                'latitude' => 50.8514,
                'longitude' => 5.691,
                'population' => 121565,
                'postal_codes' => ['6200'],
                'translations' => [
                    'lt' => ['name' => 'Maastrichtas', 'description' => 'Limburgo sostinė'],
                    'en' => ['name' => 'Maastricht', 'description' => 'Capital of Limburg'],
                ],
            ],
            // Friesland
            [
                'name' => 'Leeuwarden',
                'code' => 'NL-FR-LEE',
                'region_id' => $frieslandRegion?->id,
                'latitude' => 53.2012,
                'longitude' => 5.7999,
                'population' => 124481,
                'postal_codes' => ['8900'],
                'translations' => [
                    'lt' => ['name' => 'Leuwardenas', 'description' => 'Fryzų sostinė'],
                    'en' => ['name' => 'Leeuwarden', 'description' => 'Capital of Friesland'],
                ],
            ],
            // Groningen
            [
                'name' => 'Groningen',
                'code' => 'NL-GR-GRO',
                'region_id' => $groningenRegion?->id,
                'latitude' => 53.2194,
                'longitude' => 6.5665,
                'population' => 233218,
                'postal_codes' => ['9700'],
                'translations' => [
                    'lt' => ['name' => 'Groningenas', 'description' => 'Groningeno sostinė'],
                    'en' => ['name' => 'Groningen', 'description' => 'Capital of Groningen'],
                ],
            ],
            // Drenthe
            [
                'name' => 'Assen',
                'code' => 'NL-DR-ASS',
                'region_id' => $drentheRegion?->id,
                'latitude' => 52.9967,
                'longitude' => 6.5625,
                'population' => 68000,
                'postal_codes' => ['9400'],
                'translations' => [
                    'lt' => ['name' => 'Asenas', 'description' => 'Drentės sostinė'],
                    'en' => ['name' => 'Assen', 'description' => 'Capital of Drenthe'],
                ],
            ],
            // Flevoland
            [
                'name' => 'Almere',
                'code' => 'NL-FL-ALM',
                'region_id' => $flevolandRegion?->id,
                'latitude' => 52.3508,
                'longitude' => 5.2647,
                'population' => 211514,
                'postal_codes' => ['1300'],
                'translations' => [
                    'lt' => ['name' => 'Almerė', 'description' => 'Naujasis miestas'],
                    'en' => ['name' => 'Almere', 'description' => 'New city'],
                ],
            ],
            [
                'name' => 'Lelystad',
                'code' => 'NL-FL-LEL',
                'region_id' => $flevolandRegion?->id,
                'latitude' => 52.5185,
                'longitude' => 5.4714,
                'population' => 78993,
                'postal_codes' => ['8200'],
                'translations' => [
                    'lt' => ['name' => 'Lelystadas', 'description' => 'Flevolando sostinė'],
                    'en' => ['name' => 'Lelystad', 'description' => 'Capital of Flevoland'],
                ],
            ],
            // Zeeland
            [
                'name' => 'Middelburg',
                'code' => 'NL-ZE-MID',
                'region_id' => $zeelandRegion?->id,
                'latitude' => 51.4989,
                'longitude' => 3.61,
                'population' => 48000,
                'postal_codes' => ['4330'],
                'translations' => [
                    'lt' => ['name' => 'Midelburgas', 'description' => 'Zelandos sostinė'],
                    'en' => ['name' => 'Middelburg', 'description' => 'Capital of Zeeland'],
                ],
            ],
        ];

        foreach ($cities as $cityData) {
            $city = City::updateOrCreate(
                ['code' => $cityData['code']],
                [
                    'name' => $cityData['name'],
                    'slug' => \Str::slug($cityData['name']),
                    'is_enabled' => true,
                    'is_default' => $cityData['is_default'] ?? false,
                    'is_capital' => $cityData['is_capital'] ?? false,
                    'country_id' => $netherlands->id,
                    'zone_id' => $euZone?->id,
                    'region_id' => $cityData['region_id'],
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
                        'locale' => $locale,
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
