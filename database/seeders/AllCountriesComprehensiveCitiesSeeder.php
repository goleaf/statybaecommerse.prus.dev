<?php declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Translations\CityTranslation;
use App\Models\City;
use App\Models\Country;
use Illuminate\Database\Seeder;

final class AllCountriesComprehensiveCitiesSeeder extends Seeder
{
    public function run(): void
    {
        // Get all countries
        $countries = Country::with('translations')->get();

        $totalCities = 0;

        foreach ($countries as $country) {
            $cities = $this->getCitiesForCountry($country->cca2);

            if (empty($cities)) {
                continue;  // Skip countries with no cities defined
            }

            foreach ($cities as $cityData) {
                // Remove description from cityData as it's an array
                $cityDataForInsert = $cityData;
                unset($cityDataForInsert['description']);

                $city = City::updateOrCreate(
                    ['slug' => $cityData['slug']],
                    array_merge($cityDataForInsert, [
                        'country_id' => $country->id,
                        'name' => $cityData['name']['en'],
                        'code' => $cityData['code'],
                        'is_enabled' => true,
                        'is_default' => false,
                    ])
                );

                // Create translations
                foreach (['lt', 'en'] as $locale) {
                    CityTranslation::updateOrCreate([
                        'city_id' => $city->id,
                        'locale' => $locale,
                    ], [
                        'name' => $cityData['name'][$locale] ?? $cityData['name']['en'],
                        'description' => $cityData['description'][$locale] ?? '',
                    ]);
                }

                $totalCities++;
            }
        }
    }

    private function getCitiesForCountry(string $countryCode): array
    {
        $cities = [
            'LT' => [
                ['code' => 'LT-VLN', 'slug' => 'vilnius', 'name' => ['lt' => 'Vilnius', 'en' => 'Vilnius'], 'description' => ['lt' => 'Lietuvos sostinė', 'en' => 'Capital of Lithuania'], 'is_capital' => true, 'latitude' => 54.6872, 'longitude' => 25.2797, 'population' => 588412],
                ['code' => 'LT-KAU', 'slug' => 'kaunas', 'name' => ['lt' => 'Kaunas', 'en' => 'Kaunas'], 'description' => ['lt' => 'Antras pagal dydį Lietuvos miestas', 'en' => 'Second largest city in Lithuania'], 'is_capital' => false, 'latitude' => 54.8985, 'longitude' => 23.9036, 'population' => 315993],
                ['code' => 'LT-KLP', 'slug' => 'klaipeda', 'name' => ['lt' => 'Klaipėda', 'en' => 'Klaipėda'], 'description' => ['lt' => 'Pagrindinis Lietuvos uostamiestis', 'en' => 'Main port city of Lithuania'], 'is_capital' => false, 'latitude' => 55.7033, 'longitude' => 21.1443, 'population' => 152008],
                ['code' => 'LT-SIA', 'slug' => 'siauliai', 'name' => ['lt' => 'Šiauliai', 'en' => 'Šiauliai'], 'description' => ['lt' => 'Šiaurės Lietuvos centras', 'en' => 'Center of Northern Lithuania'], 'is_capital' => false, 'latitude' => 55.9333, 'longitude' => 23.3167, 'population' => 107086],
                ['code' => 'LT-PNV', 'slug' => 'panevezys', 'name' => ['lt' => 'Panevėžys', 'en' => 'Panevėžys'], 'description' => ['lt' => 'Aukštaitijos regiono centras', 'en' => 'Center of Aukštaitija region'], 'is_capital' => false, 'latitude' => 55.7333, 'longitude' => 24.35, 'population' => 87048],
                ['code' => 'LT-ALT', 'slug' => 'alytus', 'name' => ['lt' => 'Alytus', 'en' => 'Alytus'], 'description' => ['lt' => 'Dzūkijos regiono centras', 'en' => 'Center of Dzūkija region'], 'is_capital' => false, 'latitude' => 54.4, 'longitude' => 24.05, 'population' => 52000],
                ['code' => 'LT-MRJ', 'slug' => 'marijampole', 'name' => ['lt' => 'Marijampolė', 'en' => 'Marijampolė'], 'description' => ['lt' => 'Suvalkijos regiono centras', 'en' => 'Center of Suvalkija region'], 'is_capital' => false, 'latitude' => 54.5667, 'longitude' => 23.35, 'population' => 36000],
                ['code' => 'LT-TRG', 'slug' => 'taurage', 'name' => ['lt' => 'Tauragė', 'en' => 'Tauragė'], 'description' => ['lt' => 'Žemaitijos regiono miestas', 'en' => 'City in Žemaitija region'], 'is_capital' => false, 'latitude' => 55.25, 'longitude' => 22.2833, 'population' => 22000],
                ['code' => 'LT-TLS', 'slug' => 'telsiai', 'name' => ['lt' => 'Telšiai', 'en' => 'Telšiai'], 'description' => ['lt' => 'Žemaitijos regiono centras', 'en' => 'Center of Žemaitija region'], 'is_capital' => false, 'latitude' => 55.9833, 'longitude' => 22.25, 'population' => 25000],
                ['code' => 'LT-UTN', 'slug' => 'utena', 'name' => ['lt' => 'Utena', 'en' => 'Utena'], 'description' => ['lt' => 'Aukštaitijos regiono miestas', 'en' => 'City in Aukštaitija region'], 'is_capital' => false, 'latitude' => 55.5, 'longitude' => 25.6, 'population' => 28000],
            ],
            'LV' => [
                ['code' => 'LV-RIG', 'slug' => 'riga', 'name' => ['lt' => 'Ryga', 'en' => 'Riga'], 'description' => ['lt' => 'Latvijos sostinė', 'en' => 'Capital of Latvia'], 'is_capital' => true, 'latitude' => 56.9496, 'longitude' => 24.1052, 'population' => 605802],
                ['code' => 'LV-DGP', 'slug' => 'daugavpils', 'name' => ['lt' => 'Daugpilis', 'en' => 'Daugavpils'], 'description' => ['lt' => 'Antras pagal dydį Latvijos miestas', 'en' => 'Second largest city in Latvia'], 'is_capital' => false, 'latitude' => 55.8756, 'longitude' => 26.5364, 'population' => 82046],
                ['code' => 'LV-LIE', 'slug' => 'liepaja', 'name' => ['lt' => 'Liepoja', 'en' => 'Liepāja'], 'description' => ['lt' => 'Uostamiestis Baltijos jūroje', 'en' => 'Port city on the Baltic Sea'], 'is_capital' => false, 'latitude' => 56.5085, 'longitude' => 21.0132, 'population' => 68232],
                ['code' => 'LV-JEL', 'slug' => 'jelgava', 'name' => ['lt' => 'Jelgava', 'en' => 'Jelgava'], 'description' => ['lt' => 'Zemgalės miestas', 'en' => 'City in Zemgale'], 'is_capital' => false, 'latitude' => 56.6489, 'longitude' => 23.7139, 'population' => 55668],
                ['code' => 'LV-JUR', 'slug' => 'jurmala', 'name' => ['lt' => 'Jūrmala', 'en' => 'Jūrmala'], 'description' => ['lt' => 'Kurortinis miestas', 'en' => 'Resort city'], 'is_capital' => false, 'latitude' => 56.9677, 'longitude' => 23.7703, 'population' => 50974],
            ],
            'EE' => [
                ['code' => 'EE-TLL', 'slug' => 'tallinn', 'name' => ['lt' => 'Talinas', 'en' => 'Tallinn'], 'description' => ['lt' => 'Estijos sostinė', 'en' => 'Capital of Estonia'], 'is_capital' => true, 'latitude' => 59.437, 'longitude' => 24.7536, 'population' => 437619],
                ['code' => 'EE-TRT', 'slug' => 'tartu', 'name' => ['lt' => 'Tartu', 'en' => 'Tartu'], 'description' => ['lt' => 'Estijos universiteto miestas', 'en' => 'University city of Estonia'], 'is_capital' => false, 'latitude' => 58.378, 'longitude' => 26.729, 'population' => 91407],
                ['code' => 'EE-NRV', 'slug' => 'narva', 'name' => ['lt' => 'Narva', 'en' => 'Narva'], 'description' => ['lt' => 'Rusijos sienos miestas', 'en' => 'City on Russian border'], 'is_capital' => false, 'latitude' => 59.3753, 'longitude' => 28.1903, 'population' => 53526],
                ['code' => 'EE-PAR', 'slug' => 'parnu', 'name' => ['lt' => 'Pärnu', 'en' => 'Pärnu'], 'description' => ['lt' => 'Vasaros sostinė', 'en' => 'Summer capital'], 'is_capital' => false, 'latitude' => 58.3859, 'longitude' => 24.4971, 'population' => 39728],
                ['code' => 'EE-VOR', 'slug' => 'viljandi', 'name' => ['lt' => 'Viljandis', 'en' => 'Viljandi'], 'description' => ['lt' => 'Kultūros miestas', 'en' => 'Cultural city'], 'is_capital' => false, 'latitude' => 58.3639, 'longitude' => 25.59, 'population' => 17407],
            ],
            'PL' => [
                ['code' => 'PL-WAW', 'slug' => 'warsaw', 'name' => ['lt' => 'Varšuva', 'en' => 'Warsaw'], 'description' => ['lt' => 'Lenkijos sostinė', 'en' => 'Capital of Poland'], 'is_capital' => true, 'latitude' => 52.2297, 'longitude' => 21.0122, 'population' => 1793579],
                ['code' => 'PL-KRK', 'slug' => 'krakow', 'name' => ['lt' => 'Krokuva', 'en' => 'Krakow'], 'description' => ['lt' => 'Istorinis Lenkijos miestas', 'en' => 'Historic city of Poland'], 'is_capital' => false, 'latitude' => 50.0755, 'longitude' => 19.9445, 'population' => 779115],
                ['code' => 'PL-WRO', 'slug' => 'wroclaw', 'name' => ['lt' => 'Vroclavas', 'en' => 'Wroclaw'], 'description' => ['lt' => 'Silezijos sostinė', 'en' => 'Capital of Silesia'], 'is_capital' => false, 'latitude' => 51.1079, 'longitude' => 17.0385, 'population' => 641607],
                ['code' => 'PL-GDA', 'slug' => 'gdansk', 'name' => ['lt' => 'Gdanskas', 'en' => 'Gdansk'], 'description' => ['lt' => 'Baltijos uostamiestis', 'en' => 'Baltic port city'], 'is_capital' => false, 'latitude' => 54.352, 'longitude' => 18.6466, 'population' => 470907],
                ['code' => 'PL-POZ', 'slug' => 'poznan', 'name' => ['lt' => 'Poznanė', 'en' => 'Poznan'], 'description' => ['lt' => 'Didžiosios Lenkijos centras', 'en' => 'Center of Greater Poland'], 'is_capital' => false, 'latitude' => 52.4064, 'longitude' => 16.9252, 'population' => 533830],
            ],
            'DE' => [
                ['code' => 'DE-BER', 'slug' => 'berlin', 'name' => ['lt' => 'Berlynas', 'en' => 'Berlin'], 'description' => ['lt' => 'Vokietijos sostinė', 'en' => 'Capital of Germany'], 'is_capital' => true, 'latitude' => 52.52, 'longitude' => 13.405, 'population' => 3669491],
                ['code' => 'DE-MUC', 'slug' => 'munich', 'name' => ['lt' => 'Miunchenas', 'en' => 'Munich'], 'description' => ['lt' => 'Bavarijos sostinė', 'en' => 'Capital of Bavaria'], 'is_capital' => false, 'latitude' => 48.1351, 'longitude' => 11.582, 'population' => 1484226],
                ['code' => 'DE-HAM', 'slug' => 'hamburg', 'name' => ['lt' => 'Hamburgas', 'en' => 'Hamburg'], 'description' => ['lt' => 'Uostamiestis', 'en' => 'Port city'], 'is_capital' => false, 'latitude' => 53.5511, 'longitude' => 9.9937, 'population' => 1841179],
                ['code' => 'DE-CGN', 'slug' => 'cologne', 'name' => ['lt' => 'Kelnas', 'en' => 'Cologne'], 'description' => ['lt' => 'Rėno miestas', 'en' => 'City on the Rhine'], 'is_capital' => false, 'latitude' => 50.9375, 'longitude' => 6.9603, 'population' => 1085664],
                ['code' => 'DE-FRA', 'slug' => 'frankfurt', 'name' => ['lt' => 'Frankfurtas', 'en' => 'Frankfurt'], 'description' => ['lt' => 'Finansų centras', 'en' => 'Financial center'], 'is_capital' => false, 'latitude' => 50.1109, 'longitude' => 8.6821, 'population' => 753056],
            ],
            'FR' => [
                ['code' => 'FR-PAR', 'slug' => 'paris', 'name' => ['lt' => 'Paryžius', 'en' => 'Paris'], 'description' => ['lt' => 'Prancūzijos sostinė', 'en' => 'Capital of France'], 'is_capital' => true, 'latitude' => 48.8566, 'longitude' => 2.3522, 'population' => 2161000],
                ['code' => 'FR-MRS', 'slug' => 'marseille', 'name' => ['lt' => 'Marselis', 'en' => 'Marseille'], 'description' => ['lt' => 'Antras pagal dydį Prancūzijos miestas', 'en' => 'Second largest city in France'], 'is_capital' => false, 'latitude' => 43.2965, 'longitude' => 5.3698, 'population' => 870018],
                ['code' => 'FR-LYS', 'slug' => 'lyon', 'name' => ['lt' => 'Lionas', 'en' => 'Lyon'], 'description' => ['lt' => 'Gastronomijos sostinė', 'en' => 'Gastronomy capital'], 'is_capital' => false, 'latitude' => 45.764, 'longitude' => 4.8357, 'population' => 515695],
                ['code' => 'FR-TLS', 'slug' => 'toulouse', 'name' => ['lt' => 'Tuluza', 'en' => 'Toulouse'], 'description' => ['lt' => 'Aviacijos miestas', 'en' => 'Aviation city'], 'is_capital' => false, 'latitude' => 43.6047, 'longitude' => 1.4442, 'population' => 479553],
                ['code' => 'FR-NCE', 'slug' => 'nice', 'name' => ['lt' => 'Nicėja', 'en' => 'Nice'], 'description' => ['lt' => 'Kurortinis miestas', 'en' => 'Resort city'], 'is_capital' => false, 'latitude' => 43.7102, 'longitude' => 7.262, 'population' => 342637],
            ],
            'ES' => [
                ['code' => 'ES-MAD', 'slug' => 'madrid', 'name' => ['lt' => 'Madridas', 'en' => 'Madrid'], 'description' => ['lt' => 'Ispanijos sostinė', 'en' => 'Capital of Spain'], 'is_capital' => true, 'latitude' => 40.4168, 'longitude' => -3.7038, 'population' => 3223334],
                ['code' => 'ES-BCN', 'slug' => 'barcelona', 'name' => ['lt' => 'Barselona', 'en' => 'Barcelona'], 'description' => ['lt' => 'Katalonijos sostinė', 'en' => 'Capital of Catalonia'], 'is_capital' => false, 'latitude' => 41.3851, 'longitude' => 2.1734, 'population' => 1636762],
                ['code' => 'ES-VLC', 'slug' => 'valencia', 'name' => ['lt' => 'Valensija', 'en' => 'Valencia'], 'description' => ['lt' => 'Valensijos regiono sostinė', 'en' => 'Capital of Valencia region'], 'is_capital' => false, 'latitude' => 39.4699, 'longitude' => -0.3763, 'population' => 789744],
                ['code' => 'ES-SEV', 'slug' => 'seville', 'name' => ['lt' => 'Sevilija', 'en' => 'Seville'], 'description' => ['lt' => 'Andalūzijos sostinė', 'en' => 'Capital of Andalusia'], 'is_capital' => false, 'latitude' => 37.3891, 'longitude' => -5.9845, 'population' => 688711],
                ['code' => 'ES-BIO', 'slug' => 'bilbao', 'name' => ['lt' => 'Bilbao', 'en' => 'Bilbao'], 'description' => ['lt' => 'Baskų šalies sostinė', 'en' => 'Capital of Basque Country'], 'is_capital' => false, 'latitude' => 43.2627, 'longitude' => -2.9253, 'population' => 345141],
            ],
            'IT' => [
                ['code' => 'IT-ROM', 'slug' => 'rome', 'name' => ['lt' => 'Roma', 'en' => 'Rome'], 'description' => ['lt' => 'Italijos sostinė', 'en' => 'Capital of Italy'], 'is_capital' => true, 'latitude' => 41.9028, 'longitude' => 12.4964, 'population' => 2872800],
                ['code' => 'IT-MIL', 'slug' => 'milan', 'name' => ['lt' => 'Milanas', 'en' => 'Milan'], 'description' => ['lt' => 'Lombardijos sostinė', 'en' => 'Capital of Lombardy'], 'is_capital' => false, 'latitude' => 45.4642, 'longitude' => 9.19, 'population' => 1371498],
                ['code' => 'IT-NAP', 'slug' => 'naples', 'name' => ['lt' => 'Neapolis', 'en' => 'Naples'], 'description' => ['lt' => 'Kampanijos sostinė', 'en' => 'Capital of Campania'], 'is_capital' => false, 'latitude' => 40.8518, 'longitude' => 14.2681, 'population' => 914758],
                ['code' => 'IT-TUR', 'slug' => 'turin', 'name' => ['lt' => 'Torinas', 'en' => 'Turin'], 'description' => ['lt' => 'Piemonto sostinė', 'en' => 'Capital of Piedmont'], 'is_capital' => false, 'latitude' => 45.0703, 'longitude' => 7.6869, 'population' => 848196],
                ['code' => 'IT-FLR', 'slug' => 'florence', 'name' => ['lt' => 'Florencija', 'en' => 'Florence'], 'description' => ['lt' => 'Toskanos sostinė', 'en' => 'Capital of Tuscany'], 'is_capital' => false, 'latitude' => 43.7696, 'longitude' => 11.2558, 'population' => 380948],
            ],
            'US' => [
                ['code' => 'US-NYC', 'slug' => 'new-york', 'name' => ['lt' => 'Niujorkas', 'en' => 'New York'], 'description' => ['lt' => 'Didžiausias JAV miestas', 'en' => 'Largest city in USA'], 'is_capital' => false, 'latitude' => 40.7128, 'longitude' => -74.006, 'population' => 8336817],
                ['code' => 'US-LAX', 'slug' => 'los-angeles', 'name' => ['lt' => 'Los Andželas', 'en' => 'Los Angeles'], 'description' => ['lt' => 'Kalifornijos didžiausias miestas', 'en' => 'Largest city in California'], 'is_capital' => false, 'latitude' => 34.0522, 'longitude' => -118.2437, 'population' => 3971883],
                ['code' => 'US-CHI', 'slug' => 'chicago', 'name' => ['lt' => 'Čikaga', 'en' => 'Chicago'], 'description' => ['lt' => 'Ilinojaus didžiausias miestas', 'en' => 'Largest city in Illinois'], 'is_capital' => false, 'latitude' => 41.8781, 'longitude' => -87.6298, 'population' => 2693976],
                ['code' => 'US-HOU', 'slug' => 'houston', 'name' => ['lt' => 'Hjustonas', 'en' => 'Houston'], 'description' => ['lt' => 'Teksaso didžiausias miestas', 'en' => 'Largest city in Texas'], 'is_capital' => false, 'latitude' => 29.7604, 'longitude' => -95.3698, 'population' => 2320268],
                ['code' => 'US-PHX', 'slug' => 'phoenix', 'name' => ['lt' => 'Finiksas', 'en' => 'Phoenix'], 'description' => ['lt' => 'Arizonos sostinė', 'en' => 'Capital of Arizona'], 'is_capital' => false, 'latitude' => 33.4484, 'longitude' => -112.074, 'population' => 1608139],
            ],
            'GB' => [
                ['code' => 'GB-LON', 'slug' => 'london', 'name' => ['lt' => 'Londonas', 'en' => 'London'], 'description' => ['lt' => 'Jungtinės Karalystės sostinė', 'en' => 'Capital of United Kingdom'], 'is_capital' => true, 'latitude' => 51.5074, 'longitude' => -0.1278, 'population' => 8982000],
                ['code' => 'GB-EDI', 'slug' => 'edinburgh', 'name' => ['lt' => 'Edinburgas', 'en' => 'Edinburgh'], 'description' => ['lt' => 'Škotijos sostinė', 'en' => 'Capital of Scotland'], 'is_capital' => false, 'latitude' => 55.9533, 'longitude' => -3.1883, 'population' => 548560],
                ['code' => 'GB-MAN', 'slug' => 'manchester', 'name' => ['lt' => 'Mančesteris', 'en' => 'Manchester'], 'description' => ['lt' => 'Pramonės miestas', 'en' => 'Industrial city'], 'is_capital' => false, 'latitude' => 53.4808, 'longitude' => -2.2426, 'population' => 547627],
                ['code' => 'GB-BIR', 'slug' => 'birmingham', 'name' => ['lt' => 'Birmingemas', 'en' => 'Birmingham'], 'description' => ['lt' => 'Antras pagal dydį JK miestas', 'en' => 'Second largest UK city'], 'is_capital' => false, 'latitude' => 52.4862, 'longitude' => -1.8904, 'population' => 1141816],
                ['code' => 'GB-LIV', 'slug' => 'liverpool', 'name' => ['lt' => 'Liverpulis', 'en' => 'Liverpool'], 'description' => ['lt' => 'Uostamiestis', 'en' => 'Port city'], 'is_capital' => false, 'latitude' => 53.4084, 'longitude' => -2.9916, 'population' => 498042],
            ],
            'CA' => [
                ['code' => 'CA-TOR', 'slug' => 'toronto', 'name' => ['lt' => 'Torontas', 'en' => 'Toronto'], 'description' => ['lt' => 'Kanados didžiausias miestas', 'en' => 'Largest city in Canada'], 'is_capital' => false, 'latitude' => 43.6532, 'longitude' => -79.3832, 'population' => 2930000],
                ['code' => 'CA-MTL', 'slug' => 'montreal', 'name' => ['lt' => 'Montrealis', 'en' => 'Montreal'], 'description' => ['lt' => 'Kvebeko didžiausias miestas', 'en' => 'Largest city in Quebec'], 'is_capital' => false, 'latitude' => 45.5017, 'longitude' => -73.5673, 'population' => 1780000],
                ['code' => 'CA-VAN', 'slug' => 'vancouver', 'name' => ['lt' => 'Vankuveris', 'en' => 'Vancouver'], 'description' => ['lt' => 'Britų Kolumbijos didžiausias miestas', 'en' => 'Largest city in British Columbia'], 'is_capital' => false, 'latitude' => 49.2827, 'longitude' => -123.1207, 'population' => 675218],
                ['code' => 'CA-CAL', 'slug' => 'calgary', 'name' => ['lt' => 'Kalgaris', 'en' => 'Calgary'], 'description' => ['lt' => 'Alberta sostinė', 'en' => 'Capital of Alberta'], 'is_capital' => false, 'latitude' => 51.0447, 'longitude' => -114.0719, 'population' => 1306784],
                ['code' => 'CA-OTT', 'slug' => 'ottawa', 'name' => ['lt' => 'Otaва', 'en' => 'Ottawa'], 'description' => ['lt' => 'Kanados sostinė', 'en' => 'Capital of Canada'], 'is_capital' => true, 'latitude' => 45.4215, 'longitude' => -75.6972, 'population' => 1017449],
            ],
        ];

        return $cities[$countryCode] ?? [];
    }
}
