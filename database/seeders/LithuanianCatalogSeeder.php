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
        $this->imageGenerator = new LocalImageGeneratorService();
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
                'slug' => Str::slug($data['slug'] . '-' . $loc), // Ensure unique slugs per locale
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
            'en' => $text . ' (EN)',
            'ru' => $text . ' (RU)',
            'de' => $text . ' (DE)',
            default => $text . ' (' . strtoupper($locale) . ')',
        };
    }
}