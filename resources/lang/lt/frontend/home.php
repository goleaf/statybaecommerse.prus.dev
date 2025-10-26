<?php

declare(strict_types=1);

return [
    'meta' => [
        'title'       => 'Atraskite šiuolaikinę el. prekybą',
        'description' => 'Naršykite kruopščiai atrinktas kolekcijas, patikimus prekės ženklus ir asmenines rekomendacijas Baltijos pirkėjams.',
    ],
    'hero' => [
        'eyebrow'       => 'Sezono atradimai',
        'title'         => 'Mėgstamiausios prekės vienoje įkvepiančioje erdvėje',
        'subtitle'      => 'Čia rasite išskirtinius pagrindinius drabužius, vietinius perlus ir ką tik pasirodžiusias naujienas.',
        'cta_primary'   => 'Peržiūrėti išskirtinius pasiūlymus',
        'cta_secondary' => 'Naršyti naujienas',
        'featured_card' => [
            'badge'    => 'Redakcijos pasirinkimas',
            'title'    => 'Rankomis atrinkti deriniai Baltijos gyvenimo stiliui',
            'subtitle' => 'Atraskite stilistų paruoštas kolekcijas, gyvenimo būdo rinkinius ir aktualiausias tendencijas.',
            'link'     => 'Visi išskirtiniai rinkiniai',
        ],
        'secondary_cards' => [
            'new' => [
                'badge'    => 'Ką tik atkeliavo',
                'title'    => 'Naujausios prekės',
                'subtitle' => 'Kiekvieną rytą papildome riboto tiražo leidiniais ir išskirtinėmis spalvomis.',
                'link'     => 'Peržiūrėti naujienas',
            ],
            'sale' => [
                'badge'    => 'Laikina akcija',
                'title'    => 'Nariams skirtos nuolaidos',
                'subtitle' => 'Sutaupykite su kassavaitinėmis akcijomis ir palankiomis rinkinių kainomis.',
                'link'     => 'Aktyvios nuolaidos',
            ],
        ],
    ],
    'stats' => [
        'products' => [
            'label'   => 'Prekių',
            'caption' => 'Šiuo metu kataloge',
        ],
        'categories' => [
            'label'   => 'Kategorijų',
            'caption' => 'Patogiai suskirstyta',
        ],
        'brands' => [
            'label'   => 'Prekių ženklų',
            'caption' => 'Patikrinti partneriai',
        ],
        'reviews' => [
            'label'   => 'Atsiliepimų',
            'caption' => 'Bendras įvertinimas: :rating ★',
        ],
    ],
    'sections' => [
        'featured' => [
            'title'    => 'Išskirtiniai pasirinkimai',
            'subtitle' => 'Pirkimo komandos atrinkti leidimai ir redakcijos kolekcijos.',
        ],
        'catalogue' => [
            'title'    => 'Visa pasiūla vienoje vietoje',
            'subtitle' => 'Lengvai naršykite pagal kategorijas ar prekės ženklus ir raskite tai, kas tinka būtent jums.',
            'cards'    => [
                'categories' => [
                    'title'    => 'Pirkti pagal kategoriją',
                    'subtitle' => 'Lyginkite pagrindinius produktus, atraskite nišinius pasirinkimus ir sezonines kolekcijas.',
                    'link'     => 'Visos kategorijos',
                ],
                'brands' => [
                    'title'    => 'Pirkti pagal prekės ženklą',
                    'subtitle' => 'Atraskite patikimus gamintojus ir kylančius kūrėjus iš visos Europos.',
                    'link'     => 'Susipažinkite su ženklais',
                ],
            ],
            'lists' => [
                'categories' => [
                    'title'      => 'Populiariausios katalogo kategorijos',
                    'subtitle'   => 'Mūsų lankomiausi skyriai – nuo sunkiųjų įrankių iki apdailos medžiagų.',
                    'link'       => 'Visos kategorijos',
                    'item_count' => ':count įtrauktų produktų',
                    'empty'      => 'Kategorijos bus parodytos, kai tik bus publikuotos.',
                ],
                'brands' => [
                    'title'      => 'Išskirtiniai statybų prekės ženklai',
                    'subtitle'   => 'Lyderiai profesionalios įrangos, izoliacijos ir konstrukcinių sistemų srityse.',
                    'link'       => 'Visi prekės ženklai',
                    'item_count' => ':count prekių sandėlyje',
                    'empty'      => 'Prekės ženklų vitrina atsiras netrukus.',
                ],
            ],
        ],
        'highlights' => [
            'title'    => 'Kasdieninis įkvėpimas',
            'subtitle' => 'Sekite tendencijas, naujienas ir geriausius pasiūlymus, atnaujinamus kiekvieną dieną.',
            'latest'   => [
                'title' => 'Naujausios sandėlio siuntos',
                'empty' => 'Naujos atsargos bus matomos netrukus.',
            ],
            'brands' => [
                'fallback_description' => 'Baltijos statybų favoritai.',
                'cta'                  => 'Peržiūrėti prekės ženklą',
            ],
        ],
        'discovery' => [
            'title'    => 'Kodėl verta rinktis mus?',
            'subtitle' => 'Patikima, patogu ir džiuginanti apsipirkimo patirtis.',
            'items'    => [
                'recommendations' => [
                    'title'    => 'Asmeninės rekomendacijos',
                    'subtitle' => 'Išmanūs pasiūlymai pagal jūsų naršymą ir bendruomenės mėgstamiausius.',
                ],
                'security' => [
                    'title'    => 'Saugumas pirmoje vietoje',
                    'subtitle' => 'Pažangios apsaugos priemonės ir privatumo standartai užtikrina ramybę.',
                ],
                'payments' => [
                    'title'    => 'Lankstūs atsiskaitymai',
                    'subtitle' => 'Apmokėkite kortele, dalimis arba populiariais skaitmeniniais piniginiais sprendimais eurais.',
                ],
                'delivery' => [
                    'title'    => 'Patikimas pristatymas',
                    'subtitle' => 'Sekamos siuntos Baltijos šalyse bendradarbiaujant su tvariais kurjeriais.',
                ],
            ],
        ],
        'cta' => [
            'title'          => 'Prisijunkite prie mūsų pirkėjų bendruomenės',
            'subtitle'       => 'Sužinokite apie naujus leidimus, lojalumo privilegijas ir redakcijos gaires pirmieji.',
            'primary'        => 'Skaityti naujausias istorijas',
            'secondary'      => 'Susisiekite su komanda',
            'review_badge'   => 'Klientai mus vertina',
            'review_copy'    => 'Pastaruoju metu pirkėjai nuolat skiria daugiau nei keturias žvaigždes.',
            'review_caption' => 'Jau :count patikrintų atsiliepimų',
        ],
    ],
    'catalogue' => [
        'badge'    => 'Katalogas',
        'title'    => 'Atraskite mūsų katalogą',
        'subtitle' => 'Naršykite produktus pagal kategorijas, rikiuokite ir raskite tai, ko reikia.',
        'filters'  => [
            'all_categories' => 'Visos kategorijos',
            'sort_by'        => 'Rūšiuoti pagal',
        ],
        'sort' => [
            'latest'     => 'Naujausi',
            'popular'    => 'Populiariausi',
            'price_asc'  => 'Kaina: nuo žemiausios',
            'price_desc' => 'Kaina: nuo aukščiausios',
        ],
        'search_placeholder' => 'Ieškoti kataloge...',
        'empty'              => 'Šiuo metu produktų nėra.',
    ],
    'products' => [
        'badges' => [
            'sale'    => 'Akcija',
            'new'     => 'Nauja',
            'popular' => 'Populiaru',
        ],
        'stock' => [
            'in'  => 'Yra sandėlyje',
            'out' => 'Nėra sandėlyje',
        ],
        'actions' => [
            'details'     => 'Peržiūrėti',
            'add_to_cart' => 'Į krepšelį',
        ],
        'rating_out_of_5' => 'iš 5',
        'sections'        => [
            'featured' => [
                'title'    => 'Išskirtiniai produktai',
                'subtitle' => 'Mūsų atrinkti pasiūlymai',
            ],
            'latest' => [
                'title'    => 'Naujausios prekės',
                'subtitle' => 'Ką tik atvykę produktai',
            ],
            'trending' => [
                'title'    => 'Populiarūs dabar',
                'subtitle' => 'Daugiausiai peržiūrimi ir perkami',
            ],
            'sale' => [
                'title'    => 'Išpardavimas',
                'subtitle' => 'Sutaupykite šiandien',
            ],
        ],
        'empty' => 'Prekių nerasta.',
    ],
    'collections' => [
        'badge'          => 'Kolekcija',
        'open'           => 'Atidaryti kolekciją',
        'products_count' => '{0}Nėra prekių|{1}1 prekė|[2,*]:count prekės',
    ],
    'messages' => [
        'no_featured_products' => 'Išskirtiniai produktai greitai bus papildyti.',
        'no_latest_products'   => 'Naujausios prekės pasirodys vos tik bus paskelbtos.',
        'no_trending_products' => 'Populiariausi pasiūlymai netrukus bus atnaujinti.',
        'no_sale_products'     => 'Akcijos bus rodomos, kai tik jos prasidės.',
    ],
];
