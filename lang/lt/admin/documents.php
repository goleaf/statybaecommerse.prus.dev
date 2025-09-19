<?php

return [
    'title' => 'Dokumentai',
    'plural' => 'Dokumentai',
    'single' => 'Dokumentas',
    'form' => [
        'tabs' => [
            'basic_information' => 'Pagrindinė informacija',
            'variables' => 'Kintamieji',
            'organization' => 'Organizacija',
            'file_management' => 'Failų valdymas',
        ],
        'sections' => [
            'basic_information' => 'Pagrindinė informacija',
            'variables' => 'Kintamieji',
            'organization' => 'Organizacija',
            'file_management' => 'Failų valdymas',
        ],
        'fields' => [
            'id' => 'ID',
            'title' => 'Pavadinimas',
            'content' => 'Turinys',
            'status' => 'Būsena',
            'format' => 'Formatas',
            'template' => 'Šablonas',
            'variables' => 'Kintamieji',
            'variable_name' => 'Kintamojo pavadinimas',
            'variable_value' => 'Kintamojo reikšmė',
            'documentable_type' => 'Susijusio modelio tipas',
            'documentable_id' => 'Susijusio modelio ID',
            'created_by' => 'Sukūrė',
            'created_at' => 'Sukurta',
            'generated_at' => 'Sugeneruota',
            'file_path' => 'Failo kelias',
            'file_attached' => 'Failas pridėtas',
            'is_public' => 'Viešas',
        ],
        'actions' => [
            'add_variable' => 'Pridėti kintamąjį',
        ],
        'help' => [
            'variables' => 'Kintamieji gali būti naudojami dokumento turinyje. Naudokite formatą {{kintamojo_pavadinimas}} savo turinyje.',
        ],
    ],
    'status' => [
        'draft' => 'Juodraštis',
        'generated' => 'Sugeneruotas',
        'published' => 'Paskelbtas',
        'archived' => 'Archyvuotas',
    ],
    'actions' => [
        'generate' => 'Generuoti',
        'publish' => 'Paskelbti',
        'archive' => 'Archyvuoti',
        'download' => 'Atsisiųsti',
    ],
    'filters' => [
        'is_generated' => 'Sugeneruotas',
        'has_file' => 'Turi failą',
        'created_at' => 'Sukurta',
        'generated_at' => 'Sugeneruota',
        'recent' => 'Naujausi (paskutiniai 7 dienos)',
        'old_documents' => 'Seni dokumentai (30+ dienų)',
    ],
    'groups' => [
        'status' => 'Būsena',
        'format' => 'Formatas',
        'template' => 'Šablonas',
    ],
    'generated_successfully' => 'Dokumentas sėkmingai sugeneruotas',
    'published_successfully' => 'Dokumentas sėkmingai paskelbtas',
    'archived_successfully' => 'Dokumentas sėkmingai archyvuotas',
];


