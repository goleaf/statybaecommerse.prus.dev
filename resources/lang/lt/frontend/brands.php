<?php

declare(strict_types=1);

return [
    'meta' => [
        'title'       => 'Prekių ženklai',
        'description' => 'Naršykite patikimų partnerių katalogą ir atraskite aukštos kokybės statybų prekės ženklus StatyBae platformoje.',
    ],
    'hero' => [
        'title'       => 'Patikimi statybų prekės ženklai',
        'description' => 'Atraskite patikimus tiekėjus, įrankių gamintojus ir medžiagų partnerius, atrinktus Baltijos statybų profesionalams.',
        'cta'         => 'Peržiūrėti kolekcijas',
        'badge'       => 'Prekių ženklų katalogas',
    ],
    'stats' => [
        'brands' => [
            'caption' => 'Aktyvūs partneriai',
        ],
        'products' => [
            'caption' => 'Siūlomi produktai',
        ],
        'promise' => [
            'label'   => 'Mūsų pažadas',
            'caption' => 'Premium kokybė',
        ],
    ],
    'filters' => [
        'title'              => 'Patikslinkite sąrašą',
        'description'        => 'Pasinaudokite paieška ir rikiavimu, kad rastumėte tinkamiausius partnerius.',
        'search_label'       => 'Ieškoti prekės ženklų',
        'search_placeholder' => 'Ieškokite prekės ženklų…',
        'sort_label'         => 'Rikiuoti pagal',
        'options'            => [
            'name'           => 'Pavadinimą A–Z',
            'name_desc'      => 'Pavadinimą Z–A',
            'products_count' => 'Daugiausia produktų',
            'created_at'     => 'Naujausi',
            'featured'       => 'Pirmiausia išskirtiniai',
        ],
        'status' => [
            'none'      => 'Filtrai nepanaudoti',
            'none_hint' => 'Rodome visus įjungtus prekės ženklus.',
            'some'      => '{1}Aktyvus 1 filtras|[2,*]Aktyvūs :count filtrai',
            'some_hint' => 'Filtrai atsinaujina akimirksniu patogiam naršymui.',
        ],
        'sync_notice' => 'Filtrai sinchronizuojami automatiškai',
        'quick'       => [
            'featured' => 'Pirmiausia išskirtiniai',
            'products' => 'Daugiausia produktų',
        ],
        'quick_actions' => 'Greiti veiksmai',
        'reset_filters' => 'Atstatyti filtrus',
        'apply_filters' => 'Pritaikyti filtrus',
    ],
    'list' => [
        'title'       => 'Prekių ženklų katalogas',
        'description' => 'StatyBae komandos atrinkti patikimi tiekėjai.',
        'badges'      => [
            'brands'   => '{0}Nėra prekės ženklų|{1}1 prekės ženklas|[2,*]:count prekės ženklai',
            'products' => '{0}Nėra produktų|{1}1 produktas|[2,*]:count produktai',
            'featured' => 'Išskirtinis',
        ],
        'featured' => [
            'title'    => 'Pabrėžti partneriai',
            'subtitle' => 'Mūsų labiausiai patikimi prekės ženklai.',
            'count'    => '{1}Pabrėžtas 1 prekės ženklas|[2,*]Pabrėžta :count prekės ženklų',
        ],
        'visit'         => 'Peržiūrėti profilį',
        'fallback_logo' => ':name logotipo vieta',
        'empty'         => [
            'title'       => 'Šiuo metu nėra prekės ženklų',
            'description' => 'Nauji partneriai pasirodys netrukus. Apsilankykite dar kartą.',
        ],
        'catalogue_count' => ':count prekės ženklai kataloge',
        'description_placeholder' => 'Išsamus aprašymas netrukus bus pateiktas.',
        'showing_results' => 'Rodoma :from–:to iš :total rezultatų',
        'no_results' => 'Nėra rodytinų rezultatų',
        'pagination_navigation' => 'Puslapių navigacija',
    ],
    'show' => [
        'spotlight' => 'Prekės ženklo apžvalga',
        'categories_title' => 'Kategorijos, kurioms šis prekės ženklas tinka',
        'products_count' => ':count produktai',
        'categories_count' => ':count kategorijos',
        'quick_filters_label' => 'Greiti filtrai:',
        'all_products' => 'Visi produktai',
        'apply' => 'Pritaikyti',
        'sort_label' => 'Rikiuoti pagal',
        'update' => 'Atnaujinti',
        'no_products' => 'Šio prekės ženklo produktų dar nerasta.',
        'sort' => [
            'featured' => 'Išskirtiniai',
            'latest' => 'Naujausi',
            'price_asc' => 'Kaina: nuo žemiausios',
            'price_desc' => 'Kaina: nuo aukščiausios',
            'bestsellers' => 'Populiariausi',
        ],
        'filters' => [
            'featured' => 'Tik išskirtiniai',
            'sale' => 'Su nuolaida',
            'in_stock' => 'Yra sandėlyje',
        ],
    ],
];
