<?php declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use App\Services\Images\LocalImageGeneratorService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class LithuanianCatalogSeeder extends Seeder
{
    private LocalImageGeneratorService $imageGenerator;

    public function __construct()
    {
        $this->imageGenerator = new LocalImageGeneratorService;
    }

    public function run(): void
    {
        $categories = [
            [
                'name' => 'Sandarinimo plėvelės ir juostos',
                'slug' => 'sandarinimo-pleveles-ir-juostos',
                'description' => 'Sandarinimo sprendimai: juostos, plėvelės, hidroizoliacija ir priedai.',
                'sort_order' => 1,
                'image_url' => 'local://category/sandarinimo-pleveles-ir-juostos.webp',
                'children' => [
                    [
                        'name' => 'Juostos',
                        'slug' => 'juostos',
                        'description' => 'Sandarinimo juostos vidaus ir lauko darbams.',
                        'sort_order' => 1,
                        'image_url' => 'local://category/juostos.webp',
                        'children' => [
                            ['name' => 'Laukui', 'slug' => 'laukui', 'description' => 'Juostos, skirtos naudoti lauko sąlygomis.', 'sort_order' => 1, 'image_url' => 'local://category/juostos-laukui.webp'],
                            ['name' => 'Vidui', 'slug' => 'vidui', 'description' => 'Juostos, skirtos vidaus darbams ir patalpoms.', 'sort_order' => 2, 'image_url' => 'local://category/juostos-vidui.webp'],
                        ],
                    ],
                    [
                        'name' => 'Plėvelės',
                        'slug' => 'pleveles',
                        'description' => 'Sandarinimo plėvelės vidaus ir išorės darbams.',
                        'sort_order' => 2,
                        'image_url' => 'local://category/pleveles.webp',
                        'children' => [
                            ['name' => 'Vidui', 'slug' => 'pleveles-vidui', 'description' => 'Plėvelės, skirtos naudoti patalpų viduje.', 'sort_order' => 1, 'image_url' => 'local://category/pleveles-vidui.webp'],
                            ['name' => 'Išorei', 'slug' => 'pleveles-isorei', 'description' => 'Plėvelės, skirtos naudoti lauko sąlygomis.', 'sort_order' => 2, 'image_url' => 'local://category/pleveles-isorei.webp'],
                        ],
                    ],
                    [
                        'name' => 'Pamatų hidroizoliacija',
                        'slug' => 'pamatu-hidroizoliacija',
                        'description' => 'Pamatų hidroizoliacijos sprendimai horizontaliai ir vertikaliai apsaugai.',
                        'sort_order' => 3,
                        'image_url' => 'local://category/pamatu-hidroizoliacija.webp',
                        'children' => [
                            ['name' => 'Horizontali', 'slug' => 'hidroizoliacija-horizontali', 'description' => 'Horizontali pamatų hidroizoliacija.', 'sort_order' => 1, 'image_url' => 'local://category/hidroizoliacija-horizontali.webp'],
                            ['name' => 'Vertikali', 'slug' => 'hidroizoliacija-vertikali', 'description' => 'Vertikali pamatų hidroizoliacija.', 'sort_order' => 2, 'image_url' => 'local://category/hidroizoliacija-vertikali.webp'],
                        ],
                    ],
                    ['name' => 'Vinių sandarinimo juostos', 'slug' => 'viniu-sandarinimo-juostos', 'description' => 'Juostos vinių ir tvirtinimo vietų sandarinimui.', 'sort_order' => 4, 'image_url' => 'local://category/viniu-sandarinimo-juostos.webp'],
                    ['name' => 'Kitos sandarinimo medžiagos ir priedai', 'slug' => 'kitos-sandarinimo-medziagos-ir-priedai', 'description' => 'Papildomos sandarinimo priemonės ir reikalingi priedai.', 'sort_order' => 5, 'image_url' => 'local://category/kitos-sandarinimo-medziagos-ir-priedai.webp'],
                ],
            ],
            [
                'name' => 'Tvirtinimo elementai, varžtai, medvarsčiai',
                'slug' => 'tvirtinimo-elementai-varztai-medvarzciai',
                'description' => 'Platus tvirtinimo elementų, varžtų ir medvaržčių asortimentas.',
                'sort_order' => 2,
                'image_url' => 'local://category/tvirtinimo-elementai.webp',
                'children' => [
                    [
                        'name' => 'Medvarsčiai',
                        'slug' => 'medvarzciai',
                        'description' => 'Medienai skirti varžtai – įvairių tipų ir paskirčių.',
                        'sort_order' => 1,
                        'image_url' => 'local://category/medvarzciai.webp',
                        'children' => [
                            ['name' => 'Įleidžiama galvute', 'slug' => 'ileidziama-galvute', 'description' => 'Su įleidžiama galvute, švariam paviršiui.', 'sort_order' => 1, 'image_url' => 'local://category/ileidziama-galvute.webp'],
                            ['name' => 'Plokščia galvute', 'slug' => 'plokscia-galvute', 'description' => 'Plokščios galvutės medvaržčiai bendram naudojimui.', 'sort_order' => 2, 'image_url' => 'local://category/plokscia-galvute.webp'],
                            ['name' => 'Pusapvale galvute', 'slug' => 'pusapvale-galvute', 'description' => 'Pusapvalės galvutės sprendimai.', 'sort_order' => 3, 'image_url' => 'local://category/pusapvale-galvute.webp'],
                            ['name' => 'Šešiakampe galvute', 'slug' => 'sesiakampe-galvute', 'description' => 'Šešiakampės galvutės varžtai tvirtesniam suveržimui.', 'sort_order' => 4, 'image_url' => 'local://category/sesiakampe-galvute.webp'],
                            ['name' => 'Konstrukciniai', 'slug' => 'konstrukciniai', 'description' => 'Konstrukciniai varžtai didelėms apkrovoms.', 'sort_order' => 5, 'image_url' => 'local://category/konstrukciniai.webp'],
                            ['name' => 'Terasiniai', 'slug' => 'terasiniai', 'description' => 'Terasų montavimui pritaikyti varžtai.', 'sort_order' => 6, 'image_url' => 'local://category/terasiniai.webp'],
                            ['name' => 'Stoginiai', 'slug' => 'stoginiai', 'description' => 'Stogo dangoms ir priedams.', 'sort_order' => 7, 'image_url' => 'local://category/stoginiai.webp'],
                            ['name' => 'Savigręžiai', 'slug' => 'savigreziai', 'description' => 'Savigręžiai varžtai greitam montavimui.', 'sort_order' => 8, 'image_url' => 'local://category/savigreziai.webp'],
                            ['name' => 'Reguliuojami', 'slug' => 'reguliuojami', 'description' => 'Reguliuojamo ilgio ar aukščio sprendimai.', 'sort_order' => 9, 'image_url' => 'local://category/reguliuojami.webp'],
                        ],
                    ],
                    [
                        'name' => 'Tvirtinimas į betoną, mūrą',
                        'slug' => 'tvirtinimas-i-betona-mura',
                        'description' => 'Sprendimai tvirtinimui į betoną, mūrą bei kitus kietus pagrindus.',
                        'sort_order' => 2,
                        'image_url' => 'local://category/tvirtinimas-betonas-muras.webp',
                        'children' => [
                            ['name' => 'Su plastikiniu kaiščiu', 'slug' => 'plastikinis-kaiscius', 'description' => 'Klasikiniai plastikiniai kaiščiai.', 'sort_order' => 1, 'image_url' => 'local://category/plastikinis-kaiscius.webp'],
                            ['name' => 'Su nailoniniu kaiščiu', 'slug' => 'nailoninis-kaiscius', 'description' => 'Aukšto patvarumo nailoniniai kaiščiai.', 'sort_order' => 2, 'image_url' => 'local://category/nailoninis-kaiscius.webp'],
                            ['name' => 'Inkariniai', 'slug' => 'inkariniai', 'description' => 'Inkariniai tvirtinimo elementai.', 'sort_order' => 3, 'image_url' => 'local://category/inkariniai.webp'],
                            ['name' => 'Sraigtai į betoną', 'slug' => 'sraigtai-i-betona', 'description' => 'Specialūs sraigtai betonui.', 'sort_order' => 4, 'image_url' => 'local://category/sraigtai-i-betona.webp'],
                            ['name' => 'Sraigtai į mūrą', 'slug' => 'sraigtai-i-mura', 'description' => 'Specialūs sraigtai mūrui.', 'sort_order' => 5, 'image_url' => 'local://category/sraigtai-i-mura.webp'],
                            ['name' => 'Įbetonuojami ankeriai, sriegiai, detalės', 'slug' => 'ibetonuojami-ankeriai-sriegiai-detales', 'description' => 'Įbetonuojamos detalės tvirtoms jungtims.', 'sort_order' => 6, 'image_url' => 'local://category/ibetonuojami-ankeriai-sriegiai-detales.webp'],
                            ['name' => 'Greitvinės', 'slug' => 'greitvines', 'description' => 'Greito montavimo vinys ir sprendimai.', 'sort_order' => 7, 'image_url' => 'local://category/greitvines.webp'],
                        ],
                    ],
                    [
                        'name' => 'Medžio konstrukcijų tvirtinimas',
                        'slug' => 'medzio-konstrukciju-tvirtinimas',
                        'description' => 'Plokštelės, kampai, sijų atramos ir kiti sprendimai medienai.',
                        'sort_order' => 3,
                        'image_url' => 'local://category/medzio-konstrukciju-tvirtinimas.webp',
                        'children' => [
                            ['name' => 'Plokštelės', 'slug' => 'ploksteles', 'description' => 'Tvirtinimo plokštelės įvairioms jungtims.', 'sort_order' => 1, 'image_url' => 'local://category/ploksteles.webp'],
                            ['name' => 'Kampai', 'slug' => 'kampai', 'description' => 'Kampai medžio jungtims sutvirtinti.', 'sort_order' => 2, 'image_url' => 'local://category/kampai.webp'],
                            ['name' => 'Sijų atrama', 'slug' => 'siju-atrama', 'description' => 'Atramos sijos tvirtesniam sujungimui.', 'sort_order' => 3, 'image_url' => 'local://category/siju-atrama.webp'],
                            ['name' => 'Gegnių sujungimai', 'slug' => 'gegnniu-sujungimai', 'description' => 'Gegnių jungimo detalės.', 'sort_order' => 4, 'image_url' => 'local://category/gegnniu-sujungimai.webp'],
                            ['name' => 'Paslėptas tvirtinimas', 'slug' => 'pasleptas-tvirtinimas', 'description' => 'Nematomi tvirtinimo sprendimai.', 'sort_order' => 5, 'image_url' => 'local://category/pasleptas-tvirtinimas.webp'],
                            ['name' => 'Kolonų atramos (reguliuojamos, įbetonuojamos)', 'slug' => 'kolonu-atramos-reguliuojamos-ibetonuojamos', 'description' => 'Reguliuojamos ir įbetonuojamos kolonų atramos.', 'sort_order' => 6, 'image_url' => 'local://category/kolonu-atramos.webp'],
                            ['name' => 'Kita', 'slug' => 'medzio-tvirtinimas-kita', 'description' => 'Kiti tvirtinimo elementai medienai.', 'sort_order' => 7, 'image_url' => 'local://category/medzio-tvirtinimas-kita.webp'],
                        ],
                    ],
                    ['name' => 'Sriegiai, varžtai, veržlės, poveržlės', 'slug' => 'sriegiai-varztai-verzles-poverzles', 'description' => 'Sriegiai ir tvirtinimo detalės.', 'sort_order' => 4, 'image_url' => 'local://category/sriegiai-ir-detales.webp'],
                    ['name' => 'Kniedės', 'slug' => 'kniedes', 'description' => 'Kniedės ir kniedijimo sprendimai.', 'sort_order' => 5, 'image_url' => 'local://category/kniedes.webp'],
                    [
                        'name' => 'Vinys, kabės',
                        'slug' => 'vinys-kabes',
                        'description' => 'Palaidos ir pistoletinės vinys bei kabės.',
                        'sort_order' => 6,
                        'image_url' => 'local://category/vinys-kabes.webp',
                        'children' => [
                            ['name' => 'Palaidos', 'slug' => 'palaidos', 'description' => 'Palaidos vinys ir kabės.', 'sort_order' => 1, 'image_url' => 'local://category/palaidos-vinys.webp'],
                            ['name' => 'Pistoletinės', 'slug' => 'pistoletines', 'description' => 'Pistoletams skirtos vinys ir kabės.', 'sort_order' => 2, 'image_url' => 'local://category/pistoletines-vinys.webp'],
                        ],
                    ],
                    [
                        'name' => 'Lengvas tvirtinimas',
                        'slug' => 'lengvas-tvirtinimas',
                        'description' => 'Lengvo tvirtinimo sprendimai gipsui, izoliacijai ir kt.',
                        'sort_order' => 7,
                        'image_url' => 'local://category/lengvas-tvirtinimas.webp',
                        'children' => [
                            ['name' => 'Gipsui', 'slug' => 'gipsui', 'description' => 'Tvirtinimo elementai gipso plokštėms.', 'sort_order' => 1, 'image_url' => 'local://category/gipsui.webp'],
                            ['name' => 'Į termoizoliaciją', 'slug' => 'i-termoizoliacija', 'description' => 'Tvirtinimas į termoizoliacines medžiagas.', 'sort_order' => 2, 'image_url' => 'local://category/i-termoizoliacija.webp'],
                            ['name' => 'Laikikliai ir kt.', 'slug' => 'laikikliai-ir-kt', 'description' => 'Laikikliai ir kiti priedai.', 'sort_order' => 3, 'image_url' => 'local://category/laikikliai-ir-kt.webp'],
                            ['name' => 'Kita', 'slug' => 'lengvas-tvirtinimas-kita', 'description' => 'Kiti lengvo tvirtinimo sprendimai.', 'sort_order' => 4, 'image_url' => 'local://category/lengvas-tvirtinimas-kita.webp'],
                        ],
                    ],
                    ['name' => 'Tvirtinimo juostos', 'slug' => 'tvirtinimo-juostos', 'description' => 'Metalinių ir kompozitinių juostų sprendimai.', 'sort_order' => 8, 'image_url' => 'local://category/tvirtinimo-juostos.webp'],
                    ['name' => 'Išlyginimo kaladėlė, kyliai ir kt.', 'slug' => 'islyginimo-kaladele-kyliai-ir-kt', 'description' => 'Lygiavimo kaladėlės, kyliai ir priedai.', 'sort_order' => 9, 'image_url' => 'local://category/islyginimo-kaladeles-kyliai.webp'],
                    ['name' => 'Termoizoliaciniai kaiščiai', 'slug' => 'termoizoliaciniai-kaisciai', 'description' => 'Kaiščiai termoizoliacinėms plokštėms tvirtinti.', 'sort_order' => 10, 'image_url' => 'local://category/termoizoliaciniai-kaisciai.webp'],
                ],
            ],
            [
                'name' => 'Įsukami, įkalami poliai',
                'slug' => 'isukami-ikalami-poliai',
                'description' => 'Poliai pamatams ir konstrukcijoms – įsukami ir įkalami.',
                'sort_order' => 3,
                'image_url' => 'local://category/poliai.webp',
                'children' => [
                    ['name' => 'HDG', 'slug' => 'hdg', 'description' => 'Cinkuoti poliai HDG.', 'sort_order' => 1, 'image_url' => 'local://category/hdg.webp'],
                    ['name' => 'HEX HDG', 'slug' => 'hex-hdg', 'description' => 'HEX tipo cinkuoti poliai.', 'sort_order' => 2, 'image_url' => 'local://category/hex-hdg.webp'],
                ],
            ],
            [
                'name' => 'Chemija statyboms',
                'slug' => 'chemija-statyboms',
                'description' => 'Statybinė chemija: putos, klijai, hermetikai, dažai ir kt.',
                'sort_order' => 4,
                'image_url' => 'local://category/chemija-statyboms.webp',
                'children' => [
                    ['name' => 'Klijuojančios putos', 'slug' => 'klijuojancios-putos', 'description' => 'Klijavimui skirtos poliuretano putos.', 'sort_order' => 1, 'image_url' => 'local://category/klijuojancios-putos.webp'],
                    ['name' => 'Montavimo putos', 'slug' => 'montavimo-putos', 'description' => 'Montavimui ir sandarinimui skirtos putos.', 'sort_order' => 2, 'image_url' => 'local://category/montavimo-putos.webp'],
                    ['name' => 'Purškiama termoizoliacija', 'slug' => 'purskiama-termoizoliacija', 'description' => 'Purškiamos izoliacijos sprendimai.', 'sort_order' => 3, 'image_url' => 'local://category/purskiama-termoizoliacija.webp'],
                    ['name' => 'Klijai', 'slug' => 'klijai', 'description' => 'Universalūs ir specializuoti klijai.', 'sort_order' => 4, 'image_url' => 'local://category/klijai.webp'],
                    ['name' => 'Hermetikai', 'slug' => 'hermetikai', 'description' => 'Hermetikai siūlėms ir sandūroms.', 'sort_order' => 5, 'image_url' => 'local://category/hermetikai.webp'],
                    ['name' => 'Silikonai', 'slug' => 'silikonai', 'description' => 'Silikoniniai sandarikliai.', 'sort_order' => 6, 'image_url' => 'local://category/silikonai.webp'],
                    ['name' => 'Akrilai', 'slug' => 'akrilai', 'description' => 'Akriliniai hermetikai ir medžiagos.', 'sort_order' => 7, 'image_url' => 'local://category/akrilai.webp'],
                    ['name' => 'Sandarinimo mastikos', 'slug' => 'sandarinimo-mastikos', 'description' => 'Mastikos įvairiems sandarinimo darbams.', 'sort_order' => 8, 'image_url' => 'local://category/sandarinimo-mastikos.webp'],
                    ['name' => 'Tepama hidroizoliacija', 'slug' => 'tepama-hidroizoliacija', 'description' => 'Tepamos hidroizoliacijos mišiniai.', 'sort_order' => 9, 'image_url' => 'local://category/tepama-hidroizoliacija.webp'],
                    ['name' => 'Putų ir kiti valikliai', 'slug' => 'putu-ir-kiti-valikliai', 'description' => 'Valikliai putoms ir kitiems nešvarumams.', 'sort_order' => 10, 'image_url' => 'local://category/putu-valikliai.webp'],
                    ['name' => 'Dažai', 'slug' => 'dazai', 'description' => 'Vidaus ir lauko dažai.', 'sort_order' => 11, 'image_url' => 'local://category/dazai.webp'],
                    ['name' => 'Kita', 'slug' => 'chemija-kita', 'description' => 'Kiti statybinės chemijos produktai.', 'sort_order' => 12, 'image_url' => 'local://category/chemija-kita.webp'],
                ],
            ],
            [
                'name' => 'Įrankiai ir jų priedai',
                'slug' => 'irankiai-ir-ju-priedai',
                'description' => 'Profesionalūs įrankiai ir priedai darbams atlikti.',
                'sort_order' => 5,
                'image_url' => 'local://category/irankiai.webp',
                'children' => [
                    ['name' => 'Pieštukai', 'slug' => 'piestukai', 'description' => 'Žymėjimo pieštukai.', 'sort_order' => 1, 'image_url' => 'local://category/piestukai.webp'],
                    ['name' => 'Matavimo', 'slug' => 'matavimo', 'description' => 'Matavimo priemonės ir įrankiai.', 'sort_order' => 2, 'image_url' => 'local://category/matavimo.webp'],
                    ['name' => 'Armatūrai rišti', 'slug' => 'armaturai-risti', 'description' => 'Įrankiai armatūrai rišti.', 'sort_order' => 3, 'image_url' => 'local://category/armaturai-risti.webp'],
                    ['name' => 'EPDM darbui skirti įrankiai', 'slug' => 'epdm-darbui-skirti-irankiai', 'description' => 'Įrankiai EPDM montavimui.', 'sort_order' => 4, 'image_url' => 'local://category/epdm-darbui-irankiai.webp'],
                    ['name' => 'Grąžtai metalui', 'slug' => 'grazhtai-metalui', 'description' => 'Grąžtai metalui.', 'sort_order' => 5, 'image_url' => 'local://category/grazhtai-metalui.webp'],
                    ['name' => 'Grąžtai medžiui', 'slug' => 'grazhtai-medziui', 'description' => 'Grąžtai medienai.', 'sort_order' => 6, 'image_url' => 'local://category/grazhtai-medziui.webp'],
                    ['name' => 'Plaktukai', 'slug' => 'plaktukai', 'description' => 'Įvairūs plaktukai.', 'sort_order' => 7, 'image_url' => 'local://category/plaktukai.webp'],
                    ['name' => 'Lankstytuvai, žirklės skardai', 'slug' => 'lankstytuvai-zirkles-skardai', 'description' => 'Skardos lankstymo įrankiai ir žirklės.', 'sort_order' => 8, 'image_url' => 'local://category/lankstytuvai-zirkles.webp'],
                    ['name' => 'Kniedikliai', 'slug' => 'kniedikliai', 'description' => 'Kniedijimo įrankiai.', 'sort_order' => 9, 'image_url' => 'local://category/kniedikliai.webp'],
                    ['name' => 'Atsuktuvai', 'slug' => 'atsuktuvai', 'description' => 'Atsuktuvai ir antgaliai.', 'sort_order' => 10, 'image_url' => 'local://category/atsuktuvai.webp'],
                    ['name' => 'Replės', 'slug' => 'reples', 'description' => 'Replės įvairiems darbams.', 'sort_order' => 11, 'image_url' => 'local://category/reples.webp'],
                    ['name' => 'Pjūklai', 'slug' => 'pjuklai', 'description' => 'Rankiniai ir elektriniai pjūklai.', 'sort_order' => 12, 'image_url' => 'local://category/pjuklai.webp'],
                    ['name' => 'Laužtuvai', 'slug' => 'lauztuvai', 'description' => 'Laužtuvai demontavimo darbams.', 'sort_order' => 13, 'image_url' => 'local://category/lauztuvai.webp'],
                    ['name' => 'Kita', 'slug' => 'irankiai-kita', 'description' => 'Kiti įrankiai ir priedai.', 'sort_order' => 14, 'image_url' => 'local://category/irankiai-kita.webp'],
                ],
            ],
            [
                'name' => 'Stogų danga ir priedai',
                'slug' => 'stogu-danga-ir-priedai',
                'description' => 'Stogo dangos, EPDM ir visi reikalingi priedai.',
                'sort_order' => 6,
                'image_url' => 'local://category/stogu-danga-priedai.webp',
                'children' => [
                    ['name' => 'EPDM medžiaga ir įrankiai montavimui', 'slug' => 'epdm-medziaga-ir-irankiai-montavimui', 'description' => 'EPDM membranos ir jų montavimo įrankiai.', 'sort_order' => 1, 'image_url' => 'local://category/epdm-medziaga-irankiai.webp'],
                    [
                        'name' => 'Stogų priedai',
                        'slug' => 'stogu-priedai',
                        'description' => 'Ventiliacija, apsaugos ir sandarinimas stogams.',
                        'sort_order' => 2,
                        'image_url' => 'local://category/stogu-priedai.webp',
                        'children' => [
                            ['name' => 'Ventiliuojami profiliai', 'slug' => 'ventiliuojami-profiliai', 'description' => 'Ventiliuojami profiliai stogams.', 'sort_order' => 1, 'image_url' => 'local://category/ventiliuojami-profiliai.webp'],
                            ['name' => 'Paukščių užtvaros', 'slug' => 'pauksciu-uztvaros', 'description' => 'Apsaugos nuo paukščių stogui.', 'sort_order' => 2, 'image_url' => 'local://category/pauksciu-uztvaros.webp'],
                            ['name' => 'Latakų apsauga', 'slug' => 'lataku-apsauga', 'description' => 'Latakų apsaugos sprendimai.', 'sort_order' => 3, 'image_url' => 'local://category/lataku-apsauga.webp'],
                            ['name' => 'Kraigo tarpinės', 'slug' => 'kraigo-tarpines', 'description' => 'Kraigo sandarinimo tarpinės.', 'sort_order' => 4, 'image_url' => 'local://category/kraigo-tarpines.webp'],
                            ['name' => 'Vinių sandarinimo juostos', 'slug' => 'stogams-viniu-sandarinimo-juostos', 'description' => 'Vinių vietų sandarinimo juostos stogams.', 'sort_order' => 5, 'image_url' => 'local://category/stogu-viniu-juostos.webp'],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Fasadams',
                'slug' => 'fasadams',
                'description' => 'Ventiliuojami ir kiti fasadų sprendimai bei priedai.',
                'sort_order' => 7,
                'image_url' => 'local://category/fasadams.webp',
                'children' => [
                    [
                        'name' => 'Ventiliuojami fasadai',
                        'slug' => 'ventiliuojami-fasadai',
                        'description' => 'Ventiliuojamų fasadų medžiagos ir priedai.',
                        'sort_order' => 1,
                        'image_url' => 'local://category/ventiliuojami-fasadai.webp',
                        'children' => [
                            ['name' => 'Akmenys, stount', 'slug' => 'akmenys-stount', 'description' => 'Fasadiniai akmenys ir "stount" tipo medžiagos.', 'sort_order' => 1, 'image_url' => 'local://category/akmenys.webp'],
                            [
                                'name' => 'Priedai fasadams',
                                'slug' => 'priedai-fasadams',
                                'description' => 'Tinkleliai, barjerai, profiliai, varžtai.',
                                'sort_order' => 2,
                                'image_url' => 'local://category/priedai-fasadams.webp',
                                'children' => [
                                    ['name' => 'Pelių barjerai', 'slug' => 'peliu-barjerai', 'description' => 'Barjerai nuo graužikų.', 'sort_order' => 1, 'image_url' => 'local://category/peliu-barjerai.webp'],
                                    ['name' => 'Įvairūs tinkliukai', 'slug' => 'ivairus-tinkliukai', 'description' => 'Fasadiniai tinkliukai ir tinkleliai.', 'sort_order' => 2, 'image_url' => 'local://category/tinkliukai.webp'],
                                    ['name' => 'Ventiliuojami profiliai', 'slug' => 'fasadiniai-ventiliuojami-profiliai', 'description' => 'Profilių sistemos vėdinamiems fasadams.', 'sort_order' => 3, 'image_url' => 'local://category/fasado-profiliai.webp'],
                                    ['name' => 'Fasadiniai varžtai', 'slug' => 'fasadiniai-varztai', 'description' => 'Fasado medžiagoms skirti varžtai.', 'sort_order' => 4, 'image_url' => 'local://category/fasadiniai-varztai.webp'],
                                ],
                            ],
                        ],
                    ],
                    ['name' => 'Kiti fasadai', 'slug' => 'kiti-fasadai', 'description' => 'Kiti fasadų sprendimai ir sistemos.', 'sort_order' => 2, 'image_url' => 'local://category/kiti-fasadai.webp'],
                ],
            ],
            [
                'name' => 'Elektros prekės',
                'slug' => 'elektros-prekes',
                'description' => 'Elektrinės izoliacijos juostos, sujungimai ir tvirtinimo sprendimai.',
                'sort_order' => 8,
                'image_url' => 'local://category/elektros-prekes.webp',
                'children' => [
                    ['name' => 'Izoliacinės juostos', 'slug' => 'izoliacines-juostos', 'description' => 'Elektrinės izoliacinės juostos.', 'sort_order' => 1, 'image_url' => 'local://category/izoliacines-juostos.webp'],
                    ['name' => 'Laidų sujungimas', 'slug' => 'laidu-sujungimas', 'description' => 'Laidų sujungimo elementai.', 'sort_order' => 2, 'image_url' => 'local://category/laidu-sujungimas.webp'],
                    ['name' => 'Laidų tvirtinimas', 'slug' => 'laidu-tvirtinimas', 'description' => 'Laidų tvirtinimo sprendimai.', 'sort_order' => 3, 'image_url' => 'local://category/laidu-tvirtinimas.webp'],
                    ['name' => 'Kita', 'slug' => 'elektra-kita', 'description' => 'Kitos elektros prekės.', 'sort_order' => 4, 'image_url' => 'local://category/elektra-kita.webp'],
                ],
            ],
            [
                'name' => 'Darbo apranga, saugos priemonės',
                'slug' => 'darbo-apranga-saugos-priemones',
                'description' => 'Darbo rūbai ir asmeninės saugos priemonės.',
                'sort_order' => 9,
                'image_url' => 'local://category/darbo-apranga-sauga.webp',
                'children' => [
                    ['name' => 'Kelnės', 'slug' => 'kelnes', 'description' => 'Darbo kelnės.', 'sort_order' => 1, 'image_url' => 'local://category/kelnes.webp'],
                    ['name' => 'Džemperiai', 'slug' => 'dzemperiai', 'description' => 'Darbo džemperiai.', 'sort_order' => 2, 'image_url' => 'local://category/dzemperiai.webp'],
                    ['name' => 'Striukės', 'slug' => 'striukes', 'description' => 'Darbo striukės.', 'sort_order' => 3, 'image_url' => 'local://category/striukes.webp'],
                    ['name' => 'Kepurės', 'slug' => 'kepures', 'description' => 'Darbo kepurės.', 'sort_order' => 4, 'image_url' => 'local://category/kepures.webp'],
                    ['name' => 'Pirštinės', 'slug' => 'pirstines', 'description' => 'Apsauginės pirštinės.', 'sort_order' => 5, 'image_url' => 'local://category/pirstines.webp'],
                    ['name' => 'Kojinės', 'slug' => 'kojines', 'description' => 'Darbo kojinės.', 'sort_order' => 6, 'image_url' => 'local://category/kojines.webp'],
                    ['name' => 'Batai', 'slug' => 'batai', 'description' => 'Apsauginė avalynė.', 'sort_order' => 7, 'image_url' => 'local://category/batai.webp'],
                    ['name' => 'Akiniai', 'slug' => 'akiniai', 'description' => 'Apsauginiai akiniai.', 'sort_order' => 8, 'image_url' => 'local://category/akiniai.webp'],
                    ['name' => 'Kelių apsauga', 'slug' => 'keliu-apsauga', 'description' => 'Kelio apsaugos priemonės.', 'sort_order' => 9, 'image_url' => 'local://category/keliu-apsauga.webp'],
                    ['name' => 'Ausų apsauga', 'slug' => 'ausu-apsauga', 'description' => 'Ausų apsaugos priemonės.', 'sort_order' => 10, 'image_url' => 'local://category/ausu-apsauga.webp'],
                    ['name' => 'Kita', 'slug' => 'darbo-apranga-kita', 'description' => 'Kita darbo apranga ir sauga.', 'sort_order' => 11, 'image_url' => 'local://category/darbo-kita.webp'],
                ],
            ],
        ];

        foreach ($categories as $categoryData) {
            $this->createCategory($categoryData);
        }
    }

    private function createCategory(array $data, ?int $parentId = null): void
    {
        $category = Category::firstOrCreate(
            ['slug' => $data['slug']],
            [
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'parent_id' => $parentId,
                'sort_order' => $data['sort_order'] ?? 0,
                'is_visible' => true,
            ]
        );

        $locales = $this->supportedLocales();
        $now = now();
        $rows = [];
        foreach ($locales as $loc) {
            $name = $this->translateLike($data['name'], $loc);
            $rows[] = [
                'category_id' => $category->id,
                'locale' => $loc,
                'name' => $name,
                'slug' => Str::slug($data['slug'] . '-' . $loc),  // Ensure unique slugs per locale
                'description' => $this->translateLike((string) ($data['description'] ?? ''), $loc),
                'seo_title' => $name,
                'seo_description' => $this->translateLike('Statybinių prekių kategorija.', $loc),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        DB::table('category_translations')->upsert(
            $rows,
            ['category_id', 'locale'],
            ['name', 'slug', 'description', 'seo_title', 'seo_description', 'updated_at']
        );

        if ($category && ($category->wasRecentlyCreated || !$category->hasMedia('images')) && isset($data['image_url'])) {
            $this->attachGeneratedImage($category, 'images', $data['name'] . ' Image');
        }

        if (isset($data['children']) && is_array($data['children'])) {
            foreach ($data['children'] as $child) {
                $this->createCategory($child, (int) $category->id);
            }
        }
    }

    private function attachGeneratedImage(Category $category, string $collection, string $name): void
    {
        try {
            $imagePath = $this->imageGenerator->generateCategoryImage($category->name);
            if (!file_exists($imagePath)) {
                return;
            }
            $filename = Str::slug($name) . '.webp';
            $category
                ->addMedia($imagePath)
                ->withCustomProperties(['source' => 'local_generated'])
                ->usingName($name)
                ->usingFileName($filename)
                ->toMediaCollection($collection);
            @unlink($imagePath);
        } catch (\Throwable) {
            // ignore
        }
    }

    private function supportedLocales(): array
    {
        return collect(explode(',', (string) config('app.supported_locales', 'lt,en')))
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
            // Main categories
            'Sandarinimo plėvelės ir juostos' => 'Sealing films and tapes',
            'Tvirtinimo elementai, varžtai, medvarsčiai' => 'Fastening elements, bolts, wood screws',
            'Įsukami, įkalami poliai' => 'Screw-in, driven piles',
            'Chemija statyboms' => 'Construction chemistry',
            'Įrankiai ir jų priedai' => 'Tools and accessories',
            'Stogų danga ir priedai' => 'Roofing and accessories',
            'Fasadams' => 'For facades',
            'Elektros prekės' => 'Electrical products',
            'Darbo apranga, saugos priemonės' => 'Work clothing, safety equipment',
            // Subcategories - Sealing
            'Juostos' => 'Tapes',
            'Plėvelės' => 'Films',
            'Pamatų hidroizoliacija' => 'Foundation waterproofing',
            'Vinių sandarinimo juostos' => 'Nail sealing tapes',
            'Kitos sandarinimo medžiagos ir priedai' => 'Other sealing materials and accessories',
            'Laukui' => 'For outdoor use',
            'Vidui' => 'For indoor use',
            'Horizontali' => 'Horizontal',
            'Vertikali' => 'Vertical',
            // Subcategories - Fastening
            'Medvarsčiai' => 'Wood screws',
            'Tvirtinimas į betoną, mūrą' => 'Fastening to concrete, masonry',
            'Medžio konstrukcijų tvirtinimas' => 'Wood construction fastening',
            'Sriegiai, varžtai, veržlės, poveržlės' => 'Threads, bolts, nuts, washers',
            'Kniedės' => 'Rivets',
            'Vinys, kabės' => 'Nails, hooks',
            'Lengvas tvirtinimas' => 'Light fastening',
            'Tvirtinimo juostos' => 'Fastening strips',
            'Išlyginimo kaladėlė, kyliai ir kt.' => 'Leveling shims, wedges, etc.',
            'Termoizoliaciniai kaiščiai' => 'Thermal insulation dowels',
            // Wood screw types
            'Įleidžiama galvute' => 'Countersunk head',
            'Plokščia galvute' => 'Flat head',
            'Pusapvale galvute' => 'Round head',
            'Šešiakampe galvute' => 'Hex head',
            'Konstrukciniai' => 'Structural',
            'Terasiniai' => 'For terraces',
            'Stoginiai' => 'For roofing',
            'Savigręžiai' => 'Self-tapping',
            'Reguliuojami' => 'Adjustable',
            // Concrete/masonry fastening
            'Su plastikiniu kaiščiu' => 'With plastic dowel',
            'Su nailoniniu kaiščiu' => 'With nylon dowel',
            'Inkariniai' => 'Anchor',
            'Sraigtai į betoną' => 'Screws for concrete',
            'Sraigtai į mūrą' => 'Screws for masonry',
            'Įbetonuojami ankeriai, sriegiai, detalės' => 'Embedded anchors, threads, parts',
            'Greitvinės' => 'Quick nails',
            // Wood construction
            'Plokštelės' => 'Plates',
            'Kampai' => 'Angles',
            'Sijų atrama' => 'Beam support',
            'Gegnių sujungimai' => 'Rafter connections',
            'Paslėptas tvirtinimas' => 'Hidden fastening',
            'Kolonų atramos (reguliuojamos, įbetonuojamos)' => 'Column supports (adjustable, embedded)',
            'Kita' => 'Other',
            // Nails and hooks
            'Palaidos' => 'Hidden',
            'Pistoletinės' => 'Pistol',
            // Light fastening
            'Gipsui' => 'For gypsum',
            'Į termoizoliaciją' => 'Into thermal insulation',
            'Laikikliai ir kt.' => 'Holders and etc.',
            // Piles
            'HDG' => 'HDG',
            'HEX HDG' => 'HEX HDG',
            // Construction chemistry
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
            // Tools
            'Pieštukai' => 'Pencils',
            'Matavimo' => 'Measuring',
            'Armatūrai rišti' => 'For tying reinforcement',
            'EPDM darbui skirti įrankiai' => 'Tools for EPDM work',
            'Grąžtai metalui' => 'Drills for metal',
            'Grąžtai medžiui' => 'Drills for wood',
            'Plaktukai' => 'Hammers',
            'Lankstytuvai, žirklės skardai' => 'Benders, tin snips',
            'Kniedikliai' => 'Riveters',
            'Atsuktuvai' => 'Screwdrivers',
            'Replės' => 'Wrenches',
            'Pjūklai' => 'Saws',
            'Laužtuvai' => 'Demolition tools',
            // Roofing
            'EPDM medžiaga ir įrankiai montavimui' => 'EPDM material and installation tools',
            'Stogų priedai' => 'Roofing accessories',
            'Ventiliuojami profiliai' => 'Ventilated profiles',
            'Paukščių užtvaros' => 'Bird barriers',
            'Latakų apsauga' => 'Gutter protection',
            'Kraigo tarpinės' => 'Ridge spacers',
            'Vinių sandarinimo juostos' => 'Nail sealing tapes',
            // Facades
            'Ventiliuojami fasadai' => 'Ventilated facades',
            'Akmenys, stount' => 'Stones, stount',
            'Priedai fasadams' => 'Facade accessories',
            'Kiti fasadai' => 'Other facades',
            'Pelių barjerai' => 'Rodent barriers',
            'Įvairūs tinkliukai' => 'Various meshes',
            'Ventiliuojami profiliai' => 'Ventilated profiles',
            'Fasadiniai varžtai' => 'Facade screws',
            // Electrical
            'Izoliacinės juostos' => 'Insulating tapes',
            'Laidų sujungimas' => 'Wire connection',
            'Laidų tvirtinimas' => 'Wire fastening',
            // Work clothing
            'Kelnės' => 'Trousers',
            'Džemperiai' => 'Jumpers',
            'Striukės' => 'Jackets',
            'Kepurės' => 'Caps',
            'Pirštinės' => 'Gloves',
            'Kojinės' => 'Socks',
            'Batai' => 'Shoes',
            'Akiniai' => 'Glasses',
            'Kelių apsauga' => 'Knee protection',
            'Ausų apsauga' => 'Ear protection',
        ];

        return $translations[$text] ?? $text;
    }

    private function translateToRussian(string $text): string
    {
        $translations = [
            // Main categories
            'Sandarinimo plėvelės ir juostos' => 'Герметизирующие пленки и ленты',
            'Tvirtinimo elementai, varžtai, medvarsčiai' => 'Крепежные элементы, болты, шурупы',
            'Įsukami, įkalami poliai' => 'Винтовые, забивные сваи',
            'Chemija statyboms' => 'Строительная химия',
            'Įrankiai ir jų priedai' => 'Инструменты и аксессуары',
            'Stogų danga ir priedai' => 'Кровельные материалы и аксессуары',
            'Fasadams' => 'Для фасадов',
            'Elektros prekės' => 'Электротовары',
            'Darbo apranga, saugos priemonės' => 'Рабочая одежда, средства защиты',
            // Subcategories - Sealing
            'Juostos' => 'Ленты',
            'Plėvelės' => 'Пленки',
            'Pamatų hidroizoliacija' => 'Гидроизоляция фундамента',
            'Vinių sandarinimo juostos' => 'Герметизирующие ленты для гвоздей',
            'Kitos sandarinimo medžiagos ir priedai' => 'Другие герметизирующие материалы и аксессуары',
            'Laukui' => 'Для наружного применения',
            'Vidui' => 'Для внутреннего применения',
            'Horizontali' => 'Горизонтальная',
            'Vertikali' => 'Вертикальная',
            // Subcategories - Fastening
            'Medvarsčiai' => 'Шурупы по дереву',
            'Tvirtinimas į betoną, mūrą' => 'Крепление к бетону, кладке',
            'Medžio konstrukcijų tvirtinimas' => 'Крепление деревянных конструкций',
            'Sriegiai, varžtai, veržlės, poveržlės' => 'Резьба, болты, гайки, шайбы',
            'Kniedės' => 'Заклепки',
            'Vinys, kabės' => 'Гвозди, крючки',
            'Lengvas tvirtinimas' => 'Легкое крепление',
            'Tvirtinimo juostos' => 'Крепежные ленты',
            'Išlyginimo kaladėlė, kyliai ir kt.' => 'Выравнивающие прокладки, клинья и т.д.',
            'Termoizoliaciniai kaiščiai' => 'Дюбели для теплоизоляции',
            // Wood screw types
            'Įleidžiama galvute' => 'С потайной головкой',
            'Plokščia galvute' => 'С плоской головкой',
            'Pusapvale galvute' => 'С полукруглой головкой',
            'Šešiakampe galvute' => 'С шестигранной головкой',
            'Konstrukciniai' => 'Конструкционные',
            'Terasiniai' => 'Для террас',
            'Stoginiai' => 'Для кровли',
            'Savigręžiai' => 'Саморезы',
            'Reguliuojami' => 'Регулируемые',
            // Concrete/masonry fastening
            'Su plastikiniu kaiščiu' => 'С пластиковым дюбелем',
            'Su nailoniniu kaiščiu' => 'С нейлоновым дюбелем',
            'Inkariniai' => 'Анкерные',
            'Sraigtai į betoną' => 'Винты для бетона',
            'Sraigtai į mūrą' => 'Винты для кладки',
            'Įbetonuojami ankeriai, sriegiai, detalės' => 'Заливаемые анкеры, резьба, детали',
            'Greitvinės' => 'Быстрые гвозди',
            // Wood construction
            'Plokštelės' => 'Пластины',
            'Kampai' => 'Уголки',
            'Sijų atrama' => 'Опоры балок',
            'Gegnių sujungimai' => 'Соединения стропил',
            'Paslėptas tvirtinimas' => 'Скрытое крепление',
            'Kolonų atramos (reguliuojamos, įbetonuojamos)' => 'Опоры колонн (регулируемые, заливаемые)',
            'Kita' => 'Другое',
            // Nails and hooks
            'Palaidos' => 'Скрытые',
            'Pistoletinės' => 'Пистолетные',
            // Light fastening
            'Gipsui' => 'Для гипса',
            'Į termoizoliaciją' => 'В теплоизоляцию',
            'Laikikliai ir kt.' => 'Держатели и т.д.',
            // Piles
            'HDG' => 'HDG',
            'HEX HDG' => 'HEX HDG',
            // Construction chemistry
            'Klijuojančios putos' => 'Клеящие пены',
            'Montavimo putos' => 'Монтажные пены',
            'Purškiama termoizoliacija' => 'Напыляемая теплоизоляция',
            'Klijai' => 'Клеи',
            'Hermetikai' => 'Герметики',
            'Silikonai' => 'Силиконы',
            'Akrilai' => 'Акрилы',
            'Sandarinimo mastikos' => 'Герметизирующие мастики',
            'Tepama hidroizoliacija' => 'Обмазочная гидроизоляция',
            'Putų ir kiti valikliai' => 'Очистители пены и другие',
            'Dažai' => 'Краски',
            // Tools
            'Pieštukai' => 'Карандаши',
            'Matavimo' => 'Измерительные',
            'Armatūrai rišti' => 'Для вязки арматуры',
            'EPDM darbui skirti įrankiai' => 'Инструменты для работы с EPDM',
            'Grąžtai metalui' => 'Сверла по металлу',
            'Grąžtai medžiui' => 'Сверла по дереву',
            'Plaktukai' => 'Молотки',
            'Lankstytuvai, žirklės skardai' => 'Гибщики, ножницы по жести',
            'Kniedikliai' => 'Заклепочники',
            'Atsuktuvai' => 'Отвертки',
            'Replės' => 'Ключи',
            'Pjūklai' => 'Пилы',
            'Laužtuvai' => 'Демонтажные инструменты',
            // Roofing
            'EPDM medžiaga ir įrankiai montavimui' => 'EPDM материал и инструменты для монтажа',
            'Stogų priedai' => 'Кровельные аксессуары',
            'Ventiliuojami profiliai' => 'Вентилируемые профили',
            'Paukščių užtvaros' => 'Защита от птиц',
            'Latakų apsauga' => 'Защита водостоков',
            'Kraigo tarpinės' => 'Коньковые прокладки',
            'Vinių sandarinimo juostos' => 'Герметизирующие ленты для гвоздей',
            // Facades
            'Ventiliuojami fasadai' => 'Вентилируемые фасады',
            'Akmenys, stount' => 'Камни, stount',
            'Priedai fasadams' => 'Фасадные аксессуары',
            'Kiti fasadai' => 'Другие фасады',
            'Pelių barjerai' => 'Защита от грызунов',
            'Įvairūs tinkliukai' => 'Различные сетки',
            'Ventiliuojami profiliai' => 'Вентилируемые профили',
            'Fasadiniai varžtai' => 'Фасадные винты',
            // Electrical
            'Izoliacinės juostos' => 'Изоляционные ленты',
            'Laidų sujungimas' => 'Соединение проводов',
            'Laidų tvirtinimas' => 'Крепление проводов',
            // Work clothing
            'Kelnės' => 'Брюки',
            'Džemperiai' => 'Джемперы',
            'Striukės' => 'Куртки',
            'Kepurės' => 'Кепки',
            'Pirštinės' => 'Перчатки',
            'Kojinės' => 'Носки',
            'Batai' => 'Обувь',
            'Akiniai' => 'Очки',
            'Kelių apsauga' => 'Защита коленей',
            'Ausų apsauga' => 'Защита слуха',
        ];

        return $translations[$text] ?? $text;
    }

    private function translateToGerman(string $text): string
    {
        $translations = [
            // Main categories
            'Sandarinimo plėvelės ir juostos' => 'Dichtungsfolien und -bänder',
            'Tvirtinimo elementai, varžtai, medvarsčiai' => 'Befestigungselemente, Schrauben, Holzschrauben',
            'Įsukami, įkalami poliai' => 'Einschraub-, Rammpfähle',
            'Chemija statyboms' => 'Bauchemie',
            'Įrankiai ir jų priedai' => 'Werkzeuge und Zubehör',
            'Stogų danga ir priedai' => 'Dachmaterialien und Zubehör',
            'Fasadams' => 'Für Fassaden',
            'Elektros prekės' => 'Elektroartikel',
            'Darbo apranga, saugos priemonės' => 'Arbeitskleidung, Sicherheitsausrüstung',
            // Subcategories - Sealing
            'Juostos' => 'Bänder',
            'Plėvelės' => 'Folien',
            'Pamatų hidroizoliacija' => 'Fundamentabdichtung',
            'Vinių sandarinimo juostos' => 'Nageldichtungsbänder',
            'Kitos sandarinimo medžiagos ir priedai' => 'Andere Dichtungsmaterialien und Zubehör',
            'Laukui' => 'Für Außenbereich',
            'Vidui' => 'Für Innenbereich',
            'Horizontali' => 'Horizontal',
            'Vertikali' => 'Vertikal',
            // Subcategories - Fastening
            'Medvarsčiai' => 'Holzschrauben',
            'Tvirtinimas į betoną, mūrą' => 'Befestigung in Beton, Mauerwerk',
            'Medžio konstrukcijų tvirtinimas' => 'Holzkonstruktionsbefestigung',
            'Sriegiai, varžtai, veržlės, poveržlės' => 'Gewinde, Schrauben, Muttern, Unterlegscheiben',
            'Kniedės' => 'Nieten',
            'Vinys, kabės' => 'Nägel, Haken',
            'Lengvas tvirtinimas' => 'Leichte Befestigung',
            'Tvirtinimo juostos' => 'Befestigungsbänder',
            'Išlyginimo kaladėlė, kyliai ir kt.' => 'Ausgleichsplättchen, Keile usw.',
            'Termoizoliaciniai kaiščiai' => 'Dämmstoffdübel',
            // Wood screw types
            'Įleidžiama galvute' => 'Senkkopf',
            'Plokščia galvute' => 'Flachkopf',
            'Pusapvale galvute' => 'Halbrundkopf',
            'Šešiakampe galvute' => 'Sechskantkopf',
            'Konstrukciniai' => 'Konstruktionsschrauben',
            'Terasiniai' => 'Für Terrassen',
            'Stoginiai' => 'Für Dach',
            'Savigręžiai' => 'Selbstschneidend',
            'Reguliuojami' => 'Verstellbar',
            // Concrete/masonry fastening
            'Su plastikiniu kaiščiu' => 'Mit Kunststoffdübel',
            'Su nailoniniu kaiščiu' => 'Mit Nylondübel',
            'Inkariniai' => 'Anker',
            'Sraigtai į betoną' => 'Schrauben für Beton',
            'Sraigtai į mūrą' => 'Schrauben für Mauerwerk',
            'Įbetonuojami ankeriai, sriegiai, detalės' => 'Einbetonierte Anker, Gewinde, Teile',
            'Greitvinės' => 'Schnellnägel',
            // Wood construction
            'Plokštelės' => 'Platten',
            'Kampai' => 'Winkel',
            'Sijų atrama' => 'Balkenauflager',
            'Gegnių sujungimai' => 'Sparrenverbindungen',
            'Paslėptas tvirtinimas' => 'Versteckte Befestigung',
            'Kolonų atramos (reguliuojamos, įbetonuojamos)' => 'Stützenauflager (verstellbar, einbetoniert)',
            'Kita' => 'Andere',
            // Nails and hooks
            'Palaidos' => 'Versteckt',
            'Pistoletinės' => 'Pistolen',
            // Light fastening
            'Gipsui' => 'Für Gips',
            'Į termoizoliaciją' => 'In Dämmstoff',
            'Laikikliai ir kt.' => 'Halterungen usw.',
            // Piles
            'HDG' => 'HDG',
            'HEX HDG' => 'HEX HDG',
            // Construction chemistry
            'Klijuojančios putos' => 'Klebeschäume',
            'Montavimo putos' => 'Montageschäume',
            'Purškiama termoizoliacija' => 'Aufgesprühte Dämmung',
            'Klijai' => 'Klebstoffe',
            'Hermetikai' => 'Dichtstoffe',
            'Silikonai' => 'Silikone',
            'Akrilai' => 'Acrylate',
            'Sandarinimo mastikos' => 'Dichtungsmassen',
            'Tepama hidroizoliacija' => 'Aufgetragene Abdichtung',
            'Putų ir kiti valikliai' => 'Schaum- und andere Reiniger',
            'Dažai' => 'Farben',
            // Tools
            'Pieštukai' => 'Bleistifte',
            'Matavimo' => 'Messwerkzeuge',
            'Armatūrai rišti' => 'Für Bewehrungsbindung',
            'EPDM darbui skirti įrankiai' => 'Werkzeuge für EPDM-Arbeiten',
            'Grąžtai metalui' => 'Bohrer für Metall',
            'Grąžtai medžiui' => 'Bohrer für Holz',
            'Plaktukai' => 'Hämmer',
            'Lankstytuvai, žirklės skardai' => 'Bieger, Blechscheren',
            'Kniedikliai' => 'Nietzangen',
            'Atsuktuvai' => 'Schraubendreher',
            'Replės' => 'Schraubenschlüssel',
            'Pjūklai' => 'Sägen',
            'Laužtuvai' => 'Abbruchwerkzeuge',
            // Roofing
            'EPDM medžiaga ir įrankiai montavimui' => 'EPDM-Material und Montagewerkzeuge',
            'Stogų priedai' => 'Dachzubehör',
            'Ventiliuojami profiliai' => 'Belüftete Profile',
            'Paukščių užtvaros' => 'Vogelschutz',
            'Latakų apsauga' => 'Rinnen-Schutz',
            'Kraigo tarpinės' => 'First-Abstandshalter',
            'Vinių sandarinimo juostos' => 'Nageldichtungsbänder',
            // Facades
            'Ventiliuojami fasadai' => 'Belüftete Fassaden',
            'Akmenys, stount' => 'Steine, stount',
            'Priedai fasadams' => 'Fassadenzubehör',
            'Kiti fasadai' => 'Andere Fassaden',
            'Pelių barjerai' => 'Nagerschutz',
            'Įvairūs tinkliukai' => 'Verschiedene Netze',
            'Ventiliuojami profiliai' => 'Belüftete Profile',
            'Fasadiniai varžtai' => 'Fassadenschrauben',
            // Electrical
            'Izoliacinės juostos' => 'Isolierbänder',
            'Laidų sujungimas' => 'Kabelverbindung',
            'Laidų tvirtinimas' => 'Kabelbefestigung',
            // Work clothing
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
        ];

        return $translations[$text] ?? $text;
    }
}
