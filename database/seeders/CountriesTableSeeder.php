<?php declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class CountriesTableSeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key checks for faster seeding (database-agnostic)
        $this->disableForeignKeyChecks();

        $countries = $this->getEuropeanCountriesData();

        foreach ($countries as $countryData) {
            // Create/update the main country record
            $country = Country::query()->updateOrCreate(
                ['cca2' => $countryData['cca2']],
                [
                    'region' => $countryData['region'],
                    'subregion' => $countryData['subregion'],
                    'cca2' => $countryData['cca2'],
                    'cca3' => $countryData['cca3'],
                    'flag' => $countryData['flag'],
                    'latitude' => $countryData['latitude'],
                    'longitude' => $countryData['longitude'],
                    'phone_calling_code' => $countryData['phone_calling_code'],
                    'currencies' => $countryData['currencies'],
                ]
            );

            // Create/update translations
            foreach ($countryData['translations'] as $locale => $translation) {
                $country->translations()->updateOrCreate(
                    ['locale' => $locale],
                    [
                        'name' => $translation['name'],
                        'name_official' => $translation['name_official'],
                    ]
                );
            }
        }

        // Re-enable foreign key checks
        $this->enableForeignKeyChecks();

        $this->command->info('European countries seeded successfully! Total: ' . count($countries));
    }

    private function disableForeignKeyChecks(): void
    {
        $driver = DB::getDriverName();

        match ($driver) {
            'mysql' => DB::statement('SET FOREIGN_KEY_CHECKS=0;'),
            'sqlite' => DB::statement('PRAGMA foreign_keys=OFF;'),
            'pgsql' => DB::statement('SET session_replication_role = replica;'),
            default => null,
        };
    }

    private function enableForeignKeyChecks(): void
    {
        $driver = DB::getDriverName();

        match ($driver) {
            'mysql' => DB::statement('SET FOREIGN_KEY_CHECKS=1;'),
            'sqlite' => DB::statement('PRAGMA foreign_keys=ON;'),
            'pgsql' => DB::statement('SET session_replication_role = DEFAULT;'),
            default => null,
        };
    }

    private function getEuropeanCountriesData(): array
    {
        return [
            [
                'region' => 'Europe',
                'subregion' => 'Southern Europe',
                'cca2' => 'AL',
                'cca3' => 'ALB',
                'flag' => '🇦🇱',
                'latitude' => 41.1533,
                'longitude' => 20.1683,
                'phone_calling_code' => '355',
                'currencies' => ['ALL'],
                'translations' => [
                    'en' => ['name' => 'Albania', 'name_official' => 'Republic of Albania'],
                    'lt' => ['name' => 'Albanija', 'name_official' => 'Albanijos Respublika'],
                ]
            ],
            [
                'region' => 'Europe',
                'subregion' => 'Southern Europe',
                'cca2' => 'AD',
                'cca3' => 'AND',
                'flag' => '🇦🇩',
                'latitude' => 42.5063,
                'longitude' => 1.5218,
                'phone_calling_code' => '376',
                'currencies' => ['EUR'],
                'translations' => [
                    'en' => ['name' => 'Andorra', 'name_official' => 'Principality of Andorra'],
                    'lt' => ['name' => 'Andora', 'name_official' => 'Andoros Kunigaikštystė'],
                ]
            ],
            [
                'region' => 'Europe',
                'subregion' => 'Western Europe',
                'cca2' => 'AT',
                'cca3' => 'AUT',
                'flag' => '🇦🇹',
                'latitude' => 47.5162,
                'longitude' => 14.5501,
                'phone_calling_code' => '43',
                'currencies' => ['EUR'],
                'translations' => [
                    'en' => ['name' => 'Austria', 'name_official' => 'Republic of Austria'],
                    'lt' => ['name' => 'Austrija', 'name_official' => 'Austrijos Respublika'],
                ]
            ],
            [
                'region' => 'Europe',
                'subregion' => 'Eastern Europe',
                'cca2' => 'BY',
                'cca3' => 'BLR',
                'flag' => '🇧🇾',
                'latitude' => 53.7098,
                'longitude' => 27.9534,
                'phone_calling_code' => '375',
                'currencies' => ['BYN'],
                'translations' => [
                    'en' => ['name' => 'Belarus', 'name_official' => 'Republic of Belarus'],
                    'lt' => ['name' => 'Baltarusija', 'name_official' => 'Baltarusijos Respublika'],
                ]
            ],
            [
                'region' => 'Europe',
                'subregion' => 'Western Europe',
                'cca2' => 'BE',
                'cca3' => 'BEL',
                'flag' => '🇧🇪',
                'latitude' => 50.5039,
                'longitude' => 4.4699,
                'phone_calling_code' => '32',
                'currencies' => ['EUR'],
                'translations' => [
                    'en' => ['name' => 'Belgium', 'name_official' => 'Kingdom of Belgium'],
                    'lt' => ['name' => 'Belgija', 'name_official' => 'Belgijos Karalystė'],
                ]
            ],
            [
                'region' => 'Europe',
                'subregion' => 'Southern Europe',
                'cca2' => 'BA',
                'cca3' => 'BIH',
                'flag' => '🇧🇦',
                'latitude' => 43.9159,
                'longitude' => 17.6791,
                'phone_calling_code' => '387',
                'currencies' => ['BAM'],
                'translations' => [
                    'en' => ['name' => 'Bosnia and Herzegovina', 'name_official' => 'Bosnia and Herzegovina'],
                    'lt' => ['name' => 'Bosnija ir Hercegovina', 'name_official' => 'Bosnija ir Hercegovina'],
                ]
            ],
            [
                'region' => 'Europe',
                'subregion' => 'Eastern Europe',
                'cca2' => 'BG',
                'cca3' => 'BGR',
                'flag' => '🇧🇬',
                'latitude' => 42.7339,
                'longitude' => 25.4858,
                'phone_calling_code' => '359',
                'currencies' => ['BGN'],
                'translations' => [
                    'en' => ['name' => 'Bulgaria', 'name_official' => 'Republic of Bulgaria'],
                    'lt' => ['name' => 'Bulgarija', 'name_official' => 'Bulgarijos Respublika'],
                ]
            ],
            [
                'region' => 'Europe',
                'subregion' => 'Southern Europe',
                'cca2' => 'HR',
                'cca3' => 'HRV',
                'flag' => '🇭🇷',
                'latitude' => 45.1,
                'longitude' => 15.2,
                'phone_calling_code' => '385',
                'currencies' => ['EUR'],
                'translations' => [
                    'en' => ['name' => 'Croatia', 'name_official' => 'Republic of Croatia'],
                    'lt' => ['name' => 'Kroatija', 'name_official' => 'Kroatijos Respublika'],
                ]
            ],
            [
                'region' => 'Europe',
                'subregion' => 'Southern Europe',
                'cca2' => 'CY',
                'cca3' => 'CYP',
                'flag' => '🇨🇾',
                'latitude' => 35.1264,
                'longitude' => 33.4299,
                'phone_calling_code' => '357',
                'currencies' => ['EUR'],
                'translations' => [
                    'en' => ['name' => 'Cyprus', 'name_official' => 'Republic of Cyprus'],
                    'lt' => ['name' => 'Kipras', 'name_official' => 'Kipro Respublika'],
                ]
            ],
            [
                'region' => 'Europe',
                'subregion' => 'Central Europe',
                'cca2' => 'CZ',
                'cca3' => 'CZE',
                'flag' => '🇨🇿',
                'latitude' => 49.8175,
                'longitude' => 15.473,
                'phone_calling_code' => '420',
                'currencies' => ['CZK'],
                'translations' => [
                    'en' => ['name' => 'Czech Republic', 'name_official' => 'Czech Republic'],
                    'lt' => ['name' => 'Čekija', 'name_official' => 'Čekijos Respublika'],
                ]
            ],
            [
                'region' => 'Europe',
                'subregion' => 'Northern Europe',
                'cca2' => 'DK',
                'cca3' => 'DNK',
                'flag' => '🇩🇰',
                'latitude' => 56.2639,
                'longitude' => 9.5018,
                'phone_calling_code' => '45',
                'currencies' => ['DKK'],
                'translations' => [
                    'en' => ['name' => 'Denmark', 'name_official' => 'Kingdom of Denmark'],
                    'lt' => ['name' => 'Danija', 'name_official' => 'Danijos Karalystė'],
                ]
            ],
            [
                'region' => 'Europe',
                'subregion' => 'Northern Europe',
                'cca2' => 'EE',
                'cca3' => 'EST',
                'flag' => '🇪🇪',
                'latitude' => 58.5953,
                'longitude' => 25.0136,
                'phone_calling_code' => '372',
                'currencies' => ['EUR'],
                'translations' => [
                    'en' => ['name' => 'Estonia', 'name_official' => 'Republic of Estonia'],
                    'lt' => ['name' => 'Estija', 'name_official' => 'Estijos Respublika'],
                ]
            ],
            [
                'region' => 'Europe',
                'subregion' => 'Northern Europe',
                'cca2' => 'FO',
                'cca3' => 'FRO',
                'flag' => '🇫🇴',
                'latitude' => 61.8926,
                'longitude' => -6.9118,
                'phone_calling_code' => '298',
                'currencies' => ['DKK'],
                'translations' => [
                    'en' => ['name' => 'Faroe Islands', 'name_official' => 'Faroe Islands'],
                    'lt' => ['name' => 'Farerų salos', 'name_official' => 'Farerų salos'],
                ]
            ],
            [
                'region' => 'Europe',
                'subregion' => 'Northern Europe',
                'cca2' => 'FI',
                'cca3' => 'FIN',
                'flag' => '🇫🇮',
                'latitude' => 61.9241,
                'longitude' => 25.7482,
                'phone_calling_code' => '358',
                'currencies' => ['EUR'],
                'translations' => [
                    'en' => ['name' => 'Finland', 'name_official' => 'Republic of Finland'],
                    'lt' => ['name' => 'Suomija', 'name_official' => 'Suomijos Respublika'],
                ]
            ],
            [
                'region' => 'Europe',
                'subregion' => 'Western Europe',
                'cca2' => 'FR',
                'cca3' => 'FRA',
                'flag' => '🇫🇷',
                'latitude' => 46.2276,
                'longitude' => 2.2137,
                'phone_calling_code' => '33',
                'currencies' => ['EUR'],
                'translations' => [
                    'en' => ['name' => 'France', 'name_official' => 'French Republic'],
                    'lt' => ['name' => 'Prancūzija', 'name_official' => 'Prancūzijos Respublika'],
                ]
            ],
            [
                'region' => 'Europe',
                'subregion' => 'Western Europe',
                'cca2' => 'DE',
                'cca3' => 'DEU',
                'flag' => '🇩🇪',
                'latitude' => 51.1657,
                'longitude' => 10.4515,
                'phone_calling_code' => '49',
                'currencies' => ['EUR'],
                'translations' => [
                    'en' => ['name' => 'Germany', 'name_official' => 'Federal Republic of Germany'],
                    'lt' => ['name' => 'Vokietija', 'name_official' => 'Vokietijos Federacinė Respublika'],
                ]
            ],
            [
                'region' => 'Europe',
                'subregion' => 'Southern Europe',
                'cca2' => 'GI',
                'cca3' => 'GIB',
                'flag' => '🇬🇮',
                'latitude' => 36.1408,
                'longitude' => -5.3536,
                'phone_calling_code' => '350',
                'currencies' => ['GIP'],
                'translations' => [
                    'en' => ['name' => 'Gibraltar', 'name_official' => 'Gibraltar'],
                    'lt' => ['name' => 'Gibraltaras', 'name_official' => 'Gibraltaras'],
                ]
            ],
            [
                'region' => 'Europe',
                'subregion' => 'Southern Europe',
                'cca2' => 'GR',
                'cca3' => 'GRC',
                'flag' => '🇬🇷',
                'latitude' => 39.0742,
                'longitude' => 21.8243,
                'phone_calling_code' => '30',
                'currencies' => ['EUR'],
                'translations' => [
                    'en' => ['name' => 'Greece', 'name_official' => 'Hellenic Republic'],
                    'lt' => ['name' => 'Graikija', 'name_official' => 'Graikijos Respublika'],
                ]
            ],
            [
                'region' => 'Europe',
                'subregion' => 'Northern Europe',
                'cca2' => 'GG',
                'cca3' => 'GGY',
                'flag' => '🇬🇬',
                'latitude' => 49.4658,
                'longitude' => -2.5854,
                'phone_calling_code' => '44',
                'currencies' => ['GBP'],
                'translations' => [
                    'en' => ['name' => 'Guernsey', 'name_official' => 'Bailiwick of Guernsey'],
                    'lt' => ['name' => 'Gernsis', 'name_official' => 'Gernsio Bailivikas'],
                ]
            ],
            [
                'region' => 'Europe',
                'subregion' => 'Central Europe',
                'cca2' => 'HU',
                'cca3' => 'HUN',
                'flag' => '🇭🇺',
                'latitude' => 47.1625,
                'longitude' => 19.5033,
                'phone_calling_code' => '36',
                'currencies' => ['HUF'],
                'translations' => [
                    'en' => ['name' => 'Hungary', 'name_official' => 'Hungary'],
                    'lt' => ['name' => 'Vengrija', 'name_official' => 'Vengrija'],
                ]
            ],
            [
                'region' => 'Europe',
                'subregion' => 'Northern Europe',
                'cca2' => 'IS',
                'cca3' => 'ISL',
                'flag' => '🇮🇸',
                'latitude' => 64.9631,
                'longitude' => -19.0208,
                'phone_calling_code' => '354',
                'currencies' => ['ISK'],
                'translations' => [
                    'en' => ['name' => 'Iceland', 'name_official' => 'Iceland'],
                    'lt' => ['name' => 'Islandija', 'name_official' => 'Islandija'],
                ]
            ],
            [
                'region' => 'Europe',
                'subregion' => 'Northern Europe',
                'cca2' => 'IE',
                'cca3' => 'IRL',
                'flag' => '🇮🇪',
                'latitude' => 53.4129,
                'longitude' => -8.2439,
                'phone_calling_code' => '353',
                'currencies' => ['EUR'],
                'translations' => [
                    'en' => ['name' => 'Ireland', 'name_official' => 'Republic of Ireland'],
                    'lt' => ['name' => 'Airija', 'name_official' => 'Airijos Respublika'],
                ]
            ],
            [
                'region' => 'Europe',
                'subregion' => 'Northern Europe',
                'cca2' => 'IM',
                'cca3' => 'IMN',
                'flag' => '🇮🇲',
                'latitude' => 54.2361,
                'longitude' => -4.5481,
                'phone_calling_code' => '44',
                'currencies' => ['GBP'],
                'translations' => [
                    'en' => ['name' => 'Isle of Man', 'name_official' => 'Isle of Man'],
                    'lt' => ['name' => 'Meno sala', 'name_official' => 'Meno sala'],
                ]
            ],
            [
                'region' => 'Europe',
                'subregion' => 'Southern Europe',
                'cca2' => 'IT',
                'cca3' => 'ITA',
                'flag' => '🇮🇹',
                'latitude' => 41.8719,
                'longitude' => 12.5674,
                'phone_calling_code' => '39',
                'currencies' => ['EUR'],
                'translations' => [
                    'en' => ['name' => 'Italy', 'name_official' => 'Italian Republic'],
                    'lt' => ['name' => 'Italija', 'name_official' => 'Italijos Respublika'],
                ]
            ],
            [
                'region' => 'Europe',
                'subregion' => 'Northern Europe',
                'cca2' => 'JE',
                'cca3' => 'JEY',
                'flag' => '🇯🇪',
                'latitude' => 49.2144,
                'longitude' => -2.1312,
                'phone_calling_code' => '44',
                'currencies' => ['GBP'],
                'translations' => [
                    'en' => ['name' => 'Jersey', 'name_official' => 'Bailiwick of Jersey'],
                    'lt' => ['name' => 'Džersis', 'name_official' => 'Džersio Bailivikas'],
                ]
            ],
            [
                'region' => 'Europe',
                'subregion' => 'Northern Europe',
                'cca2' => 'LV',
                'cca3' => 'LVA',
                'flag' => '🇱🇻',
                'latitude' => 56.8796,
                'longitude' => 24.6032,
                'phone_calling_code' => '371',
                'currencies' => ['EUR'],
                'translations' => [
                    'en' => ['name' => 'Latvia', 'name_official' => 'Republic of Latvia'],
                    'lt' => ['name' => 'Latvija', 'name_official' => 'Latvijos Respublika'],
                ]
            ],
            [
                'region' => 'Europe',
                'subregion' => 'Western Europe',
                'cca2' => 'LI',
                'cca3' => 'LIE',
                'flag' => '🇱🇮',
                'latitude' => 47.166,
                'longitude' => 9.5554,
                'phone_calling_code' => '423',
                'currencies' => ['CHF'],
                'translations' => [
                    'en' => ['name' => 'Liechtenstein', 'name_official' => 'Principality of Liechtenstein'],
                    'lt' => ['name' => 'Lichtenšteinas', 'name_official' => 'Lichtenšteino Kunigaikštystė'],
                ]
            ],
            [
                'region' => 'Europe',
                'subregion' => 'Northern Europe',
                'cca2' => 'LT',
                'cca3' => 'LTU',
                'flag' => '🇱🇹',
                'latitude' => 55.1694,
                'longitude' => 23.8813,
                'phone_calling_code' => '370',
                'currencies' => ['EUR'],
                'translations' => [
                    'en' => ['name' => 'Lithuania', 'name_official' => 'Republic of Lithuania'],
                    'lt' => ['name' => 'Lietuva', 'name_official' => 'Lietuvos Respublika'],
                ]
            ],
            [
                'region' => 'Europe',
                'subregion' => 'Western Europe',
                'cca2' => 'LU',
                'cca3' => 'LUX',
                'flag' => '🇱🇺',
                'latitude' => 49.8153,
                'longitude' => 6.1296,
                'phone_calling_code' => '352',
                'currencies' => ['EUR'],
                'translations' => [
                    'en' => ['name' => 'Luxembourg', 'name_official' => 'Grand Duchy of Luxembourg'],
                    'lt' => ['name' => 'Liuksemburgas', 'name_official' => 'Liuksemburgo Didžioji Hercogystė'],
                ]
            ],
            [
                'region' => 'Europe',
                'subregion' => 'Southern Europe',
                'cca2' => 'MT',
                'cca3' => 'MLT',
                'flag' => '🇲🇹',
                'latitude' => 35.9375,
                'longitude' => 14.3754,
                'phone_calling_code' => '356',
                'currencies' => ['EUR'],
                'translations' => [
                    'en' => ['name' => 'Malta', 'name_official' => 'Republic of Malta'],
                    'lt' => ['name' => 'Malta', 'name_official' => 'Maltos Respublika'],
                ]
            ],
            [
                'region' => 'Europe',
                'subregion' => 'Eastern Europe',
                'cca2' => 'MD',
                'cca3' => 'MDA',
                'flag' => '🇲🇩',
                'latitude' => 47.4116,
                'longitude' => 28.3699,
                'phone_calling_code' => '373',
                'currencies' => ['MDL'],
                'translations' => [
                    'en' => ['name' => 'Moldova', 'name_official' => 'Republic of Moldova'],
                    'lt' => ['name' => 'Moldova', 'name_official' => 'Moldovos Respublika'],
                ]
            ],
            [
                'region' => 'Europe',
                'subregion' => 'Western Europe',
                'cca2' => 'MC',
                'cca3' => 'MCO',
                'flag' => '🇲🇨',
                'latitude' => 43.7508,
                'longitude' => 7.412,
                'phone_calling_code' => '377',
                'currencies' => ['EUR'],
                'translations' => [
                    'en' => ['name' => 'Monaco', 'name_official' => 'Principality of Monaco'],
                    'lt' => ['name' => 'Monakas', 'name_official' => 'Monako Kunigaikštystė'],
                ]
            ],
            [
                'region' => 'Europe',
                'subregion' => 'Southern Europe',
                'cca2' => 'ME',
                'cca3' => 'MNE',
                'flag' => '🇲🇪',
                'latitude' => 42.7087,
                'longitude' => 19.3744,
                'phone_calling_code' => '382',
                'currencies' => ['EUR'],
                'translations' => [
                    'en' => ['name' => 'Montenegro', 'name_official' => 'Montenegro'],
                    'lt' => ['name' => 'Juodkalnija', 'name_official' => 'Juodkalnija'],
                ]
            ],
            [
                'region' => 'Europe',
                'subregion' => 'Western Europe',
                'cca2' => 'NL',
                'cca3' => 'NLD',
                'flag' => '🇳🇱',
                'latitude' => 52.1326,
                'longitude' => 5.2913,
                'phone_calling_code' => '31',
                'currencies' => ['EUR'],
                'translations' => [
                    'en' => ['name' => 'Netherlands', 'name_official' => 'Kingdom of the Netherlands'],
                    'lt' => ['name' => 'Nyderlandai', 'name_official' => 'Nyderlandų Karalystė'],
                ]
            ],
            [
                'region' => 'Europe',
                'subregion' => 'Southern Europe',
                'cca2' => 'MK',
                'cca3' => 'MKD',
                'flag' => '🇲🇰',
                'latitude' => 41.6086,
                'longitude' => 21.7453,
                'phone_calling_code' => '389',
                'currencies' => ['MKD'],
                'translations' => [
                    'en' => ['name' => 'North Macedonia', 'name_official' => 'Republic of North Macedonia'],
                    'lt' => ['name' => 'Šiaurės Makedonija', 'name_official' => 'Šiaurės Makedonijos Respublika'],
                ]
            ],
            [
                'region' => 'Europe',
                'subregion' => 'Northern Europe',
                'cca2' => 'NO',
                'cca3' => 'NOR',
                'flag' => '🇳🇴',
                'latitude' => 60.472,
                'longitude' => 8.4689,
                'phone_calling_code' => '47',
                'currencies' => ['NOK'],
                'translations' => [
                    'en' => ['name' => 'Norway', 'name_official' => 'Kingdom of Norway'],
                    'lt' => ['name' => 'Norvegija', 'name_official' => 'Norvegijos Karalystė'],
                ]
            ],
            [
                'region' => 'Europe',
                'subregion' => 'Central Europe',
                'cca2' => 'PL',
                'cca3' => 'POL',
                'flag' => '🇵🇱',
                'latitude' => 51.9194,
                'longitude' => 19.1451,
                'phone_calling_code' => '48',
                'currencies' => ['PLN'],
                'translations' => [
                    'en' => ['name' => 'Poland', 'name_official' => 'Republic of Poland'],
                    'lt' => ['name' => 'Lenkija', 'name_official' => 'Lenkijos Respublika'],
                ]
            ],
            [
                'region' => 'Europe',
                'subregion' => 'Southern Europe',
                'cca2' => 'PT',
                'cca3' => 'PRT',
                'flag' => '🇵🇹',
                'latitude' => 39.3999,
                'longitude' => -8.2245,
                'phone_calling_code' => '351',
                'currencies' => ['EUR'],
                'translations' => [
                    'en' => ['name' => 'Portugal', 'name_official' => 'Portuguese Republic'],
                    'lt' => ['name' => 'Portugalija', 'name_official' => 'Portugalijos Respublika'],
                ]
            ],
            [
                'region' => 'Europe',
                'subregion' => 'Eastern Europe',
                'cca2' => 'RO',
                'cca3' => 'ROU',
                'flag' => '🇷🇴',
                'latitude' => 45.9432,
                'longitude' => 24.9668,
                'phone_calling_code' => '40',
                'currencies' => ['RON'],
                'translations' => [
                    'en' => ['name' => 'Romania', 'name_official' => 'Romania'],
                    'lt' => ['name' => 'Rumunija', 'name_official' => 'Rumunija'],
                ]
            ],
            [
                'region' => 'Europe',
                'subregion' => 'Eastern Europe',
                'cca2' => 'RU',
                'cca3' => 'RUS',
                'flag' => '🇷🇺',
                'latitude' => 61.524,
                'longitude' => 105.3188,
                'phone_calling_code' => '7',
                'currencies' => ['RUB'],
                'translations' => [
                    'en' => ['name' => 'Russia', 'name_official' => 'Russian Federation'],
                    'lt' => ['name' => 'Rusija', 'name_official' => 'Rusijos Federacija'],
                ]
            ],
            [
                'region' => 'Europe',
                'subregion' => 'Southern Europe',
                'cca2' => 'SM',
                'cca3' => 'SMR',
                'flag' => '🇸🇲',
                'latitude' => 43.9424,
                'longitude' => 12.4578,
                'phone_calling_code' => '378',
                'currencies' => ['EUR'],
                'translations' => [
                    'en' => ['name' => 'San Marino', 'name_official' => 'Republic of San Marino'],
                    'lt' => ['name' => 'San Marinas', 'name_official' => 'San Marino Respublika'],
                ]
            ],
            [
                'region' => 'Europe',
                'subregion' => 'Southern Europe',
                'cca2' => 'RS',
                'cca3' => 'SRB',
                'flag' => '🇷🇸',
                'latitude' => 44.0165,
                'longitude' => 21.0059,
                'phone_calling_code' => '381',
                'currencies' => ['RSD'],
                'translations' => [
                    'en' => ['name' => 'Serbia', 'name_official' => 'Republic of Serbia'],
                    'lt' => ['name' => 'Serbija', 'name_official' => 'Serbijos Respublika'],
                ]
            ],
            [
                'region' => 'Europe',
                'subregion' => 'Central Europe',
                'cca2' => 'SK',
                'cca3' => 'SVK',
                'flag' => '🇸🇰',
                'latitude' => 48.669,
                'longitude' => 19.699,
                'phone_calling_code' => '421',
                'currencies' => ['EUR'],
                'translations' => [
                    'en' => ['name' => 'Slovakia', 'name_official' => 'Slovak Republic'],
                    'lt' => ['name' => 'Slovakija', 'name_official' => 'Slovakijos Respublika'],
                ]
            ],
            [
                'region' => 'Europe',
                'subregion' => 'Central Europe',
                'cca2' => 'SI',
                'cca3' => 'SVN',
                'flag' => '🇸🇮',
                'latitude' => 46.1512,
                'longitude' => 14.9955,
                'phone_calling_code' => '386',
                'currencies' => ['EUR'],
                'translations' => [
                    'en' => ['name' => 'Slovenia', 'name_official' => 'Republic of Slovenia'],
                    'lt' => ['name' => 'Slovėnija', 'name_official' => 'Slovėnijos Respublika'],
                ]
            ],
            [
                'region' => 'Europe',
                'subregion' => 'Southern Europe',
                'cca2' => 'ES',
                'cca3' => 'ESP',
                'flag' => '🇪🇸',
                'latitude' => 40.4637,
                'longitude' => -3.7492,
                'phone_calling_code' => '34',
                'currencies' => ['EUR'],
                'translations' => [
                    'en' => ['name' => 'Spain', 'name_official' => 'Kingdom of Spain'],
                    'lt' => ['name' => 'Ispanija', 'name_official' => 'Ispanijos Karalystė'],
                ]
            ],
            [
                'region' => 'Europe',
                'subregion' => 'Northern Europe',
                'cca2' => 'SJ',
                'cca3' => 'SJM',
                'flag' => '🇸🇯',
                'latitude' => 77.5536,
                'longitude' => 23.6703,
                'phone_calling_code' => '47',
                'currencies' => ['NOK'],
                'translations' => [
                    'en' => ['name' => 'Svalbard and Jan Mayen', 'name_official' => 'Svalbard and Jan Mayen'],
                    'lt' => ['name' => 'Svalbardas ir Jan Majenas', 'name_official' => 'Svalbardas ir Jan Majenas'],
                ]
            ],
            [
                'region' => 'Europe',
                'subregion' => 'Northern Europe',
                'cca2' => 'SE',
                'cca3' => 'SWE',
                'flag' => '🇸🇪',
                'latitude' => 60.1282,
                'longitude' => 18.6435,
                'phone_calling_code' => '46',
                'currencies' => ['SEK'],
                'translations' => [
                    'en' => ['name' => 'Sweden', 'name_official' => 'Kingdom of Sweden'],
                    'lt' => ['name' => 'Švedija', 'name_official' => 'Švedijos Karalystė'],
                ]
            ],
            [
                'region' => 'Europe',
                'subregion' => 'Western Europe',
                'cca2' => 'CH',
                'cca3' => 'CHE',
                'flag' => '🇨🇭',
                'latitude' => 46.8182,
                'longitude' => 8.2275,
                'phone_calling_code' => '41',
                'currencies' => ['CHF'],
                'translations' => [
                    'en' => ['name' => 'Switzerland', 'name_official' => 'Swiss Confederation'],
                    'lt' => ['name' => 'Šveicarija', 'name_official' => 'Šveicarijos Konfederacija'],
                ]
            ],
            [
                'region' => 'Europe',
                'subregion' => 'Eastern Europe',
                'cca2' => 'UA',
                'cca3' => 'UKR',
                'flag' => '🇺🇦',
                'latitude' => 48.3794,
                'longitude' => 31.1656,
                'phone_calling_code' => '380',
                'currencies' => ['UAH'],
                'translations' => [
                    'en' => ['name' => 'Ukraine', 'name_official' => 'Ukraine'],
                    'lt' => ['name' => 'Ukraina', 'name_official' => 'Ukraina'],
                ]
            ],
            [
                'region' => 'Europe',
                'subregion' => 'Northern Europe',
                'cca2' => 'GB',
                'cca3' => 'GBR',
                'flag' => '🇬🇧',
                'latitude' => 55.3781,
                'longitude' => -3.436,
                'phone_calling_code' => '44',
                'currencies' => ['GBP'],
                'translations' => [
                    'en' => ['name' => 'United Kingdom', 'name_official' => 'United Kingdom of Great Britain and Northern Ireland'],
                    'lt' => ['name' => 'Jungtinė Karalystė', 'name_official' => 'Didžiosios Britanijos ir Šiaurės Airijos Jungtinė Karalystė'],
                ]
            ],
            [
                'region' => 'Europe',
                'subregion' => 'Southern Europe',
                'cca2' => 'VA',
                'cca3' => 'VAT',
                'flag' => '🇻🇦',
                'latitude' => 41.9029,
                'longitude' => 12.4534,
                'phone_calling_code' => '379',
                'currencies' => ['EUR'],
                'translations' => [
                    'en' => ['name' => 'Vatican City', 'name_official' => 'Vatican City State'],
                    'lt' => ['name' => 'Vatikanas', 'name_official' => 'Vatikano Miesto Valstybė'],
                ]
            ],
            [
                'region' => 'Europe',
                'subregion' => 'Northern Europe',
                'cca2' => 'AX',
                'cca3' => 'ALA',
                'flag' => '🇦🇽',
                'latitude' => 60.1785,
                'longitude' => 19.9156,
                'phone_calling_code' => '358',
                'currencies' => ['EUR'],
                'translations' => [
                    'en' => ['name' => 'Åland Islands', 'name_official' => 'Åland Islands'],
                    'lt' => ['name' => 'Alandų salos', 'name_official' => 'Alandų salos'],
                ]
            ],
        ];
    }
}
