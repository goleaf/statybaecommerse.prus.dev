<?php

declare(strict_types=1);

return [
    'title' => 'Ataskaitos',
    'description' => 'Peržiūrėkite ir atsisiųskite mūsų ataskaitas',

    // Filters
    'filters' => [
        'search' => 'Paieška',
        'search_placeholder' => 'Ieškoti ataskaitų...',
        'type' => 'Tipas',
        'category' => 'Kategorija',
        'sort' => 'Rūšiavimas',
        'all_types' => 'Visi tipai',
        'all_categories' => 'Visos kategorijos',
        'sort_by_date' => 'Pagal datą',
        'sort_by_name' => 'Pagal pavadinimą',
        'sort_by_views' => 'Pagal peržiūras',
        'sort_by_downloads' => 'Pagal atsisiuntimus',
        'apply' => 'Taikyti',
        'clear' => 'Išvalyti',
    ],

    // Actions
    'actions' => [
        'view' => 'Peržiūrėti',
        'download' => 'Atsisiųsti',
        'generate' => 'Generuoti',
        'print' => 'Spausdinti',
        'title' => 'Veiksmai',
    ],

    // Stats
    'stats' => [
        'views' => 'Peržiūros',
        'downloads' => 'Atsisiuntimai',
        'created' => 'Sukurta',
        'last_generated' => 'Paskutinį kartą sugeneruota',
    ],

    // Content
    'content' => [
        'title' => 'Turinys',
    ],

    // Info
    'info' => [
        'title' => 'Informacija',
        'type' => 'Tipas',
        'category' => 'Kategorija',
        'date_range' => 'Datos intervalas',
        'created' => 'Sukurta',
        'last_generated' => 'Paskutinį kartą sugeneruota',
        'generated_by' => 'Sugeneravo',
    ],

    // Related
    'related' => [
        'title' => 'Susijusios ataskaitos',
    ],

    // Empty state
    'empty' => [
        'title' => 'Ataskaitų nerasta',
        'description' => 'Pagal jūsų paieškos kriterijus ataskaitų nerasta.',
    ],

    // Messages
    'messages' => [
        'access_denied' => 'Neturite leidimo peržiūrėti šios ataskaitos.',
        'generated_successfully' => 'Ataskaita sėkmingai sugeneruota.',
    ],

    // PDF
    'pdf' => [
        'generated_on' => 'Sugeneruota',
        'type' => 'Tipas',
        'category' => 'Kategorija',
        'date_range' => 'Datos intervalas',
        'period' => 'Laikotarpis',
        'generated_by' => 'Sugeneravo',
        'created' => 'Sukurta',
        'description' => 'Aprašymas',
        'content' => 'Turinys',
        'filters' => 'Filtrai',
        'views' => 'Peržiūros',
        'downloads' => 'Atsisiuntimai',
        'status' => 'Būsena',
        'active' => 'Aktyvus',
        'inactive' => 'Neaktyvus',
        'footer' => 'Ataskaita: :name | Sugeneruota: :date',
    ],
];
