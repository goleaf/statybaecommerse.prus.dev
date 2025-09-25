<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Services\Images\LocalImageGeneratorService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    private LocalImageGeneratorService $imageGenerator;

    public function __construct()
    {
        $this->imageGenerator = new LocalImageGeneratorService;
    }

    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $categories = $this->getCategoriesData();

        foreach ($categories as $categoryData) {
            $this->createCategory($categoryData);
        }
    }

    /**
     * Get comprehensive categories data with Lithuanian names and translations
     */
    private function getCategoriesData(): array
    {
        return [
            [
                'name' => 'Sandarinimo plėvelės ir juostos',
                'slug' => 'sandarinimo-pleveles-ir-juostos',
                'description' => 'Sandarinimo plėvelės ir juostos statyboms',
                'sort_order' => 1,
                'children' => [
                    [
                        'name' => 'Juostos',
                        'slug' => 'juostos',
                        'description' => 'Sandarinimo juostos',
                        'sort_order' => 1,
                        'children' => [
                            [
                                'name' => 'Laukui',
                                'slug' => 'juostos-laukui',
                                'description' => 'Juostos lauko darbams',
                                'sort_order' => 1,
                            ],
                            [
                                'name' => 'Vidui',
                                'slug' => 'juostos-vidui',
                                'description' => 'Juostos vidaus darbams',
                                'sort_order' => 2,
                            ],
                        ],
                    ],
                    [
                        'name' => 'Plėvelės',
                        'slug' => 'pleveles',
                        'description' => 'Sandarinimo plėvelės',
                        'sort_order' => 2,
                        'children' => [
                            [
                                'name' => 'Vidui',
                                'slug' => 'pleveles-vidui',
                                'description' => 'Plėvelės vidaus darbams',
                                'sort_order' => 1,
                            ],
                            [
                                'name' => 'Laukui',
                                'slug' => 'pleveles-laukui',
                                'description' => 'Plėvelės lauko darbams',
                                'sort_order' => 2,
                            ],
                        ],
                    ],
                    [
                        'name' => 'Pamatų hidroizoliacija',
                        'slug' => 'pamatu-hidroizoliacija',
                        'description' => 'Pamatų hidroizoliacijos medžiagos',
                        'sort_order' => 3,
                        'children' => [
                            [
                                'name' => 'Horizontali',
                                'slug' => 'horizontali',
                                'description' => 'Horizontalioji hidroizoliacija',
                                'sort_order' => 1,
                            ],
                            [
                                'name' => 'Vertikali',
                                'slug' => 'vertikali',
                                'description' => 'Vertikalioji hidroizoliacija',
                                'sort_order' => 2,
                            ],
                        ],
                    ],
                    [
                        'name' => 'Vinių sandarinimo juostos',
                        'slug' => 'vinu-sandarinimo-juostos',
                        'description' => 'Vinių sandarinimo juostos',
                        'sort_order' => 4,
                    ],
                    [
                        'name' => 'Kitos sandarinimo medžiagos ir priedai',
                        'slug' => 'kitos-sandarinimo-medziagos-ir-priedai',
                        'description' => 'Kitos sandarinimo medžiagos ir priedai',
                        'sort_order' => 5,
                    ],
                ],
            ],
            [
                'name' => 'Tvirtinimo elementai, varžtai, medvarsčiai',
                'slug' => 'tvirtinimo-elementai-varztai-medvarsciai',
                'description' => 'Tvirtinimo elementai, varžtai, medvarsčiai',
                'sort_order' => 2,
                'children' => [
                    [
                        'name' => 'Medvarsčiai',
                        'slug' => 'medvarsciai',
                        'description' => 'Medvarsčiai ir tvirtinimo elementai',
                        'sort_order' => 1,
                        'children' => [
                            [
                                'name' => 'Įleidžiama galvute',
                                'slug' => 'ileidziama-galvute',
                                'description' => 'Medvarsčiai su įleidžiama galvute',
                                'sort_order' => 1,
                            ],
                            [
                                'name' => 'Plokščia galvute',
                                'slug' => 'ploksia-galvute',
                                'description' => 'Medvarsčiai su plokščia galvute',
                                'sort_order' => 2,
                            ],
                            [
                                'name' => 'Pusapvale galvute',
                                'slug' => 'pusapvale-galvute',
                                'description' => 'Medvarsčiai su pusapvale galvute',
                                'sort_order' => 3,
                            ],
                            [
                                'name' => 'Šešiakampe galvute',
                                'slug' => 'sesiakampe-galvute',
                                'description' => 'Medvarsčiai su šešiakampe galvute',
                                'sort_order' => 4,
                            ],
                            [
                                'name' => 'Konstrukciniai',
                                'slug' => 'konstrukciniai',
                                'description' => 'Konstrukciniai medvarsčiai',
                                'sort_order' => 5,
                            ],
                            [
                                'name' => 'Terasiniai',
                                'slug' => 'terasiniai',
                                'description' => 'Terasiniai medvarsčiai',
                                'sort_order' => 6,
                            ],
                            [
                                'name' => 'Stoginiai',
                                'slug' => 'stoginiai',
                                'description' => 'Stoginiai medvarsčiai',
                                'sort_order' => 7,
                            ],
                            [
                                'name' => 'Savigręžiai',
                                'slug' => 'savigreziai',
                                'description' => 'Savigręžiai medvarsčiai',
                                'sort_order' => 8,
                            ],
                            [
                                'name' => 'Reguliuojami',
                                'slug' => 'reguliuojami',
                                'description' => 'Reguliuojami medvarsčiai',
                                'sort_order' => 9,
                            ],
                        ],
                    ],
                    [
                        'name' => 'Tvirtinimas į betoną, mūrą',
                        'slug' => 'tvirtinimas-i-betona-mura',
                        'description' => 'Tvirtinimo elementai į betoną ir mūrą',
                        'sort_order' => 2,
                        'children' => [
                            [
                                'name' => 'Su plastikiniu kaiščiu',
                                'slug' => 'su-plastikiniu-kaisciu',
                                'description' => 'Tvirtinimas su plastikiniu kaiščiu',
                                'sort_order' => 1,
                            ],
                            [
                                'name' => 'Su nailoniniu kaiščiu',
                                'slug' => 'su-nailoniniu-kaisciu',
                                'description' => 'Tvirtinimas su nailoniniu kaiščiu',
                                'sort_order' => 2,
                            ],
                            [
                                'name' => 'Inkariniai',
                                'slug' => 'inkariniai',
                                'description' => 'Inkariniai tvirtinimo elementai',
                                'sort_order' => 3,
                            ],
                            [
                                'name' => 'Sraigtai į betoną',
                                'slug' => 'sraigtai-i-betona',
                                'description' => 'Sraigtai į betoną',
                                'sort_order' => 4,
                            ],
                            [
                                'name' => 'Sraigtai į mūrą',
                                'slug' => 'sraigtai-i-mura',
                                'description' => 'Sraigtai į mūrą',
                                'sort_order' => 5,
                            ],
                            [
                                'name' => 'Įbetonuojami ankeriai, sriegiai, detalės',
                                'slug' => 'ibetonuojami-ankeriai-sriegiai-detales',
                                'description' => 'Įbetonuojami ankeriai, sriegiai, detalės',
                                'sort_order' => 6,
                            ],
                            [
                                'name' => 'Greitvinės',
                                'slug' => 'greitvines',
                                'description' => 'Greitvinės tvirtinimo elementai',
                                'sort_order' => 7,
                            ],
                        ],
                    ],
                    [
                        'name' => 'Medžio konstrukcijų tvirtinimas',
                        'slug' => 'medzio-konstrukciju-tvirtinimas',
                        'description' => 'Medžio konstrukcijų tvirtinimo elementai',
                        'sort_order' => 3,
                        'children' => [
                            [
                                'name' => 'Plokštelės',
                                'slug' => 'ploksteles',
                                'description' => 'Tvirtinimo plokštelės',
                                'sort_order' => 1,
                            ],
                            [
                                'name' => 'Kampai',
                                'slug' => 'kampai',
                                'description' => 'Tvirtinimo kampai',
                                'sort_order' => 2,
                            ],
                            [
                                'name' => 'Sijų atrama',
                                'slug' => 'siju-atrama',
                                'description' => 'Sijų atramos',
                                'sort_order' => 3,
                            ],
                            [
                                'name' => 'Gegnių sujungimai',
                                'slug' => 'gegnu-sujungimai',
                                'description' => 'Gegnių sujungimo elementai',
                                'sort_order' => 4,
                            ],
                            [
                                'name' => 'Paslėptas tvirtinimas',
                                'slug' => 'pasletas-tvirtinimas',
                                'description' => 'Paslėpto tvirtinimo elementai',
                                'sort_order' => 5,
                            ],
                            [
                                'name' => 'Kolonų atramos (reguliuojamos, įbetonuojamos)',
                                'slug' => 'kolonu-atramos',
                                'description' => 'Kolonų atramos',
                                'sort_order' => 6,
                            ],
                            [
                                'name' => 'Kita',
                                'slug' => 'kita-medzio-tvirtinimas',
                                'description' => 'Kiti medžio tvirtinimo elementai',
                                'sort_order' => 7,
                            ],
                        ],
                    ],
                    [
                        'name' => 'Sriegiai, varžtai, veržlės, poveržlės',
                        'slug' => 'sriegiai-varztai-verzles-poverzles',
                        'description' => 'Sriegiai, varžtai, veržlės, poveržlės',
                        'sort_order' => 4,
                    ],
                    [
                        'name' => 'Kniedės',
                        'slug' => 'kniedes',
                        'description' => 'Kniedės ir kniedikliai',
                        'sort_order' => 5,
                    ],
                    [
                        'name' => 'Vinys, kabės',
                        'slug' => 'vinys-kabes',
                        'description' => 'Vinys ir kabės',
                        'sort_order' => 6,
                        'children' => [
                            [
                                'name' => 'Palaidos',
                                'slug' => 'palaidos',
                                'description' => 'Palaidos vinys',
                                'sort_order' => 1,
                            ],
                            [
                                'name' => 'Pistoletinės',
                                'slug' => 'pistoletines',
                                'description' => 'Pistoletinės vinys',
                                'sort_order' => 2,
                            ],
                        ],
                    ],
                    [
                        'name' => 'Lengvas tvirtinimas',
                        'slug' => 'lengvas-tvirtinimas',
                        'description' => 'Lengvo tvirtinimo elementai',
                        'sort_order' => 7,
                        'children' => [
                            [
                                'name' => 'Gipsui',
                                'slug' => 'gipsui',
                                'description' => 'Tvirtinimas gipsui',
                                'sort_order' => 1,
                            ],
                            [
                                'name' => 'Į termoizoliaciją',
                                'slug' => 'i-termoizoliacija',
                                'description' => 'Tvirtinimas į termoizoliaciją',
                                'sort_order' => 2,
                            ],
                            [
                                'name' => 'Laikikliai ir kt.',
                                'slug' => 'laikikliai-ir-kt',
                                'description' => 'Laikikliai ir kiti elementai',
                                'sort_order' => 3,
                            ],
                            [
                                'name' => 'Kita',
                                'slug' => 'kita-lengvas-tvirtinimas',
                                'description' => 'Kiti lengvo tvirtinimo elementai',
                                'sort_order' => 4,
                            ],
                        ],
                    ],
                    [
                        'name' => 'Tvirtinimo juostos',
                        'slug' => 'tvirtinimo-juostos',
                        'description' => 'Tvirtinimo juostos',
                        'sort_order' => 8,
                    ],
                    [
                        'name' => 'Išlyginimo kaladėlė, kyliai ir kt.',
                        'slug' => 'islyginimo-kaladele-kyliai-ir-kt',
                        'description' => 'Išlyginimo kaladėlė, kyliai ir kiti elementai',
                        'sort_order' => 9,
                    ],
                    [
                        'name' => 'Termoizoliaciniai kaiščiai',
                        'slug' => 'termoizoliaciniai-kaisiai',
                        'description' => 'Termoizoliaciniai kaiščiai',
                        'sort_order' => 10,
                    ],
                ],
            ],
            [
                'name' => 'Įsukami, įkalami poliai',
                'slug' => 'isukami-ikalami-poliai',
                'description' => 'Įsukami, įkalami poliai',
                'sort_order' => 3,
                'children' => [
                    [
                        'name' => 'HDG',
                        'slug' => 'hdg',
                        'description' => 'HDG poliai',
                        'sort_order' => 1,
                    ],
                    [
                        'name' => 'HEX HDG',
                        'slug' => 'hex-hdg',
                        'description' => 'HEX HDG poliai',
                        'sort_order' => 2,
                    ],
                ],
            ],
            [
                'name' => 'Chemija statyboms',
                'slug' => 'chemija-statyboms',
                'description' => 'Chemija statyboms',
                'sort_order' => 4,
                'children' => [
                    [
                        'name' => 'Klijuojančios putos',
                        'slug' => 'klijuojancios-putos',
                        'description' => 'Klijuojančios putos',
                        'sort_order' => 1,
                    ],
                    [
                        'name' => 'Montavimo putos',
                        'slug' => 'montavimo-putos',
                        'description' => 'Montavimo putos',
                        'sort_order' => 2,
                    ],
                    [
                        'name' => 'Purškiama termoizoliacija',
                        'slug' => 'purskiama-termoizoliacija',
                        'description' => 'Purškiama termoizoliacija',
                        'sort_order' => 3,
                    ],
                    [
                        'name' => 'Klijai',
                        'slug' => 'klijai',
                        'description' => 'Statybų klijai',
                        'sort_order' => 4,
                    ],
                    [
                        'name' => 'Hermetikai',
                        'slug' => 'hermetikai',
                        'description' => 'Hermetikai',
                        'sort_order' => 5,
                    ],
                    [
                        'name' => 'Silikonai',
                        'slug' => 'silikonai',
                        'description' => 'Silikonai',
                        'sort_order' => 6,
                    ],
                    [
                        'name' => 'Akrilai',
                        'slug' => 'akrilai',
                        'description' => 'Akrilai',
                        'sort_order' => 7,
                    ],
                    [
                        'name' => 'Sandarinimo mastikos',
                        'slug' => 'sandarinimo-mastikos',
                        'description' => 'Sandarinimo mastikos',
                        'sort_order' => 8,
                    ],
                    [
                        'name' => 'Tepama hidroizoliacija',
                        'slug' => 'tepama-hidroizoliacija',
                        'description' => 'Tepama hidroizoliacija',
                        'sort_order' => 9,
                    ],
                    [
                        'name' => 'Putų ir kiti valikliai',
                        'slug' => 'putu-ir-kiti-valikliai',
                        'description' => 'Putų ir kiti valikliai',
                        'sort_order' => 10,
                    ],
                    [
                        'name' => 'Dažai',
                        'slug' => 'dazai',
                        'description' => 'Statybų dažai',
                        'sort_order' => 11,
                    ],
                    [
                        'name' => 'Kita',
                        'slug' => 'kita-chemija',
                        'description' => 'Kita chemija statyboms',
                        'sort_order' => 12,
                    ],
                ],
            ],
            [
                'name' => 'Įrankiai ir jų priedai',
                'slug' => 'irankiai-ir-ju-priedai',
                'description' => 'Įrankiai ir jų priedai',
                'sort_order' => 5,
                'children' => [
                    [
                        'name' => 'Pieštukai',
                        'slug' => 'piestukai',
                        'description' => 'Pieštukai',
                        'sort_order' => 1,
                    ],
                    [
                        'name' => 'Matavimo',
                        'slug' => 'matavimo',
                        'description' => 'Matavimo įrankiai',
                        'sort_order' => 2,
                    ],
                    [
                        'name' => 'Armatūrai rišti',
                        'slug' => 'armaturai-risi',
                        'description' => 'Armatūrai rišti įrankiai',
                        'sort_order' => 3,
                    ],
                    [
                        'name' => 'EPDM darbui skirti įrankiai',
                        'slug' => 'epdm-darbui-skirti-irankiai',
                        'description' => 'EPDM darbui skirti įrankiai',
                        'sort_order' => 4,
                    ],
                    [
                        'name' => 'Grąžtai metalui',
                        'slug' => 'graztai-metalui',
                        'description' => 'Grąžtai metalui',
                        'sort_order' => 5,
                    ],
                    [
                        'name' => 'Grąžtai medžiui',
                        'slug' => 'graztai-medziui',
                        'description' => 'Grąžtai medžiui',
                        'sort_order' => 6,
                    ],
                    [
                        'name' => 'Plaktukai',
                        'slug' => 'plaktukai',
                        'description' => 'Plaktukai',
                        'sort_order' => 7,
                    ],
                    [
                        'name' => 'Lankstytuvai, žirklės skardai',
                        'slug' => 'lankstytuvai-zirkles-skardai',
                        'description' => 'Lankstytuvai, žirklės skardai',
                        'sort_order' => 8,
                    ],
                    [
                        'name' => 'Kniedikliai',
                        'slug' => 'kniedikliai',
                        'description' => 'Kniedikliai',
                        'sort_order' => 9,
                    ],
                    [
                        'name' => 'Atsuktuvai',
                        'slug' => 'atsuktuvai',
                        'description' => 'Atsuktuvai',
                        'sort_order' => 10,
                    ],
                    [
                        'name' => 'Replės',
                        'slug' => 'reples',
                        'description' => 'Replės',
                        'sort_order' => 11,
                    ],
                    [
                        'name' => 'Pjūklai',
                        'slug' => 'pjukai',
                        'description' => 'Pjūklai',
                        'sort_order' => 12,
                    ],
                    [
                        'name' => 'Laužtuvai',
                        'slug' => 'lauztuvai',
                        'description' => 'Laužtuvai',
                        'sort_order' => 13,
                    ],
                    [
                        'name' => 'Kita',
                        'slug' => 'kita-irankiai',
                        'description' => 'Kiti įrankiai',
                        'sort_order' => 14,
                    ],
                ],
            ],
            [
                'name' => 'Stogų danga ir priedai',
                'slug' => 'stogu-danga-ir-priedai',
                'description' => 'Stogų danga ir priedai',
                'sort_order' => 6,
                'children' => [
                    [
                        'name' => 'EPDM medžiaga ir įrankiai montavimui',
                        'slug' => 'epdm-medziaga-ir-irankiai-montavimui',
                        'description' => 'EPDM medžiaga ir įrankiai montavimui',
                        'sort_order' => 1,
                    ],
                    [
                        'name' => 'Stogų priedai',
                        'slug' => 'stogu-priedai',
                        'description' => 'Stogų priedai',
                        'sort_order' => 2,
                        'children' => [
                            [
                                'name' => 'Ventiliuojami profiliai',
                                'slug' => 'ventiliuojami-profiliai',
                                'description' => 'Ventiliuojami profiliai',
                                'sort_order' => 1,
                            ],
                            [
                                'name' => 'Paukščių užtvaros',
                                'slug' => 'pauksciu-uztvaros',
                                'description' => 'Paukščių užtvaros',
                                'sort_order' => 2,
                            ],
                            [
                                'name' => 'Latakų apsauga',
                                'slug' => 'lataku-apsauga',
                                'description' => 'Latakų apsauga',
                                'sort_order' => 3,
                            ],
                            [
                                'name' => 'Kraigo tarpinės',
                                'slug' => 'kraigo-tarpines',
                                'description' => 'Kraigo tarpinės',
                                'sort_order' => 4,
                            ],
                            [
                                'name' => 'Vinių sandarinimo juostos',
                                'slug' => 'vinu-sandarinimo-juostos-stogu',
                                'description' => 'Vinių sandarinimo juostos stogams',
                                'sort_order' => 5,
                            ],
                            [
                                'name' => 'Kiti priedai',
                                'slug' => 'kiti-priedai-stogu',
                                'description' => 'Kiti stogų priedai',
                                'sort_order' => 6,
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Fasadams',
                'slug' => 'fasadams',
                'description' => 'Fasadams skirtos medžiagos',
                'sort_order' => 7,
                'children' => [
                    [
                        'name' => 'Ventiliuojami fasadai',
                        'slug' => 'ventiliuojami-fasadai',
                        'description' => 'Ventiliuojami fasadai',
                        'sort_order' => 1,
                        'children' => [
                            [
                                'name' => 'Akmenys, stount',
                                'slug' => 'akmenys-stount',
                                'description' => 'Akmenys, stount',
                                'sort_order' => 1,
                            ],
                            [
                                'name' => 'Priedai fasadams',
                                'slug' => 'priedai-fasadams',
                                'description' => 'Priedai fasadams',
                                'sort_order' => 2,
                            ],
                            [
                                'name' => 'WPC dailylentės',
                                'slug' => 'wpc-dailylentes',
                                'description' => 'WPC dailylentės',
                                'sort_order' => 3,
                            ],
                        ],
                    ],
                    [
                        'name' => 'Kiti fasadai',
                        'slug' => 'kiti-fasadai',
                        'description' => 'Kiti fasadai',
                        'sort_order' => 2,
                        'children' => [
                            [
                                'name' => 'Akmens apdaila',
                                'slug' => 'akmens-apdaila',
                                'description' => 'Akmens apdaila',
                                'sort_order' => 1,
                            ],
                            [
                                'name' => 'Klinkerio apdaila',
                                'slug' => 'klinkerio-apdaila',
                                'description' => 'Klinkerio apdaila',
                                'sort_order' => 2,
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Elektros prekės',
                'slug' => 'elektros-prekes',
                'description' => 'Elektros prekės',
                'sort_order' => 8,
                'children' => [
                    [
                        'name' => 'Izoliacinės juostos',
                        'slug' => 'izoliacines-juostos',
                        'description' => 'Izoliacinės juostos',
                        'sort_order' => 1,
                    ],
                    [
                        'name' => 'Laidų sujungimas',
                        'slug' => 'laidu-sujungimas',
                        'description' => 'Laidų sujungimo elementai',
                        'sort_order' => 2,
                    ],
                    [
                        'name' => 'Elektros instaliacijos produktai',
                        'slug' => 'elektros-instaliacijos-produktai',
                        'description' => 'Elektros instaliacijos produktai',
                        'sort_order' => 3,
                        'children' => [
                            [
                                'name' => 'EGANT instaliacijos sistema',
                                'slug' => 'egant-instaliacijos-sistema',
                                'description' => 'EGANT instaliacijos sistema',
                                'sort_order' => 1,
                            ],
                        ],
                    ],
                    [
                        'name' => 'Laidų tvirtinimas',
                        'slug' => 'laidu-tvirtinimas',
                        'description' => 'Laidų tvirtinimo elementai',
                        'sort_order' => 4,
                    ],
                    [
                        'name' => 'Kita',
                        'slug' => 'kita-elektros',
                        'description' => 'Kitos elektros prekės',
                        'sort_order' => 5,
                    ],
                ],
            ],
            [
                'name' => 'Darbo apranga, saugos priemonės',
                'slug' => 'darbo-apranga-saugos-priemones',
                'description' => 'Darbo apranga ir saugos priemonės',
                'sort_order' => 9,
                'children' => [
                    [
                        'name' => 'Kelnės',
                        'slug' => 'kelnes',
                        'description' => 'Darbo kelnės',
                        'sort_order' => 1,
                    ],
                    [
                        'name' => 'Džemperiai',
                        'slug' => 'dzemperiai',
                        'description' => 'Darbo džemperiai',
                        'sort_order' => 2,
                    ],
                    [
                        'name' => 'Striukės',
                        'slug' => 'striukes',
                        'description' => 'Darbo striukės',
                        'sort_order' => 3,
                    ],
                    [
                        'name' => 'Kepurės',
                        'slug' => 'kepures',
                        'description' => 'Darbo kepurės',
                        'sort_order' => 4,
                    ],
                    [
                        'name' => 'Pirštinės',
                        'slug' => 'pirstines',
                        'description' => 'Darbo pirštinės',
                        'sort_order' => 5,
                    ],
                    [
                        'name' => 'Kojinės',
                        'slug' => 'kojines',
                        'description' => 'Darbo kojinės',
                        'sort_order' => 6,
                    ],
                    [
                        'name' => 'Batai',
                        'slug' => 'batai',
                        'description' => 'Darbo batai',
                        'sort_order' => 7,
                    ],
                    [
                        'name' => 'Akiniai',
                        'slug' => 'akiniai',
                        'description' => 'Apsaugos akiniai',
                        'sort_order' => 8,
                    ],
                    [
                        'name' => 'Kelių apsauga',
                        'slug' => 'keliu-apsauga',
                        'description' => 'Kelių apsaugos priemonės',
                        'sort_order' => 9,
                    ],
                    [
                        'name' => 'Ausų apsauga',
                        'slug' => 'ausu-apsauga',
                        'description' => 'Ausų apsaugos priemonės',
                        'sort_order' => 10,
                    ],
                    [
                        'name' => 'Kita',
                        'slug' => 'kita-saugos',
                        'description' => 'Kitos saugos priemonės',
                        'sort_order' => 11,
                    ],
                ],
            ],
            [
                'name' => 'Stogų, grindų, sienų konstrukcijos',
                'slug' => 'stogu-grindu-sienu-konstrukcijos',
                'description' => 'Stogų, grindų, sienų konstrukcijos',
                'sort_order' => 10,
                'children' => [
                    [
                        'name' => 'Dvitejinės ir klijuotos fanieros sijos stogams ir sienoms',
                        'slug' => 'dvitejines-ir-klijuotos-fanieros-sijos',
                        'description' => 'Dvitejinės ir klijuotos fanieros sijos stogams ir sienoms',
                        'sort_order' => 1,
                    ],
                    [
                        'name' => 'Grindims ir perdangai',
                        'slug' => 'grindims-ir-perdangai',
                        'description' => 'Grindims ir perdangai skirtos konstrukcijos',
                        'sort_order' => 2,
                    ],
                ],
            ],
            [
                'name' => 'Vidaus apdaila',
                'slug' => 'vidaus-apdaila',
                'description' => 'Vidaus apdailos medžiagos',
                'sort_order' => 11,
                'children' => [
                    [
                        'name' => 'Sienų apdailinės plokštės',
                        'slug' => 'sienu-apdailines-plokstes',
                        'description' => 'Sienų apdailinės plokštės',
                        'sort_order' => 1,
                    ],
                    [
                        'name' => 'Vonios apdailinės plokštės',
                        'slug' => 'vonios-apdailines-plokstes',
                        'description' => 'Vonios apdailinės plokštės',
                        'sort_order' => 2,
                    ],
                    [
                        'name' => 'Virtuvės apdailinės plokštės',
                        'slug' => 'virtuves-apdailines-plokstes',
                        'description' => 'Virtuvės apdailinės plokštės',
                        'sort_order' => 3,
                    ],
                    [
                        'name' => 'Lubų apdaila',
                        'slug' => 'lubu-apdaila',
                        'description' => 'Lubų apdailos medžiagos',
                        'sort_order' => 4,
                    ],
                    [
                        'name' => 'Molio tinkai',
                        'slug' => 'molio-tinkai',
                        'description' => 'Molio tinkai',
                        'sort_order' => 5,
                    ],
                ],
            ],
        ];
    }

    private function createCategory(array $categoryData, ?int $parentId = null): void
    {
        // Check if category already exists to maintain idempotency
        $category = Category::query()->firstWhere('slug', $categoryData['slug']);

        $attributes = [
            'name' => $categoryData['name'],
            'slug' => $categoryData['slug'],
            'description' => $categoryData['description'],
            'parent_id' => $parentId,
            'sort_order' => $categoryData['sort_order'],
            'is_visible' => true,
        ];

        if ($category) {
            $category->update($attributes);
        } else {
            // Use factory to create category with relationships
            $category = Category::factory()
                ->state($attributes)
                ->create();
        }

        $locales = $this->supportedLocales();

        // Create translations using the model's translation relationship
        foreach ($locales as $loc) {
            $name = $this->translateLike($categoryData['name'], $loc);
            $translationData = [
                'name' => $name,
                'slug' => $this->translateSlug($categoryData['slug'], $loc),
                'description' => $this->translateLike($categoryData['description'], $loc),
                'seo_title' => $name,
                'seo_description' => $this->translateLike('Statybinių prekių kategorija.', $loc),
            ];

            $category->updateTranslation($loc, $translationData);
        }

        // Add main image if category was created and doesn't have one
        if ($category && ($category->wasRecentlyCreated || !$category->hasMedia('images')) && isset($categoryData['image_url'])) {
            $this->downloadAndAttachImage($category, $categoryData['image_url'], 'images', $categoryData['name'] . ' Image');
        }

        // Add banner if category was created and doesn't have one
        if ($category && ($category->wasRecentlyCreated || !$category->hasMedia('banner')) && isset($categoryData['banner_url'])) {
            $this->downloadAndAttachImage($category, $categoryData['banner_url'], 'banner', $categoryData['name'] . ' Banner');
        }

        // Create children categories recursively
        if (isset($categoryData['children'])) {
            foreach ($categoryData['children'] as $childData) {
                $this->createCategory($childData, $category->id);
            }
        }
    }

    /**
     * Generate local WebP image and attach it to the category
     */
    private function downloadAndAttachImage(Category $category, string $imageUrl, string $collection, string $name): void
    {
        try {
            // Generate local WebP image instead of downloading
            $imagePath = $this->imageGenerator->generateCategoryImage($category->name);

            if (file_exists($imagePath)) {
                $filename = Str::slug($name) . '.webp';

                // Add media to category
                $category
                    ->addMedia($imagePath)
                    ->withCustomProperties(['source' => 'local_generated'])
                    ->usingName($name)
                    ->usingFileName($filename)
                    ->toMediaCollection($collection);

                // Clean up temporary file
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }

                $this->command->info("✓ Generated {$collection} WebP image for {$category->name}");
            } else {
                $this->command->warn("✗ Failed to generate {$collection} image for {$category->name}");
            }
        } catch (\Exception $e) {
            $this->command->warn("✗ Failed to generate {$collection} image for {$category->name}: " . $e->getMessage());
        }
    }

    private function supportedLocales(): array
    {
        return collect(explode(',', (string) config('app.supported_locales', 'lt')))
            ->map(fn($v) => trim((string) $v))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function translateLike(string $text, string $locale): string
    {
        return match ($locale) {
            'lt' => $text,
            'en' => $this->translateToEnglish($text),
            'ru' => $this->translateToRussian($text),
            'de' => $this->translateToGerman($text),
            default => $text . ' (' . strtoupper($locale) . ')',
        };
    }

    private function translateToEnglish(string $text): string
    {
        $translations = [
            'Sandarinimo plėvelės ir juostos' => 'Sealing films and tapes',
            'Juostos' => 'Tapes',
            'Laukui' => 'For outdoor use',
            'Vidui' => 'For indoor use',
            'Plėvelės' => 'Films',
            'Pamatų hidroizoliacija' => 'Foundation waterproofing',
            'Horizontali' => 'Horizontal',
            'Vertikali' => 'Vertical',
            'Vinių sandarinimo juostos' => 'Nail sealing tapes',
            'Kitos sandarinimo medžiagos ir priedai' => 'Other sealing materials and accessories',
            'Tvirtinimo elementai, varžtai, medvarsčiai' => 'Fastening elements, screws, wood screws',
            'Medvarsčiai' => 'Wood screws',
            'Įleidžiama galvute' => 'Countersunk head',
            'Plokščia galvute' => 'Flat head',
            'Pusapvale galvute' => 'Round head',
            'Šešiakampe galvute' => 'Hex head',
            'Konstrukciniai' => 'Structural',
            'Terasiniai' => 'Deck screws',
            'Stoginiai' => 'Roofing screws',
            'Savigręžiai' => 'Self-tapping',
            'Reguliuojami' => 'Adjustable',
            'Tvirtinimas į betoną, mūrą' => 'Fastening to concrete, masonry',
            'Su plastikiniu kaiščiu' => 'With plastic dowel',
            'Su nailoniniu kaiščiu' => 'With nylon dowel',
            'Inkariniai' => 'Anchor bolts',
            'Sraigtai į betoną' => 'Concrete screws',
            'Sraigtai į mūrą' => 'Masonry screws',
            'Įbetonuojami ankeriai, sriegiai, detalės' => 'Embedded anchors, threads, details',
            'Greitvinės' => 'Quick nails',
            'Medžio konstrukcijų tvirtinimas' => 'Wood construction fastening',
            'Plokštelės' => 'Plates',
            'Kampai' => 'Angles',
            'Sijų atrama' => 'Beam support',
            'Gegnių sujungimai' => 'Rafter connections',
            'Paslėptas tvirtinimas' => 'Hidden fastening',
            'Kolonų atramos (reguliuojamos, įbetonuojamos)' => 'Column supports (adjustable, embedded)',
            'Kita' => 'Other',
            'Sriegiai, varžtai, veržlės, poveržlės' => 'Threads, bolts, nuts, washers',
            'Kniedės' => 'Rivets',
            'Vinys, kabės' => 'Nails, hooks',
            'Palaidos' => 'Hidden',
            'Pistoletinės' => 'Pneumatic',
            'Lengvas tvirtinimas' => 'Light fastening',
            'Gipsui' => 'For gypsum',
            'Į termoizoliaciją' => 'Into thermal insulation',
            'Laikikliai ir kt.' => 'Brackets and others',
            'Tvirtinimo juostos' => 'Fastening tapes',
            'Išlyginimo kaladėlė, kyliai ir kt.' => 'Leveling shims, wedges and others',
            'Termoizoliaciniai kaiščiai' => 'Thermal insulation dowels',
            'Įsukami, įkalami poliai' => 'Screw-in, driven posts',
            'HDG' => 'HDG',
            'HEX HDG' => 'HEX HDG',
            'Chemija statyboms' => 'Chemistry for construction',
            'Klijuojančios putos' => 'Adhesive foams',
            'Montavimo putos' => 'Installation foams',
            'Purškiama termoizoliacija' => 'Spray thermal insulation',
            'Klijai' => 'Adhesives',
            'Hermetikai' => 'Sealants',
            'Silikonai' => 'Silicones',
            'Akrilai' => 'Acrylics',
            'Sandarinimo mastikos' => 'Sealing mastics',
            'Tepama hidroizoliacija' => 'Applied waterproofing',
            'Putų ir kiti valikliai' => 'Foam and other cleaners',
            'Dažai' => 'Paints',
            'Įrankiai ir jų priedai' => 'Tools and accessories',
            'Pieštukai' => 'Pencils',
            'Matavimo' => 'Measuring',
            'Armatūrai rišti' => 'For tying reinforcement',
            'EPDM darbui skirti įrankiai' => 'Tools for EPDM work',
            'Grąžtai metalui' => 'Metal drills',
            'Grąžtai medžiui' => 'Wood drills',
            'Plaktukai' => 'Hammers',
            'Lankstytuvai, žirklės skardai' => 'Benders, sheet metal shears',
            'Kniedikliai' => 'Riveters',
            'Atsuktuvai' => 'Screwdrivers',
            'Replės' => 'Files',
            'Pjūklai' => 'Saws',
            'Laužtuvai' => 'Chisels',
            'Stogų danga ir priedai' => 'Roofing and accessories',
            'EPDM medžiaga ir įrankiai montavimui' => 'EPDM material and installation tools',
            'Stogų priedai' => 'Roofing accessories',
            'Ventiliuojami profiliai' => 'Ventilated profiles',
            'Paukščių užtvaros' => 'Bird barriers',
            'Latakų apsauga' => 'Gutter protection',
            'Kraigo tarpinės' => 'Ridge spacers',
            'Fasadams' => 'For facades',
            'Ventiliuojami fasadai' => 'Ventilated facades',
            'Akmenys, stount' => 'Stones, stount',
            'Priedai fasadams' => 'Facade accessories',
            'WPC dailylentės' => 'WPC cladding boards',
            'Kiti fasadai' => 'Other facades',
            'Akmens apdaila' => 'Stone cladding',
            'Klinkerio apdaila' => 'Clinker cladding',
            'Elektros prekės' => 'Electrical products',
            'Izoliacinės juostos' => 'Insulating tapes',
            'Laidų sujungimas' => 'Cable connection',
            'Elektros instaliacijos produktai' => 'Electrical installation products',
            'EGANT instaliacijos sistema' => 'EGANT installation system',
            'Laidų tvirtinimas' => 'Cable fastening',
            'Darbo apranga, saugos priemonės' => 'Work clothing, safety equipment',
            'Kelnės' => 'Trousers',
            'Džemperiai' => 'Jumpers',
            'Striukės' => 'Jackets',
            'Kepurės' => 'Hats',
            'Pirštinės' => 'Gloves',
            'Kojinės' => 'Socks',
            'Batai' => 'Shoes',
            'Akiniai' => 'Glasses',
            'Kelių apsauga' => 'Knee protection',
            'Ausų apsauga' => 'Hearing protection',
            'Stogų, grindų, sienų konstrukcijos' => 'Roof, floor, wall structures',
            'Dvitejinės ir klijuotos fanieros sijos stogams ir sienoms' => 'Double and glued veneer beams for roofs and walls',
            'Grindims ir perdangai' => 'For floors and ceilings',
            'Vidaus apdaila' => 'Interior finishing',
            'Sienų apdailinės plokštės' => 'Wall finishing boards',
            'Vonios apdailinės plokštės' => 'Bathroom finishing boards',
            'Virtuvės apdailinės plokštės' => 'Kitchen finishing boards',
            'Lubų apdaila' => 'Ceiling finishing',
            'Molio tinkai' => 'Clay plasters',
        ];

        return $translations[$text] ?? $text;
    }

    private function translateToRussian(string $text): string
    {
        $translations = [
            'Sandarinimo plėvelės ir juostos' => 'Герметизирующие пленки и ленты',
            'Juostos' => 'Ленты',
            'Laukui' => 'Для наружного применения',
            'Vidui' => 'Для внутреннего применения',
            'Plėvelės' => 'Пленки',
            'Pamatų hidroizoliacija' => 'Гидроизоляция фундамента',
            'Horizontali' => 'Горизонтальная',
            'Vertikali' => 'Вертикальная',
            'Vinių sandarinimo juostos' => 'Герметизирующие ленты для гвоздей',
            'Kitos sandarinimo medžiagos ir priedai' => 'Другие герметизирующие материалы и аксессуары',
            'Tvirtinimo elementai, varžtai, medvarsčiai' => 'Крепежные элементы, винты, саморезы',
            'Medvarsčiai' => 'Саморезы',
            'Įleidžiama galvute' => 'С потайной головкой',
            'Plokščia galvute' => 'С плоской головкой',
            'Pusapvale galvute' => 'С полукруглой головкой',
            'Šešiakampe galvute' => 'С шестигранной головкой',
            'Konstrukciniai' => 'Конструкционные',
            'Terasiniai' => 'Террасные',
            'Stoginiai' => 'Кровельные',
            'Savigręžiai' => 'Саморезы',
            'Reguliuojami' => 'Регулируемые',
            'Tvirtinimas į betoną, mūrą' => 'Крепление к бетону, кладке',
            'Su plastikiniu kaiščiu' => 'С пластиковым дюбелем',
            'Su nailoniniu kaiščiu' => 'С нейлоновым дюбелем',
            'Inkariniai' => 'Анкерные болты',
            'Sraigtai į betoną' => 'Винты для бетона',
            'Sraigtai į mūrą' => 'Винты для кладки',
            'Įbetonuojami ankeriai, sriegiai, detalės' => 'Встраиваемые анкеры, резьбы, детали',
            'Greitvinės' => 'Быстрые гвозди',
            'Medžio konstrukcijų tvirtinimas' => 'Крепление деревянных конструкций',
            'Plokštelės' => 'Пластины',
            'Kampai' => 'Углы',
            'Sijų atrama' => 'Опора балок',
            'Gegnių sujungimai' => 'Соединения стропил',
            'Paslėptas tvirtinimas' => 'Скрытое крепление',
            'Kolonų atramos (reguliuojamos, įbetonuojamos)' => 'Опора колонн (регулируемые, встраиваемые)',
            'Kita' => 'Другое',
            'Sriegiai, varžtai, veržlės, poveržlės' => 'Резьбы, болты, гайки, шайбы',
            'Kniedės' => 'Заклепки',
            'Vinys, kabės' => 'Гвозди, крюки',
            'Palaidos' => 'Скрытые',
            'Pistoletinės' => 'Пневматические',
            'Lengvas tvirtinimas' => 'Легкое крепление',
            'Gipsui' => 'Для гипса',
            'Į termoizoliaciją' => 'В теплоизоляцию',
            'Laikikliai ir kt.' => 'Кронштейны и др.',
            'Tvirtinimo juostos' => 'Крепежные ленты',
            'Išlyginimo kaladėlė, kyliai ir kt.' => 'Выравнивающие прокладки, клинья и др.',
            'Termoizoliaciniai kaiščiai' => 'Теплоизоляционные дюбели',
            'Įsukami, įkalami poliai' => 'Ввинчиваемые, забиваемые столбы',
            'HDG' => 'HDG',
            'HEX HDG' => 'HEX HDG',
            'Chemija statyboms' => 'Химия для строительства',
            'Klijuojančios putos' => 'Клеящие пены',
            'Montavimo putos' => 'Монтажные пены',
            'Purškiama termoizoliacija' => 'Напыляемая теплоизоляция',
            'Klijai' => 'Клеи',
            'Hermetikai' => 'Герметики',
            'Silikonai' => 'Силиконы',
            'Akrilai' => 'Акрилы',
            'Sandarinimo mastikos' => 'Герметизирующие мастики',
            'Tepama hidroizoliacija' => 'Обмазочная гидроизоляция',
            'Putų ir kiti valikliai' => 'Пены и другие очистители',
            'Dažai' => 'Краски',
            'Įrankiai ir jų priedai' => 'Инструменты и принадлежности',
            'Pieštukai' => 'Карандаши',
            'Matavimo' => 'Измерительные',
            'Armatūrai rišti' => 'Для вязки арматуры',
            'EPDM darbui skirti įrankiai' => 'Инструменты для работы с EPDM',
            'Grąžtai metalui' => 'Сверла по металлу',
            'Grąžtai medžiui' => 'Сверла по дереву',
            'Plaktukai' => 'Молотки',
            'Lankstytuvai, žirklės skardai' => 'Гибщики, ножницы по металлу',
            'Kniedikliai' => 'Заклепочники',
            'Atsuktuvai' => 'Отвертки',
            'Replės' => 'Напильники',
            'Pjūklai' => 'Пилы',
            'Laužtuvai' => 'Зубила',
            'Stogų danga ir priedai' => 'Кровельные материалы и аксессуары',
            'EPDM medžiaga ir įrankiai montavimui' => 'EPDM материал и инструменты для монтажа',
            'Stogų priedai' => 'Кровельные аксессуары',
            'Ventiliuojami profiliai' => 'Вентилируемые профили',
            'Paukščių užtvaros' => 'Защита от птиц',
            'Latakų apsauga' => 'Защита желобов',
            'Kraigo tarpinės' => 'Коньковые прокладки',
            'Fasadams' => 'Для фасадов',
            'Ventiliuojami fasadai' => 'Вентилируемые фасады',
            'Akmenys, stount' => 'Камни, stount',
            'Priedai fasadams' => 'Фасадные аксессуары',
            'WPC dailylentės' => 'WPC облицовочные доски',
            'Kiti fasadai' => 'Другие фасады',
            'Akmens apdaila' => 'Каменная облицовка',
            'Klinkerio apdaila' => 'Клинкерная облицовка',
            'Elektros prekės' => 'Электротовары',
            'Izoliacinės juostos' => 'Изоляционные ленты',
            'Laidų sujungimas' => 'Соединение кабелей',
            'Elektros instaliacijos produktai' => 'Продукты для электромонтажа',
            'EGANT instaliacijos sistema' => 'EGANT система установки',
            'Laidų tvirtinimas' => 'Крепление кабелей',
            'Darbo apranga, saugos priemonės' => 'Рабочая одежда, средства защиты',
            'Kelnės' => 'Брюки',
            'Džemperiai' => 'Джемперы',
            'Striukės' => 'Куртки',
            'Kepurės' => 'Шапки',
            'Pirštinės' => 'Перчатки',
            'Kojinės' => 'Носки',
            'Batai' => 'Обувь',
            'Akiniai' => 'Очки',
            'Kelių apsauga' => 'Защита коленей',
            'Ausų apsauga' => 'Защита слуха',
            'Stogų, grindų, sienų konstrukcijos' => 'Конструкции крыш, полов, стен',
            'Dvitejinės ir klijuotos fanieros sijos stogams ir sienoms' => 'Двутавровые и клееные фанерные балки для крыш и стен',
            'Grindims ir perdangai' => 'Для полов и перекрытий',
            'Vidaus apdaila' => 'Внутренняя отделка',
            'Sienų apdailinės plokštės' => 'Отделочные плиты для стен',
            'Vonios apdailinės plokštės' => 'Отделочные плиты для ванной',
            'Virtuvės apdailinės plokštės' => 'Отделочные плиты для кухни',
            'Lubų apdaila' => 'Отделка потолков',
            'Molio tinkai' => 'Глиняные штукатурки',
        ];

        return $translations[$text] ?? $text;
    }

    private function translateToGerman(string $text): string
    {
        $translations = [
            'Sandarinimo plėvelės ir juostos' => 'Dichtungsfolien und Bänder',
            'Juostos' => 'Bänder',
            'Laukui' => 'Für Außenbereich',
            'Vidui' => 'Für Innenbereich',
            'Plėvelės' => 'Folien',
            'Pamatų hidroizoliacija' => 'Fundamentabdichtung',
            'Horizontali' => 'Horizontal',
            'Vertikali' => 'Vertikal',
            'Vinių sandarinimo juostos' => 'Nageldichtungsbänder',
            'Kitos sandarinimo medžiagos ir priedai' => 'Andere Dichtungsmaterialien und Zubehör',
            'Tvirtinimo elementai, varžtai, medvarsčiai' => 'Befestigungselemente, Schrauben, Holzschrauben',
            'Medvarsčiai' => 'Holzschrauben',
            'Įleidžiama galvute' => 'Senkkopf',
            'Plokščia galvute' => 'Flachkopf',
            'Pusapvale galvute' => 'Halbrundkopf',
            'Šešiakampe galvute' => 'Sechskantkopf',
            'Konstrukciniai' => 'Konstruktionsschrauben',
            'Terasiniai' => 'Terrassenschrauben',
            'Stoginiai' => 'Dachschrauben',
            'Savigręžiai' => 'Selbstschneidend',
            'Reguliuojami' => 'Verstellbar',
            'Tvirtinimas į betoną, mūrą' => 'Befestigung in Beton, Mauerwerk',
            'Su plastikiniu kaiščiu' => 'Mit Kunststoffdübel',
            'Su nailoniniu kaiščiu' => 'Mit Nylondübel',
            'Inkariniai' => 'Ankerbolzen',
            'Sraigtai į betoną' => 'Betonschrauben',
            'Sraigtai į mūrą' => 'Mauerwerksschrauben',
            'Įbetonuojami ankeriai, sriegiai, detalės' => 'Einbetonierte Anker, Gewinde, Details',
            'Greitvinės' => 'Schnellnägel',
            'Medžio konstrukcijų tvirtinimas' => 'Holzkonstruktionsbefestigung',
            'Plokštelės' => 'Platten',
            'Kampai' => 'Winkel',
            'Sijų atrama' => 'Balkenstütze',
            'Gegnių sujungimai' => 'Sparrenverbindungen',
            'Paslėptas tvirtinimas' => 'Versteckte Befestigung',
            'Kolonų atramos (reguliuojamos, įbetonuojamos)' => 'Säulenstützen (verstellbar, einbetoniert)',
            'Kita' => 'Andere',
            'Sriegiai, varžtai, veržlės, poveržlės' => 'Gewinde, Bolzen, Muttern, Unterlegscheiben',
            'Kniedės' => 'Nieten',
            'Vinys, kabės' => 'Nägel, Haken',
            'Palaidos' => 'Versteckt',
            'Pistoletinės' => 'Pneumatisch',
            'Lengvas tvirtinimas' => 'Leichte Befestigung',
            'Gipsui' => 'Für Gips',
            'Į termoizoliaciją' => 'In Wärmedämmung',
            'Laikikliai ir kt.' => 'Halter und andere',
            'Tvirtinimo juostos' => 'Befestigungsbänder',
            'Išlyginimo kaladėlė, kyliai ir kt.' => 'Ausgleichskeile, Keile und andere',
            'Termoizoliaciniai kaiščiai' => 'Wärmedämmungsdübel',
            'Įsukami, įkalami poliai' => 'Einschraub-, Einschlagpfähle',
            'HDG' => 'HDG',
            'HEX HDG' => 'HEX HDG',
            'Chemija statyboms' => 'Chemie für Bauwesen',
            'Klijuojančios putos' => 'Klebschäume',
            'Montavimo putos' => 'Montageschäume',
            'Purškiama termoizoliacija' => 'Spraydämmung',
            'Klijai' => 'Klebstoffe',
            'Hermetikai' => 'Dichtstoffe',
            'Silikonai' => 'Silikone',
            'Akrilai' => 'Acrylate',
            'Sandarinimo mastikos' => 'Dichtungsmassen',
            'Tepama hidroizoliacija' => 'Aufgetragene Abdichtung',
            'Putų ir kiti valikliai' => 'Schäume und andere Reiniger',
            'Dažai' => 'Farben',
            'Įrankiai ir jų priedai' => 'Werkzeuge und Zubehör',
            'Pieštukai' => 'Bleistifte',
            'Matavimo' => 'Messwerkzeuge',
            'Armatūrai rišti' => 'Für Bewehrungsbindung',
            'EPDM darbui skirti įrankiai' => 'Werkzeuge für EPDM-Arbeiten',
            'Grąžtai metalui' => 'Metallbohrer',
            'Grąžtai medžiui' => 'Holzbohrer',
            'Plaktukai' => 'Hämmer',
            'Lankstytuvai, žirklės skardai' => 'Bieger, Blechscheren',
            'Kniedikliai' => 'Nietzangen',
            'Atsuktuvai' => 'Schraubendreher',
            'Replės' => 'Feilen',
            'Pjūklai' => 'Sägen',
            'Laužtuvai' => 'Meißel',
            'Stogų danga ir priedai' => 'Dachmaterialien und Zubehör',
            'EPDM medžiaga ir įrankiai montavimui' => 'EPDM-Material und Montagewerkzeuge',
            'Stogų priedai' => 'Dachzubehör',
            'Ventiliuojami profiliai' => 'Belüftete Profile',
            'Paukščių užtvaros' => 'Vogelschutz',
            'Latakų apsauga' => 'Rinnenabdeckung',
            'Kraigo tarpinės' => 'Firstabstandshalter',
            'Fasadams' => 'Für Fassaden',
            'Ventiliuojami fasadai' => 'Belüftete Fassaden',
            'Akmenys, stount' => 'Steine, stount',
            'Priedai fasadams' => 'Fassadenzubehör',
            'WPC dailylentės' => 'WPC-Verblendbretter',
            'Kiti fasadai' => 'Andere Fassaden',
            'Akmens apdaila' => 'Steinverblendung',
            'Klinkerio apdaila' => 'Klinkerverblendung',
            'Elektros prekės' => 'Elektroartikel',
            'Izoliacinės juostos' => 'Isolierbänder',
            'Laidų sujungimas' => 'Kabelverbindung',
            'Elektros instaliacijos produktai' => 'Elektroinstallationsprodukte',
            'EGANT instaliacijos sistema' => 'EGANT Installationssystem',
            'Laidų tvirtinimas' => 'Kabelbefestigung',
            'Darbo apranga, saugos priemonės' => 'Arbeitskleidung, Sicherheitsausrüstung',
            'Kelnės' => 'Hosen',
            'Džemperiai' => 'Pullover',
            'Striukės' => 'Jacken',
            'Kepurės' => 'Mützen',
            'Pirštinės' => 'Handschuhe',
            'Kojinės' => 'Socken',
            'Batai' => 'Schuhe',
            'Akiniai' => 'Brillen',
            'Kelių apsauga' => 'Knieschutz',
            'Ausų apsauga' => 'Gehörschutz',
            'Stogų, grindų, sienų konstrukcijos' => 'Dach-, Boden-, Wandkonstruktionen',
            'Dvitejinės ir klijuotos fanieros sijos stogams ir sienoms' => 'Doppel- und geklebte Furnierbalken für Dächer und Wände',
            'Grindims ir perdangai' => 'Für Böden und Decken',
            'Vidaus apdaila' => 'Innenausbau',
            'Sienų apdailinės plokštės' => 'Wandverkleidungsplatten',
            'Vonios apdailinės plokštės' => 'Badezimmerverkleidungsplatten',
            'Virtuvės apdailinės plokštės' => 'Küchenverkleidungsplatten',
            'Lubų apdaila' => 'Deckenverkleidung',
            'Molio tinkai' => 'Lehmputze',
        ];

        return $translations[$text] ?? $text;
    }

    /**
     * Translate slug based on locale, keeping original structure
     */
    private function translateSlug(string $slug, string $locale): string
    {
        // For Lithuanian, return the original slug
        if ($locale === 'lt') {
            return $slug;
        }

        // For other locales, create a translated version
        // This ensures unique slugs per locale while maintaining structure
        return match ($locale) {
            'en' => $this->translateSlugToEnglish($slug),
            'ru' => $this->translateSlugToRussian($slug),
            'de' => $this->translateSlugToGerman($slug),
            default => $slug . '-' . $locale,
        };
    }

    /**
     * Translate slug to English
     */
    private function translateSlugToEnglish(string $slug): string
    {
        $translations = [
            'sandarinimo-pleveles-ir-juostos' => 'sealing-films-and-tapes',
            'juostos-laukui' => 'tapes-for-outdoor',
            'juostos-vidui' => 'tapes-for-indoor',
            'pleveles-vidui' => 'films-for-indoor',
            'pleveles-laukui' => 'films-for-outdoor',
            'horizontali' => 'horizontal',
            'vertikali' => 'vertical',
            'medvarsciu-ileidziama-galvute' => 'screws-flush-head',
            'medvarsciu-plokscija-galvute' => 'screws-flat-head',
            'medvarsciu-pusapvale-galvute' => 'screws-pan-head',
            'medvarsciu-sesiakampe-galvute' => 'screws-hex-head',
            'medvarsciu-konstrukciniai' => 'screws-structural',
            'medvarsciu-terasiniai' => 'screws-terrace',
            'medvarsciu-stoginiai' => 'screws-roofing',
            'medvarsciu-savigreziai' => 'screws-self-tapping',
            'medvarsciu-reguliuojami' => 'screws-adjustable',
            'tvirtinimas-i-betona-mura' => 'fastening-to-concrete-masonry',
            'su-plastikiniu-kaisciu' => 'with-plastic-dowel',
            'su-nailoniniu-kaisciu' => 'with-nylon-dowel',
            'inkariniai' => 'anchor',
            'sraigtai-i-betona' => 'screws-to-concrete',
            'sraigtai-i-mura' => 'screws-to-masonry',
            'ibetonuojami-ankeriai-sriegiai-detales' => 'embedded-anchors-threads-details',
            'greitvines' => 'quick-fix',
            'medzio-konstrukciju-tvirtinimas' => 'wood-construction-fastening',
            'ploksteles' => 'plates',
            'kampai' => 'angles',
            'siju-atrama' => 'beam-support',
            'gegniu-sujungimai' => 'rafter-connections',
            'pasleptas-tvirtinimas' => 'hidden-fastening',
            'kolonu-atramos-reguliuojamos-ibetonuojamos' => 'column-supports-adjustable-embedded',
            'sriegiai-varztai-verzles-poverzles' => 'threads-bolts-nuts-washers',
            'kniedes' => 'rivets',
            'vinys-kabes' => 'nails-hooks',
            'palaidos' => 'embedded',
            'pistoletines' => 'pistol',
            'lengvas-tvirtinimas' => 'light-fastening',
            'gipsui' => 'for-gypsum',
            'i-termoizoliacija' => 'to-thermal-insulation',
            'laikikliai-ir-kt' => 'brackets-and-others',
            'tvirtinimo-juostos' => 'fastening-tapes',
            'islyginimo-kaladele-kyliai-ir-kt' => 'leveling-strips-shims-and-others',
            'termoizoliaciniai-kaisci' => 'thermal-insulation-dowels',
            'isukami-ikalami-poliai' => 'screw-in-drive-in-piles',
            'hdg' => 'hdg',
            'hex-hdg' => 'hex-hdg',
            'chemija-statyboms' => 'construction-chemistry',
            'klijuojancios-putos' => 'adhesive-foams',
            'montavimo-putos' => 'installation-foams',
            'purskiama-termoizoliacija' => 'spray-thermal-insulation',
            'klijai' => 'adhesives',
            'hermetikai' => 'sealants',
            'silikonai' => 'silicones',
            'akrilai' => 'acrylics',
            'sandarinimo-mastikos' => 'sealing-mastics',
            'tepama-hidroizoliacija' => 'applied-waterproofing',
            'putu-ir-kiti-valikliai' => 'foams-and-other-cleaners',
            'dažai' => 'paints',
            'irankiai-ir-ju-priedai' => 'tools-and-accessories',
            'piestukai' => 'pencils',
            'matavimo' => 'measuring',
            'armaturai-risyti' => 'for-tying-reinforcement',
            'epdm-darbui-skirti-irankiai' => 'tools-for-epdm-work',
            'graztai-metalui' => 'drills-for-metal',
            'graztai-medziui' => 'drills-for-wood',
            'plaktukai' => 'hammers',
            'lankstytuvai-zirkles-skardai' => 'benders-shears-sheet-metal',
            'kniedikliai' => 'rivet-tools',
            'atsuktuvai' => 'screwdrivers',
            'reples' => 'files',
            'pjūklai' => 'saws',
            'laužtuvai' => 'chisels',
            'stogu-danga-ir-priedai' => 'roofing-materials-and-accessories',
            'epdm-medziaga-ir-irankiai-montavimui' => 'epdm-materials-and-installation-tools',
            'stogu-priedai' => 'roofing-accessories',
            'ventiliuojami-profiliai' => 'ventilated-profiles',
            'pauksciu-uztvaros' => 'bird-protection',
            'lataku-apsauga' => 'gutter-protection',
            'kraigo-tarpines' => 'ridge-spacers',
            'viniu-sandarinimo-juostos' => 'nail-sealing-tapes',
            'kiti-priedai' => 'other-accessories',
            'fasadams' => 'for-facades',
            'ventiliuojami-fasadai' => 'ventilated-facades',
            'akmenys-stount' => 'stones-stount',
            'priedai-fasadams' => 'facade-accessories',
            'wpc-dailylentes' => 'wpc-cladding-boards',
            'kiti-fasadai' => 'other-facades',
            'akmens-apdaila' => 'stone-cladding',
            'klinkerio-apdaila' => 'clinker-cladding',
            'elektros-prekes' => 'electrical-products',
            'izoliacines-juostos' => 'insulating-tapes',
            'laidu-sujungimas' => 'cable-connection',
            'elektros-instaliacijos-produktai' => 'electrical-installation-products',
            'egant-instaliacijos-sistema' => 'egant-installation-system',
            'laidu-tvirtinimas' => 'cable-fastening',
            'darbo-apranga-saugos-priemones' => 'work-clothing-safety-equipment',
            'kelnes' => 'pants',
            'dzemperiai' => 'sweaters',
            'striukes' => 'jackets',
            'kepures' => 'caps',
            'pirštines' => 'gloves',
            'kojinės' => 'socks',
            'batai' => 'shoes',
            'akiniai' => 'glasses',
            'keliu-apsauga' => 'knee-protection',
            'ausu-apsauga' => 'ear-protection',
            'stogu-grindu-sienu-konstrukcijos' => 'roof-floor-wall-constructions',
            'dvitejines-ir-klijuotos-fanieros-sijos' => 'double-and-glued-veneer-beams',
            'grindims-ir-perdangai' => 'for-floors-and-ceilings',
            'vidaus-apdaila' => 'interior-finishing',
            'sienu-apdailines-plokstes' => 'wall-finishing-plates',
            'vonios-apdailines-plokstes' => 'bathroom-finishing-plates',
            'virtuves-apdailines-plokstes' => 'kitchen-finishing-plates',
            'lubu-apdaila' => 'ceiling-finishing',
            'molio-tinkai' => 'clay-plasters',
        ];

        return $translations[$slug] ?? $slug;
    }

    /**
     * Translate slug to Russian
     */
    private function translateSlugToRussian(string $slug): string
    {
        $translations = [
            'sandarinimo-pleveles-ir-juostos' => 'germetiziruyushchie-plenki-i-lenty',
            'juostos-laukui' => 'lenty-dlya-ulitsy',
            'juostos-vidui' => 'lenty-dlya-vnutri',
            'pleveles-vidui' => 'plenki-dlya-vnutri',
            'pleveles-laukui' => 'plenki-dlya-ulitsy',
            'horizontali' => 'gorizontalnaya',
            'vertikali' => 'vertikalnaya',
            'medvarsciu-ileidziama-galvute' => 'samo-rezy-s-pogruzhennoy-golovkoy',
            'medvarsciu-plokscija-galvute' => 'samo-rezy-s-ploskoy-golovkoy',
            'medvarsciu-pusapvale-galvute' => 'samo-rezy-s-polusfericheskoy-golovkoy',
            'medvarsciu-sesiakampe-galvute' => 'samo-rezy-s-shestigrannoy-golovkoy',
            'medvarsciu-konstrukciniai' => 'samo-rezy-konstruktsionnye',
            'medvarsciu-terasiniai' => 'samo-rezy-terrasnye',
            'medvarsciu-stoginiai' => 'samo-rezy-krovelnye',
            'medvarsciu-savigreziai' => 'samo-rezy-samorezayushchie',
            'medvarsciu-reguliuojami' => 'samo-rezy-reguliruemye',
            'tvirtinimas-i-betona-mura' => 'kreplenie-k-betonu-kladke',
            'su-plastikiniu-kaisciu' => 's-plastikovym-dyubelyem',
            'su-nailoniniu-kaisciu' => 's-naylonovym-dyubelyem',
            'inkariniai' => 'ankernye',
            'sraigtai-i-betona' => 'samo-rezy-v-beton',
            'sraigtai-i-mura' => 'samo-rezy-v-kladku',
            'ibetonuojami-ankeriai-sriegiai-detales' => 'zabetonirovannye-ankery-rezby-detali',
            'greitvines' => 'bystryy-montazh',
            'medzio-konstrukciju-tvirtinimas' => 'kreplenie-derevyannyh-konstruktsiy',
            'ploksteles' => 'plastiny',
            'kampai' => 'ugly',
            'siju-atrama' => 'opora-balkov',
            'gegniu-sujungimai' => 'soedineniya-stropil',
            'pasleptas-tvirtinimas' => 'skrytoe-kreplenie',
            'kolonu-atramos-reguliuojamos-ibetonuojamos' => 'opory-kolonn-reguliruemye-zabetonirovannye',
            'sriegiai-varztai-verzles-poverzles' => 'rezby-bolty-gayki-podlozhki',
            'kniedes' => 'zaklepki',
            'vinys-kabes' => 'gvozdi-kryuchki',
            'palaidos' => 'zabetonirovannye',
            'pistoletines' => 'pistoletnye',
            'lengvas-tvirtinimas' => 'legkoe-kreplenie',
            'gipsui' => 'dlya-gipsa',
            'i-termoizoliacija' => 'v-termoizolyatsiyu',
            'laikikliai-ir-kt' => 'kronstejny-i-dr',
            'tvirtinimo-juostos' => 'kreplenie-lenty',
            'islyginimo-kaladele-kyliai-ir-kt' => 'vyravnivayushchie-planki-prokladki-i-dr',
            'termoizoliaciniai-kaisci' => 'termoizolyatsionnye-dyubelya',
            'isukami-ikalami-poliai' => 'vintovye-zabivnye-svai',
            'hdg' => 'hdg',
            'hex-hdg' => 'hex-hdg',
            'chemija-statyboms' => 'stroitelnaya-himiya',
            'klijuojancios-putos' => 'kleevye-peny',
            'montavimo-putos' => 'montazhnye-peny',
            'purskiama-termoizoliacija' => 'napyljaemaya-termoizolyatsiya',
            'klijai' => 'klei',
            'hermetikai' => 'germetiki',
            'silikonai' => 'silikony',
            'akrilai' => 'akrily',
            'sandarinimo-mastikos' => 'germetiziruyushchie-mastiki',
            'tepama-hidroizoliacija' => 'nanesennaya-gidroizolyatsiya',
            'putu-ir-kiti-valikliai' => 'peny-i-drugie-ochistiteli',
            'dažai' => 'kraski',
            'irankiai-ir-ju-priedai' => 'instrumenty-i-aksessuary',
            'piestukai' => 'karandashi',
            'matavimo' => 'izmerenie',
            'armaturai-risyti' => 'dlya-vyazki-armatury',
            'epdm-darbui-skirti-irankiai' => 'instrumenty-dlya-epdm-rabot',
            'graztai-metalui' => 'sverla-dlya-metalla',
            'graztai-medziui' => 'sverla-dlya-dereva',
            'plaktukai' => 'molotki',
            'lankstytuvai-zirkles-skardai' => 'gibki-nozhnicy-zhest',
            'kniedikliai' => 'zaklepochnye-instrumenty',
            'atsuktuvai' => 'otvertki',
            'reples' => 'najmny',
            'pjūklai' => 'pily',
            'laužtuvai' => 'dolota',
            'stogu-danga-ir-priedai' => 'krovelnye-materialy-i-aksessuary',
            'epdm-medziaga-ir-irankiai-montavimui' => 'epdm-materialy-i-instrumenty-montazha',
            'stogu-priedai' => 'krovelnye-aksessuary',
            'ventiliuojami-profiliai' => 'ventiliruemye-profili',
            'pauksciu-uztvaros' => 'zashchita-ot-ptic',
            'lataku-apsauga' => 'zashchita-zhelobov',
            'kraigo-tarpines' => 'rasstoyaniya-konka',
            'viniu-sandarinimo-juostos' => 'germetiziruyushchie-lenty-gvozdey',
            'kiti-priedai' => 'drugie-aksessuary',
            'fasadams' => 'dlya-fasadov',
            'ventiliuojami-fasadai' => 'ventiliruemye-fasady',
            'akmenys-stount' => 'kamni-stount',
            'priedai-fasadams' => 'aksessuary-dlya-fasadov',
            'wpc-dailylentes' => 'wpc-obshivka-doski',
            'kiti-fasadai' => 'drugie-fasady',
            'akmens-apdaila' => 'kamennaya-obshivka',
            'klinkerio-apdaila' => 'klinkernaya-obshivka',
            'elektros-prekes' => 'elektrotovary',
            'izoliacines-juostos' => 'izoliruyushchie-lenty',
            'laidu-sujungimas' => 'soedinenie-kabelej',
            'elektros-instaliacijos-produktai' => 'produkty-elektroustanovki',
            'egant-instaliacijos-sistema' => 'sistema-ustanovki-egant',
            'laidu-tvirtinimas' => 'kreplenie-kabelej',
            'darbo-apranga-saugos-priemones' => 'rabochaya-odezhda-sredstva-zashchity',
            'kelnes' => 'bryuki',
            'dzemperiai' => 'svitery',
            'striukes' => 'kurtki',
            'kepures' => 'shapki',
            'pirštines' => 'perchatki',
            'kojinės' => 'noski',
            'batai' => 'obuv',
            'akiniai' => 'ochki',
            'keliu-apsauga' => 'zashchita-kolen',
            'ausu-apsauga' => 'zashchita-ushej',
            'stogu-grindu-sienu-konstrukcijos' => 'konstruktsii-krysh-polov-sten',
            'dvitejines-ir-klijuotos-fanieros-sijos' => 'dvuhslojnye-i-kleevye-fanernye-balki',
            'grindims-ir-perdangai' => 'dlya-polov-i-perekrytij',
            'vidaus-apdaila' => 'vnutrennyaya-otdelka',
            'sienu-apdailines-plokstes' => 'stenovye-otdelochnye-plity',
            'vonios-apdailines-plokstes' => 'vannye-otdelochnye-plity',
            'virtuves-apdailines-plokstes' => 'kuhonnye-otdelochnye-plity',
            'lubu-apdaila' => 'otdelka-potolkov',
            'molio-tinkai' => 'glinyanye-shtukaturki',
        ];

        return $translations[$slug] ?? $slug;
    }

    /**
     * Translate slug to German
     */
    private function translateSlugToGerman(string $slug): string
    {
        $translations = [
            'sandarinimo-pleveles-ir-juostos' => 'abdichtungsfolien-und-baender',
            'juostos-laukui' => 'baender-fuer-aussen',
            'juostos-vidui' => 'baender-fuer-innen',
            'pleveles-vidui' => 'folien-fuer-innen',
            'pleveles-laukui' => 'folien-fuer-aussen',
            'horizontali' => 'horizontal',
            'vertikali' => 'vertikal',
            'medvarsciu-ileidziama-galvute' => 'schrauben-mit-senkkopf',
            'medvarsciu-plokscija-galvute' => 'schrauben-mit-flachkopf',
            'medvarsciu-pusapvale-galvute' => 'schrauben-mit-halbkugelkopf',
            'medvarsciu-sesiakampe-galvute' => 'schrauben-mit-sechskantkopf',
            'medvarsciu-konstrukciniai' => 'schrauben-konstruktions',
            'medvarsciu-terasiniai' => 'schrauben-terrassen',
            'medvarsciu-stoginiai' => 'schrauben-dach',
            'medvarsciu-savigreziai' => 'schrauben-selbstbohrend',
            'medvarsciu-reguliuojami' => 'schrauben-verstellbar',
            'tvirtinimas-i-betona-mura' => 'befestigung-an-beton-mauerwerk',
            'su-plastikiniu-kaisciu' => 'mit-kunststoffdübel',
            'su-nailoniniu-kaisciu' => 'mit-nylondübel',
            'inkariniai' => 'anker',
            'sraigtai-i-betona' => 'schrauben-in-beton',
            'sraigtai-i-mura' => 'schrauben-in-mauerwerk',
            'ibetonuojami-ankeriai-sriegiai-detales' => 'einbetonierte-anker-gewinde-details',
            'greitvines' => 'schnellbefestigung',
            'medzio-konstrukciju-tvirtinimas' => 'holzkonstruktion-befestigung',
            'ploksteles' => 'platten',
            'kampai' => 'winkel',
            'siju-atrama' => 'balkenauflage',
            'gegniu-sujungimai' => 'sparrenverbindungen',
            'pasleptas-tvirtinimas' => 'versteckte-befestigung',
            'kolonu-atramos-reguliuojamos-ibetonuojamos' => 'saeulenauflagen-verstellbar-einbetoniert',
            'sriegiai-varztai-verzles-poverzles' => 'gewinde-schrauben-muttern-unterlegscheiben',
            'kniedes' => 'niete',
            'vinys-kabes' => 'naegel-haken',
            'palaidos' => 'einbetoniert',
            'pistoletines' => 'pistolen',
            'lengvas-tvirtinimas' => 'leichte-befestigung',
            'gipsui' => 'fuer-gips',
            'i-termoizoliacija' => 'in-waermedaemmung',
            'laikikliai-ir-kt' => 'halterungen-und-andere',
            'tvirtinimo-juostos' => 'befestigungsbaender',
            'islyginimo-kaladele-kyliai-ir-kt' => 'ausgleichsleisten-keile-und-andere',
            'termoizoliaciniai-kaisci' => 'waermedaemmungsdübel',
            'isukami-ikalami-poliai' => 'einschraub-einrammpfaehle',
            'hdg' => 'hdg',
            'hex-hdg' => 'hex-hdg',
            'chemija-statyboms' => 'baustoffchemie',
            'klijuojancios-putos' => 'klebeschaeume',
            'montavimo-putos' => 'montageschaeume',
            'purskiama-termoizoliacija' => 'aufgespritzte-waermedaemmung',
            'klijai' => 'klebstoffe',
            'hermetikai' => 'dichtstoffe',
            'silikonai' => 'silikone',
            'akrilai' => 'acrylate',
            'sandarinimo-mastikos' => 'abdichtungsmassen',
            'tepama-hidroizoliacija' => 'aufgetragene-wasserdichtung',
            'putu-ir-kiti-valikliai' => 'schaeume-und-andere-reiniger',
            'dažai' => 'farben',
            'irankiai-ir-ju-priedai' => 'werkzeuge-und-zubehoer',
            'piestukai' => 'bleistifte',
            'matavimo' => 'messung',
            'armaturai-risyti' => 'fuer-bewehrung-binden',
            'epdm-darbui-skirti-irankiai' => 'werkzeuge-fuer-epdm-arbeit',
            'graztai-metalui' => 'bohrer-fuer-metall',
            'graztai-medziui' => 'bohrer-fuer-holz',
            'plaktukai' => 'hämmer',
            'lankstytuvai-zirkles-skardai' => 'bieger-scheren-blech',
            'kniedikliai' => 'nietwerkzeuge',
            'atsuktuvai' => 'schraubendreher',
            'reples' => 'feilen',
            'pjūklai' => 'saegen',
            'laužtuvai' => 'meissel',
            'stogu-danga-ir-priedai' => 'dachmaterialien-und-zubehoer',
            'epdm-medziaga-ir-irankiai-montavimui' => 'epdm-material-und-montagewerkzeuge',
            'stogu-priedai' => 'dachzubehoer',
            'ventiliuojami-profiliai' => 'belueftete-profile',
            'pauksciu-uztvaros' => 'vogelschutz',
            'lataku-apsauga' => 'rinnenabdeckung',
            'kraigo-tarpines' => 'firstabstandshalter',
            'viniu-sandarinimo-juostos' => 'nagelabdichtungsbaender',
            'kiti-priedai' => 'andere-zubehoer',
            'fasadams' => 'fuer-fassaden',
            'ventiliuojami-fasadai' => 'belueftete-fassaden',
            'akmenys-stount' => 'steine-stount',
            'priedai-fasadams' => 'fassadenzubehoer',
            'wpc-dailylentes' => 'wpc-verblendbretter',
            'kiti-fasadai' => 'andere-fassaden',
            'akmens-apdaila' => 'steinverblendung',
            'klinkerio-apdaila' => 'klinkerverblendung',
            'elektros-prekes' => 'elektroartikel',
            'izoliacines-juostos' => 'isolierbaender',
            'laidu-sujungimas' => 'kabelverbindung',
            'elektros-instaliacijos-produktai' => 'elektroinstallationsprodukte',
            'egant-instaliacijos-sistema' => 'egant-installationssystem',
            'laidu-tvirtinimas' => 'kabelbefestigung',
            'darbo-apranga-saugos-priemones' => 'arbeitskleidung-sicherheitsausruestung',
            'kelnes' => 'hosen',
            'dzemperiai' => 'pullover',
            'striukes' => 'jacken',
            'kepures' => 'muetzen',
            'pirštines' => 'handschuhe',
            'kojinės' => 'socken',
            'batai' => 'schuhe',
            'akiniai' => 'brillen',
            'keliu-apsauga' => 'knieschutz',
            'ausu-apsauga' => 'gehoerschutz',
            'stogu-grindu-sienu-konstrukcijos' => 'dach-boden-wandkonstruktionen',
            'dvitejines-ir-klijuotos-fanieros-sijos' => 'doppel-und-geklebte-furnierbalken',
            'grindims-ir-perdangai' => 'fuer-boeden-und-decken',
            'vidaus-apdaila' => 'innenausbau',
            'sienu-apdailines-plokstes' => 'wandverkleidungsplatten',
            'vonios-apdailines-plokstes' => 'badezimmerverkleidungsplatten',
            'virtuves-apdailines-plokstes' => 'kuechenverkleidungsplatten',
            'lubu-apdaila' => 'deckenverkleidung',
            'molio-tinkai' => 'lehmputze',
        ];

        return $translations[$slug] ?? $slug;
    }
}
