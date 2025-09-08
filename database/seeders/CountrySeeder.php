<?php declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Translations\CountryTranslation;
use App\Models\Country;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class CountrySeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $countries = $this->getCountriesData();

            foreach ($countries as $countryData) {
                $country = Country::updateOrCreate(
                    ['cca2' => $countryData['cca2']],
                    [
                        'cca3' => $countryData['cca3'],
                        'phone_calling_code' => $countryData['phone_calling_code'],
                        'flag' => $countryData['flag'],
                        'region' => $countryData['region'],
                        'subregion' => $countryData['subregion'],
                        'latitude' => $countryData['latitude'],
                        'longitude' => $countryData['longitude'],
                        'currencies' => $countryData['currencies'],
                    ]
                );

                // Create translations for Lithuanian and English
                foreach ($countryData['translations'] as $locale => $translation) {
                    CountryTranslation::updateOrCreate(
                        [
                            'country_id' => $country->id,
                            'locale' => $locale,
                        ],
                        [
                            'name' => $translation['name'],
                            'name_official' => $translation['name_official'],
                        ]
                    );
                }
            }
        });
    }

    private function getCountriesData(): array
    {
        return [
            [
                'cca2' => 'LT',
                'cca3' => 'LTU',
                'phone_calling_code' => '370',
                'flag' => '🇱🇹',
                'region' => 'Europe',
                'subregion' => 'Northern Europe',
                'latitude' => 55.169438,
                'longitude' => 23.881275,
                'currencies' => ['EUR'],
                'translations' => [
                    'lt' => [
                        'name' => 'Lietuva',
                        'name_official' => 'Lietuvos Respublika',
                    ],
                    'en' => [
                        'name' => 'Lithuania',
                        'name_official' => 'Republic of Lithuania',
                    ],
                ],
            ],
            [
                'cca2' => 'LV',
                'cca3' => 'LVA',
                'phone_calling_code' => '371',
                'flag' => '🇱🇻',
                'region' => 'Europe',
                'subregion' => 'Northern Europe',
                'latitude' => 56.879635,
                'longitude' => 24.603189,
                'currencies' => ['EUR'],
                'translations' => [
                    'lt' => [
                        'name' => 'Latvija',
                        'name_official' => 'Latvijos Respublika',
                    ],
                    'en' => [
                        'name' => 'Latvia',
                        'name_official' => 'Republic of Latvia',
                    ],
                ],
            ],
            [
                'cca2' => 'EE',
                'cca3' => 'EST',
                'phone_calling_code' => '372',
                'flag' => '🇪🇪',
                'region' => 'Europe',
                'subregion' => 'Northern Europe',
                'latitude' => 58.595272,
                'longitude' => 25.013607,
                'currencies' => ['EUR'],
                'translations' => [
                    'lt' => [
                        'name' => 'Estija',
                        'name_official' => 'Estijos Respublika',
                    ],
                    'en' => [
                        'name' => 'Estonia',
                        'name_official' => 'Republic of Estonia',
                    ],
                ],
            ],
            [
                'cca2' => 'PL',
                'cca3' => 'POL',
                'phone_calling_code' => '48',
                'flag' => '🇵🇱',
                'region' => 'Europe',
                'subregion' => 'Central Europe',
                'latitude' => 51.919438,
                'longitude' => 19.145136,
                'currencies' => ['PLN'],
                'translations' => [
                    'lt' => [
                        'name' => 'Lenkija',
                        'name_official' => 'Lenkijos Respublika',
                    ],
                    'en' => [
                        'name' => 'Poland',
                        'name_official' => 'Republic of Poland',
                    ],
                ],
            ],
            [
                'cca2' => 'DE',
                'cca3' => 'DEU',
                'phone_calling_code' => '49',
                'flag' => '🇩🇪',
                'region' => 'Europe',
                'subregion' => 'Central Europe',
                'latitude' => 51.165691,
                'longitude' => 10.451526,
                'currencies' => ['EUR'],
                'translations' => [
                    'lt' => [
                        'name' => 'Vokietija',
                        'name_official' => 'Vokietijos Federacinė Respublika',
                    ],
                    'en' => [
                        'name' => 'Germany',
                        'name_official' => 'Federal Republic of Germany',
                    ],
                ],
            ],
            [
                'cca2' => 'FR',
                'cca3' => 'FRA',
                'phone_calling_code' => '33',
                'flag' => '🇫🇷',
                'region' => 'Europe',
                'subregion' => 'Western Europe',
                'latitude' => 46.227638,
                'longitude' => 2.213749,
                'currencies' => ['EUR'],
                'translations' => [
                    'lt' => [
                        'name' => 'Prancūzija',
                        'name_official' => 'Prancūzijos Respublika',
                    ],
                    'en' => [
                        'name' => 'France',
                        'name_official' => 'French Republic',
                    ],
                ],
            ],
            [
                'cca2' => 'GB',
                'cca3' => 'GBR',
                'phone_calling_code' => '44',
                'flag' => '🇬🇧',
                'region' => 'Europe',
                'subregion' => 'Northern Europe',
                'latitude' => 55.378051,
                'longitude' => -3.435973,
                'currencies' => ['GBP'],
                'translations' => [
                    'lt' => [
                        'name' => 'Jungtinė Karalystė',
                        'name_official' => 'Didžiosios Britanijos ir Šiaurės Airijos Jungtinė Karalystė',
                    ],
                    'en' => [
                        'name' => 'United Kingdom',
                        'name_official' => 'United Kingdom of Great Britain and Northern Ireland',
                    ],
                ],
            ],
            [
                'cca2' => 'US',
                'cca3' => 'USA',
                'phone_calling_code' => '1',
                'flag' => '🇺🇸',
                'region' => 'Americas',
                'subregion' => 'North America',
                'latitude' => 37.09024,
                'longitude' => -95.712891,
                'currencies' => ['USD'],
                'translations' => [
                    'lt' => [
                        'name' => 'Jungtinės Amerikos Valstijos',
                        'name_official' => 'Amerikos Jungtinės Valstijos',
                    ],
                    'en' => [
                        'name' => 'United States',
                        'name_official' => 'United States of America',
                    ],
                ],
            ],
            [
                'cca2' => 'CA',
                'cca3' => 'CAN',
                'phone_calling_code' => '1',
                'flag' => '🇨🇦',
                'region' => 'Americas',
                'subregion' => 'North America',
                'latitude' => 56.130366,
                'longitude' => -106.346771,
                'currencies' => ['CAD'],
                'translations' => [
                    'lt' => [
                        'name' => 'Kanada',
                        'name_official' => 'Kanada',
                    ],
                    'en' => [
                        'name' => 'Canada',
                        'name_official' => 'Canada',
                    ],
                ],
            ],
            [
                'cca2' => 'RU',
                'cca3' => 'RUS',
                'phone_calling_code' => '7',
                'flag' => '🇷🇺',
                'region' => 'Europe',
                'subregion' => 'Eastern Europe',
                'latitude' => 61.52401,
                'longitude' => 105.318756,
                'currencies' => ['RUB'],
                'translations' => [
                    'lt' => [
                        'name' => 'Rusija',
                        'name_official' => 'Rusijos Federacija',
                    ],
                    'en' => [
                        'name' => 'Russia',
                        'name_official' => 'Russian Federation',
                    ],
                ],
            ],
            [
                'cca2' => 'BY',
                'cca3' => 'BLR',
                'phone_calling_code' => '375',
                'flag' => '🇧🇾',
                'region' => 'Europe',
                'subregion' => 'Eastern Europe',
                'latitude' => 53.709807,
                'longitude' => 27.953389,
                'currencies' => ['BYN'],
                'translations' => [
                    'lt' => [
                        'name' => 'Baltarusija',
                        'name_official' => 'Baltarusijos Respublika',
                    ],
                    'en' => [
                        'name' => 'Belarus',
                        'name_official' => 'Republic of Belarus',
                    ],
                ],
            ],
            [
                'cca2' => 'UA',
                'cca3' => 'UKR',
                'phone_calling_code' => '380',
                'flag' => '🇺🇦',
                'region' => 'Europe',
                'subregion' => 'Eastern Europe',
                'latitude' => 48.379433,
                'longitude' => 31.16558,
                'currencies' => ['UAH'],
                'translations' => [
                    'lt' => [
                        'name' => 'Ukraina',
                        'name_official' => 'Ukraina',
                    ],
                    'en' => [
                        'name' => 'Ukraine',
                        'name_official' => 'Ukraine',
                    ],
                ],
            ],
            [
                'cca2' => 'SE',
                'cca3' => 'SWE',
                'phone_calling_code' => '46',
                'flag' => '🇸🇪',
                'region' => 'Europe',
                'subregion' => 'Northern Europe',
                'latitude' => 60.128161,
                'longitude' => 18.643501,
                'currencies' => ['SEK'],
                'translations' => [
                    'lt' => [
                        'name' => 'Švedija',
                        'name_official' => 'Švedijos Karalystė',
                    ],
                    'en' => [
                        'name' => 'Sweden',
                        'name_official' => 'Kingdom of Sweden',
                    ],
                ],
            ],
            [
                'cca2' => 'NO',
                'cca3' => 'NOR',
                'phone_calling_code' => '47',
                'flag' => '🇳🇴',
                'region' => 'Europe',
                'subregion' => 'Northern Europe',
                'latitude' => 60.472024,
                'longitude' => 8.468946,
                'currencies' => ['NOK'],
                'translations' => [
                    'lt' => [
                        'name' => 'Norvegija',
                        'name_official' => 'Norvegijos Karalystė',
                    ],
                    'en' => [
                        'name' => 'Norway',
                        'name_official' => 'Kingdom of Norway',
                    ],
                ],
            ],
            [
                'cca2' => 'DK',
                'cca3' => 'DNK',
                'phone_calling_code' => '45',
                'flag' => '🇩🇰',
                'region' => 'Europe',
                'subregion' => 'Northern Europe',
                'latitude' => 56.26392,
                'longitude' => 9.501785,
                'currencies' => ['DKK'],
                'translations' => [
                    'lt' => [
                        'name' => 'Danija',
                        'name_official' => 'Danijos Karalystė',
                    ],
                    'en' => [
                        'name' => 'Denmark',
                        'name_official' => 'Kingdom of Denmark',
                    ],
                ],
            ],
            [
                'cca2' => 'FI',
                'cca3' => 'FIN',
                'phone_calling_code' => '358',
                'flag' => '🇫🇮',
                'region' => 'Europe',
                'subregion' => 'Northern Europe',
                'latitude' => 61.92411,
                'longitude' => 25.748151,
                'currencies' => ['EUR'],
                'translations' => [
                    'lt' => [
                        'name' => 'Suomija',
                        'name_official' => 'Suomijos Respublika',
                    ],
                    'en' => [
                        'name' => 'Finland',
                        'name_official' => 'Republic of Finland',
                    ],
                ],
            ],
            [
                'cca2' => 'NL',
                'cca3' => 'NLD',
                'phone_calling_code' => '31',
                'flag' => '🇳🇱',
                'region' => 'Europe',
                'subregion' => 'Western Europe',
                'latitude' => 52.132633,
                'longitude' => 5.291266,
                'currencies' => ['EUR'],
                'translations' => [
                    'lt' => [
                        'name' => 'Nyderlandai',
                        'name_official' => 'Nyderlandų Karalystė',
                    ],
                    'en' => [
                        'name' => 'Netherlands',
                        'name_official' => 'Kingdom of the Netherlands',
                    ],
                ],
            ],
            [
                'cca2' => 'BE',
                'cca3' => 'BEL',
                'phone_calling_code' => '32',
                'flag' => '🇧🇪',
                'region' => 'Europe',
                'subregion' => 'Western Europe',
                'latitude' => 50.503887,
                'longitude' => 4.469936,
                'currencies' => ['EUR'],
                'translations' => [
                    'lt' => [
                        'name' => 'Belgija',
                        'name_official' => 'Belgijos Karalystė',
                    ],
                    'en' => [
                        'name' => 'Belgium',
                        'name_official' => 'Kingdom of Belgium',
                    ],
                ],
            ],
            [
                'cca2' => 'ES',
                'cca3' => 'ESP',
                'phone_calling_code' => '34',
                'flag' => '🇪🇸',
                'region' => 'Europe',
                'subregion' => 'Southern Europe',
                'latitude' => 40.463667,
                'longitude' => -3.74922,
                'currencies' => ['EUR'],
                'translations' => [
                    'lt' => [
                        'name' => 'Ispanija',
                        'name_official' => 'Ispanijos Karalystė',
                    ],
                    'en' => [
                        'name' => 'Spain',
                        'name_official' => 'Kingdom of Spain',
                    ],
                ],
            ],
            [
                'cca2' => 'IT',
                'cca3' => 'ITA',
                'phone_calling_code' => '39',
                'flag' => '🇮🇹',
                'region' => 'Europe',
                'subregion' => 'Southern Europe',
                'latitude' => 41.87194,
                'longitude' => 12.56738,
                'currencies' => ['EUR'],
                'translations' => [
                    'lt' => [
                        'name' => 'Italija',
                        'name_official' => 'Italijos Respublika',
                    ],
                    'en' => [
                        'name' => 'Italy',
                        'name_official' => 'Italian Republic',
                    ],
                ],
            ],
            [
                'cca2' => 'AT',
                'cca3' => 'AUT',
                'phone_calling_code' => '43',
                'flag' => '🇦🇹',
                'region' => 'Europe',
                'subregion' => 'Central Europe',
                'latitude' => 47.516231,
                'longitude' => 14.550072,
                'currencies' => ['EUR'],
                'translations' => [
                    'lt' => [
                        'name' => 'Austrija',
                        'name_official' => 'Austrijos Respublika',
                    ],
                    'en' => [
                        'name' => 'Austria',
                        'name_official' => 'Republic of Austria',
                    ],
                ],
            ],
            [
                'cca2' => 'CH',
                'cca3' => 'CHE',
                'phone_calling_code' => '41',
                'flag' => '🇨🇭',
                'region' => 'Europe',
                'subregion' => 'Central Europe',
                'latitude' => 46.818188,
                'longitude' => 8.227512,
                'currencies' => ['CHF'],
                'translations' => [
                    'lt' => [
                        'name' => 'Šveicarija',
                        'name_official' => 'Šveicarijos Konfederacija',
                    ],
                    'en' => [
                        'name' => 'Switzerland',
                        'name_official' => 'Swiss Confederation',
                    ],
                ],
            ],
            [
                'cca2' => 'CZ',
                'cca3' => 'CZE',
                'phone_calling_code' => '420',
                'flag' => '🇨🇿',
                'region' => 'Europe',
                'subregion' => 'Central Europe',
                'latitude' => 49.817492,
                'longitude' => 15.472962,
                'currencies' => ['CZK'],
                'translations' => [
                    'lt' => [
                        'name' => 'Čekija',
                        'name_official' => 'Čekijos Respublika',
                    ],
                    'en' => [
                        'name' => 'Czech Republic',
                        'name_official' => 'Czech Republic',
                    ],
                ],
            ],
            [
                'cca2' => 'SK',
                'cca3' => 'SVK',
                'phone_calling_code' => '421',
                'flag' => '🇸🇰',
                'region' => 'Europe',
                'subregion' => 'Central Europe',
                'latitude' => 48.669026,
                'longitude' => 19.699024,
                'currencies' => ['EUR'],
                'translations' => [
                    'lt' => [
                        'name' => 'Slovakija',
                        'name_official' => 'Slovakijos Respublika',
                    ],
                    'en' => [
                        'name' => 'Slovakia',
                        'name_official' => 'Slovak Republic',
                    ],
                ],
            ],
            [
                'cca2' => 'HU',
                'cca3' => 'HUN',
                'phone_calling_code' => '36',
                'flag' => '🇭🇺',
                'region' => 'Europe',
                'subregion' => 'Central Europe',
                'latitude' => 47.162494,
                'longitude' => 19.503304,
                'currencies' => ['HUF'],
                'translations' => [
                    'lt' => [
                        'name' => 'Vengrija',
                        'name_official' => 'Vengrija',
                    ],
                    'en' => [
                        'name' => 'Hungary',
                        'name_official' => 'Hungary',
                    ],
                ],
            ],
            [
                'cca2' => 'RO',
                'cca3' => 'ROU',
                'phone_calling_code' => '40',
                'flag' => '🇷🇴',
                'region' => 'Europe',
                'subregion' => 'Eastern Europe',
                'latitude' => 45.943161,
                'longitude' => 24.96676,
                'currencies' => ['RON'],
                'translations' => [
                    'lt' => [
                        'name' => 'Rumunija',
                        'name_official' => 'Rumunija',
                    ],
                    'en' => [
                        'name' => 'Romania',
                        'name_official' => 'Romania',
                    ],
                ],
            ],
            [
                'cca2' => 'BG',
                'cca3' => 'BGR',
                'phone_calling_code' => '359',
                'flag' => '🇧🇬',
                'region' => 'Europe',
                'subregion' => 'Eastern Europe',
                'latitude' => 42.733883,
                'longitude' => 25.48583,
                'currencies' => ['BGN'],
                'translations' => [
                    'lt' => [
                        'name' => 'Bulgarija',
                        'name_official' => 'Bulgarijos Respublika',
                    ],
                    'en' => [
                        'name' => 'Bulgaria',
                        'name_official' => 'Republic of Bulgaria',
                    ],
                ],
            ],
            [
                'cca2' => 'HR',
                'cca3' => 'HRV',
                'phone_calling_code' => '385',
                'flag' => '🇭🇷',
                'region' => 'Europe',
                'subregion' => 'Central Europe',
                'latitude' => 45.1,
                'longitude' => 15.2,
                'currencies' => ['EUR'],
                'translations' => [
                    'lt' => [
                        'name' => 'Kroatija',
                        'name_official' => 'Kroatijos Respublika',
                    ],
                    'en' => [
                        'name' => 'Croatia',
                        'name_official' => 'Republic of Croatia',
                    ],
                ],
            ],
            [
                'cca2' => 'SI',
                'cca3' => 'SVN',
                'phone_calling_code' => '386',
                'flag' => '🇸🇮',
                'region' => 'Europe',
                'subregion' => 'Central Europe',
                'latitude' => 46.151241,
                'longitude' => 14.995463,
                'currencies' => ['EUR'],
                'translations' => [
                    'lt' => [
                        'name' => 'Slovėnija',
                        'name_official' => 'Slovėnijos Respublika',
                    ],
                    'en' => [
                        'name' => 'Slovenia',
                        'name_official' => 'Republic of Slovenia',
                    ],
                ],
            ],
            [
                'cca2' => 'RS',
                'cca3' => 'SRB',
                'phone_calling_code' => '381',
                'flag' => '🇷🇸',
                'region' => 'Europe',
                'subregion' => 'Eastern Europe',
                'latitude' => 44.016521,
                'longitude' => 21.005859,
                'currencies' => ['RSD'],
                'translations' => [
                    'lt' => [
                        'name' => 'Serbija',
                        'name_official' => 'Serbijos Respublika',
                    ],
                    'en' => [
                        'name' => 'Serbia',
                        'name_official' => 'Republic of Serbia',
                    ],
                ],
            ],
            [
                'cca2' => 'JP',
                'cca3' => 'JPN',
                'phone_calling_code' => '81',
                'flag' => '🇯🇵',
                'region' => 'Asia',
                'subregion' => 'Eastern Asia',
                'latitude' => 36.204824,
                'longitude' => 138.252924,
                'currencies' => ['JPY'],
                'translations' => [
                    'lt' => [
                        'name' => 'Japonija',
                        'name_official' => 'Japonija',
                    ],
                    'en' => [
                        'name' => 'Japan',
                        'name_official' => 'Japan',
                    ],
                ],
            ],
            [
                'cca2' => 'CN',
                'cca3' => 'CHN',
                'phone_calling_code' => '86',
                'flag' => '🇨🇳',
                'region' => 'Asia',
                'subregion' => 'Eastern Asia',
                'latitude' => 35.86166,
                'longitude' => 104.195397,
                'currencies' => ['CNY'],
                'translations' => [
                    'lt' => [
                        'name' => 'Kinija',
                        'name_official' => 'Kinijos Liaudies Respublika',
                    ],
                    'en' => [
                        'name' => 'China',
                        'name_official' => "People's Republic of China",
                    ],
                ],
            ],
            [
                'cca2' => 'KR',
                'cca3' => 'KOR',
                'phone_calling_code' => '82',
                'flag' => '🇰🇷',
                'region' => 'Asia',
                'subregion' => 'Eastern Asia',
                'latitude' => 35.907757,
                'longitude' => 127.766922,
                'currencies' => ['KRW'],
                'translations' => [
                    'lt' => [
                        'name' => 'Pietų Korėja',
                        'name_official' => 'Korėjos Respublika',
                    ],
                    'en' => [
                        'name' => 'South Korea',
                        'name_official' => 'Republic of Korea',
                    ],
                ],
            ],
            [
                'cca2' => 'IN',
                'cca3' => 'IND',
                'phone_calling_code' => '91',
                'flag' => '🇮🇳',
                'region' => 'Asia',
                'subregion' => 'Southern Asia',
                'latitude' => 20.593684,
                'longitude' => 78.96288,
                'currencies' => ['INR'],
                'translations' => [
                    'lt' => [
                        'name' => 'Indija',
                        'name_official' => 'Indijos Respublika',
                    ],
                    'en' => [
                        'name' => 'India',
                        'name_official' => 'Republic of India',
                    ],
                ],
            ],
            [
                'cca2' => 'AU',
                'cca3' => 'AUS',
                'phone_calling_code' => '61',
                'flag' => '🇦🇺',
                'region' => 'Oceania',
                'subregion' => 'Australia and New Zealand',
                'latitude' => -25.274398,
                'longitude' => 133.775136,
                'currencies' => ['AUD'],
                'translations' => [
                    'lt' => [
                        'name' => 'Australija',
                        'name_official' => 'Australijos Sandraugos Valstybė',
                    ],
                    'en' => [
                        'name' => 'Australia',
                        'name_official' => 'Commonwealth of Australia',
                    ],
                ],
            ],
            [
                'cca2' => 'NZ',
                'cca3' => 'NZL',
                'phone_calling_code' => '64',
                'flag' => '🇳🇿',
                'region' => 'Oceania',
                'subregion' => 'Australia and New Zealand',
                'latitude' => -40.900557,
                'longitude' => 174.885971,
                'currencies' => ['NZD'],
                'translations' => [
                    'lt' => [
                        'name' => 'Naujoji Zelandija',
                        'name_official' => 'Naujoji Zelandija',
                    ],
                    'en' => [
                        'name' => 'New Zealand',
                        'name_official' => 'New Zealand',
                    ],
                ],
            ],
            [
                'cca2' => 'BR',
                'cca3' => 'BRA',
                'phone_calling_code' => '55',
                'flag' => '🇧🇷',
                'region' => 'Americas',
                'subregion' => 'South America',
                'latitude' => -14.235004,
                'longitude' => -51.92528,
                'currencies' => ['BRL'],
                'translations' => [
                    'lt' => [
                        'name' => 'Brazilija',
                        'name_official' => 'Brazilijos Federacinė Respublika',
                    ],
                    'en' => [
                        'name' => 'Brazil',
                        'name_official' => 'Federative Republic of Brazil',
                    ],
                ],
            ],
            [
                'cca2' => 'AR',
                'cca3' => 'ARG',
                'phone_calling_code' => '54',
                'flag' => '🇦🇷',
                'region' => 'Americas',
                'subregion' => 'South America',
                'latitude' => -38.416097,
                'longitude' => -63.616672,
                'currencies' => ['ARS'],
                'translations' => [
                    'lt' => [
                        'name' => 'Argentina',
                        'name_official' => 'Argentinos Respublika',
                    ],
                    'en' => [
                        'name' => 'Argentina',
                        'name_official' => 'Argentine Republic',
                    ],
                ],
            ],
            [
                'cca2' => 'MX',
                'cca3' => 'MEX',
                'phone_calling_code' => '52',
                'flag' => '🇲🇽',
                'region' => 'Americas',
                'subregion' => 'North America',
                'latitude' => 23.634501,
                'longitude' => -102.552784,
                'currencies' => ['MXN'],
                'translations' => [
                    'lt' => [
                        'name' => 'Meksika',
                        'name_official' => 'Meksikos Jungtinės Valstijos',
                    ],
                    'en' => [
                        'name' => 'Mexico',
                        'name_official' => 'United Mexican States',
                    ],
                ],
            ],
            [
                'cca2' => 'ZA',
                'cca3' => 'ZAF',
                'phone_calling_code' => '27',
                'flag' => '🇿🇦',
                'region' => 'Africa',
                'subregion' => 'Southern Africa',
                'latitude' => -30.559482,
                'longitude' => 22.937506,
                'currencies' => ['ZAR'],
                'translations' => [
                    'lt' => [
                        'name' => 'Pietų Afrika',
                        'name_official' => 'Pietų Afrikos Respublika',
                    ],
                    'en' => [
                        'name' => 'South Africa',
                        'name_official' => 'Republic of South Africa',
                    ],
                ],
            ],
            [
                'cca2' => 'EG',
                'cca3' => 'EGY',
                'phone_calling_code' => '20',
                'flag' => '🇪🇬',
                'region' => 'Africa',
                'subregion' => 'Northern Africa',
                'latitude' => 26.820553,
                'longitude' => 30.802498,
                'currencies' => ['EGP'],
                'translations' => [
                    'lt' => [
                        'name' => 'Egiptas',
                        'name_official' => 'Egipto Arabų Respublika',
                    ],
                    'en' => [
                        'name' => 'Egypt',
                        'name_official' => 'Arab Republic of Egypt',
                    ],
                ],
            ],
            [
                'cca2' => 'NG',
                'cca3' => 'NGA',
                'phone_calling_code' => '234',
                'flag' => '🇳🇬',
                'region' => 'Africa',
                'subregion' => 'Western Africa',
                'latitude' => 9.081999,
                'longitude' => 8.675277,
                'currencies' => ['NGN'],
                'translations' => [
                    'lt' => [
                        'name' => 'Nigerija',
                        'name_official' => 'Nigerijos Federacinė Respublika',
                    ],
                    'en' => [
                        'name' => 'Nigeria',
                        'name_official' => 'Federal Republic of Nigeria',
                    ],
                ],
            ],
            [
                'cca2' => 'KE',
                'cca3' => 'KEN',
                'phone_calling_code' => '254',
                'flag' => '🇰🇪',
                'region' => 'Africa',
                'subregion' => 'Eastern Africa',
                'latitude' => -0.023559,
                'longitude' => 37.906193,
                'currencies' => ['KES'],
                'translations' => [
                    'lt' => [
                        'name' => 'Kenija',
                        'name_official' => 'Kenijos Respublika',
                    ],
                    'en' => [
                        'name' => 'Kenya',
                        'name_official' => 'Republic of Kenya',
                    ],
                ],
            ],
            [
                'cca2' => 'TR',
                'cca3' => 'TUR',
                'phone_calling_code' => '90',
                'flag' => '🇹🇷',
                'region' => 'Asia',
                'subregion' => 'Western Asia',
                'latitude' => 38.963745,
                'longitude' => 35.243322,
                'currencies' => ['TRY'],
                'translations' => [
                    'lt' => [
                        'name' => 'Turkija',
                        'name_official' => 'Turkijos Respublika',
                    ],
                    'en' => [
                        'name' => 'Turkey',
                        'name_official' => 'Republic of Turkey',
                    ],
                ],
            ],
            [
                'cca2' => 'SA',
                'cca3' => 'SAU',
                'phone_calling_code' => '966',
                'flag' => '🇸🇦',
                'region' => 'Asia',
                'subregion' => 'Western Asia',
                'latitude' => 23.885942,
                'longitude' => 45.079162,
                'currencies' => ['SAR'],
                'translations' => [
                    'lt' => [
                        'name' => 'Saudo Arabija',
                        'name_official' => 'Saudo Arabijos Karalystė',
                    ],
                    'en' => [
                        'name' => 'Saudi Arabia',
                        'name_official' => 'Kingdom of Saudi Arabia',
                    ],
                ],
            ],
            [
                'cca2' => 'AE',
                'cca3' => 'ARE',
                'phone_calling_code' => '971',
                'flag' => '🇦🇪',
                'region' => 'Asia',
                'subregion' => 'Western Asia',
                'latitude' => 23.424076,
                'longitude' => 53.847818,
                'currencies' => ['AED'],
                'translations' => [
                    'lt' => [
                        'name' => 'Jungtiniai Arabų Emyratai',
                        'name_official' => 'Jungtiniai Arabų Emyratai',
                    ],
                    'en' => [
                        'name' => 'United Arab Emirates',
                        'name_official' => 'United Arab Emirates',
                    ],
                ],
            ],
            [
                'cca2' => 'IL',
                'cca3' => 'ISR',
                'phone_calling_code' => '972',
                'flag' => '🇮🇱',
                'region' => 'Asia',
                'subregion' => 'Western Asia',
                'latitude' => 31.046051,
                'longitude' => 34.851612,
                'currencies' => ['ILS'],
                'translations' => [
                    'lt' => [
                        'name' => 'Izraelis',
                        'name_official' => 'Izraelio Valstybė',
                    ],
                    'en' => [
                        'name' => 'Israel',
                        'name_official' => 'State of Israel',
                    ],
                ],
            ],
            [
                'cca2' => 'TH',
                'cca3' => 'THA',
                'phone_calling_code' => '66',
                'flag' => '🇹🇭',
                'region' => 'Asia',
                'subregion' => 'South-Eastern Asia',
                'latitude' => 15.870032,
                'longitude' => 100.992541,
                'currencies' => ['THB'],
                'translations' => [
                    'lt' => [
                        'name' => 'Tailandas',
                        'name_official' => 'Tailando Karalystė',
                    ],
                    'en' => [
                        'name' => 'Thailand',
                        'name_official' => 'Kingdom of Thailand',
                    ],
                ],
            ],
            [
                'cca2' => 'SG',
                'cca3' => 'SGP',
                'phone_calling_code' => '65',
                'flag' => '🇸🇬',
                'region' => 'Asia',
                'subregion' => 'South-Eastern Asia',
                'latitude' => 1.352083,
                'longitude' => 103.819836,
                'currencies' => ['SGD'],
                'translations' => [
                    'lt' => [
                        'name' => 'Singapūras',
                        'name_official' => 'Singapūro Respublika',
                    ],
                    'en' => [
                        'name' => 'Singapore',
                        'name_official' => 'Republic of Singapore',
                    ],
                ],
            ],
            [
                'cca2' => 'MY',
                'cca3' => 'MYS',
                'phone_calling_code' => '60',
                'flag' => '🇲🇾',
                'region' => 'Asia',
                'subregion' => 'South-Eastern Asia',
                'latitude' => 4.210484,
                'longitude' => 101.975766,
                'currencies' => ['MYR'],
                'translations' => [
                    'lt' => [
                        'name' => 'Malaizija',
                        'name_official' => 'Malaizija',
                    ],
                    'en' => [
                        'name' => 'Malaysia',
                        'name_official' => 'Malaysia',
                    ],
                ],
            ],
            [
                'cca2' => 'ID',
                'cca3' => 'IDN',
                'phone_calling_code' => '62',
                'flag' => '🇮🇩',
                'region' => 'Asia',
                'subregion' => 'South-Eastern Asia',
                'latitude' => -0.789275,
                'longitude' => 113.921327,
                'currencies' => ['IDR'],
                'translations' => [
                    'lt' => [
                        'name' => 'Indonezija',
                        'name_official' => 'Indonezijos Respublika',
                    ],
                    'en' => [
                        'name' => 'Indonesia',
                        'name_official' => 'Republic of Indonesia',
                    ],
                ],
            ],
            [
                'cca2' => 'PH',
                'cca3' => 'PHL',
                'phone_calling_code' => '63',
                'flag' => '🇵🇭',
                'region' => 'Asia',
                'subregion' => 'South-Eastern Asia',
                'latitude' => 12.879721,
                'longitude' => 121.774017,
                'currencies' => ['PHP'],
                'translations' => [
                    'lt' => [
                        'name' => 'Filipinai',
                        'name_official' => 'Filipinų Respublika',
                    ],
                    'en' => [
                        'name' => 'Philippines',
                        'name_official' => 'Republic of the Philippines',
                    ],
                ],
            ],
            [
                'cca2' => 'VN',
                'cca3' => 'VNM',
                'phone_calling_code' => '84',
                'flag' => '🇻🇳',
                'region' => 'Asia',
                'subregion' => 'South-Eastern Asia',
                'latitude' => 14.058324,
                'longitude' => 108.277199,
                'currencies' => ['VND'],
                'translations' => [
                    'lt' => [
                        'name' => 'Vietnamas',
                        'name_official' => 'Vietnamo Socialistinė Respublika',
                    ],
                    'en' => [
                        'name' => 'Vietnam',
                        'name_official' => 'Socialist Republic of Vietnam',
                    ],
                ],
            ],
        ];
    }
}
