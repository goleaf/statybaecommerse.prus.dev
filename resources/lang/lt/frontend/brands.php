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
    ],
];
