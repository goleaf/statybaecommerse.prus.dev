<?php

declare(strict_types=1);

return [
    // Navigation
    'navigation' => [
        'campaigns' => 'Kampanijos',
    ],

    // Models
    'models' => [
        'campaign' => 'Kampanija',
        'campaigns' => 'Kampanijos',
    ],

    // Sections
    'sections' => [
        'basic_information' => 'Pagrindinė informacija',
        'campaign_settings' => 'Kampanijos nustatymai',
        'content' => 'Turinys',
        'media' => 'Medija',
        'targeting' => 'Taikinys',
        'tracking' => 'Sekimas',
        'seo' => 'SEO',
        'automation' => 'Automatizavimas',
    ],

    // Fields
    'fields' => [
        'name' => 'Pavadinimas',
        'slug' => 'URL slug',
        'description' => 'Aprašymas',
        'type' => 'Tipas',
        'status' => 'Būsena',
        'start_date' => 'Pradžios data',
        'end_date' => 'Pabaigos data',
        'budget' => 'Biudžetas',
        'budget_limit' => 'Biudžeto limitas',
        'target_audience' => 'Tikslinė auditorija',
        'subject' => 'Tema',
        'content' => 'Turinys',
        'cta_text' => 'Kvietimo tekstas',
        'cta_url' => 'Kvietimo URL',
        'image' => 'Paveikslėlis',
        'banner' => 'Baneris',
        'banner_alt_text' => 'Banerio alternatyvus tekstas',
        'attachments' => 'Priedai',
        'target_segments' => 'Tiksliniai segmentai',
        'target_products' => 'Tiksliniai produktai',
        'target_categories' => 'Tikslinės kategorijos',
        'target_customer_groups' => 'Tikslinės klientų grupės',
        'channel' => 'Kanalas',
        'discounts' => 'Nuolaidos',
        'display_priority' => 'Rodymo prioritetas',
        'is_featured' => 'Rekomenduojama',
        'is_active' => 'Aktyvi',
        'send_notifications' => 'Siųsti pranešimus',
        'track_conversions' => 'Sekti konversijas',
        'max_uses' => 'Maksimalus naudojimų skaičius',
        'auto_pause_on_budget' => 'Stabdyti viršijus biudžetą',
        'auto_start' => 'Automatinė pradžia',
        'auto_end' => 'Automatinė pabaiga',
        'total_views' => 'Iš viso peržiūrų',
        'total_clicks' => 'Iš viso paspaudimų',
        'total_conversions' => 'Iš viso konversijų',
        'total_revenue' => 'Bendros pajamos',
        'conversion_rate' => 'Konversijos rodiklis',
        'meta_title' => 'Meta pavadinimas',
        'meta_description' => 'Meta aprašymas',
        'social_media_ready' => 'Paruošta socialiniams tinklams',
        'created_at' => 'Sukurta',
        'updated_at' => 'Atnaujinta',
    ],

    // Status
    'status' => [
        'draft' => 'Juodraštis',
        'active' => 'Aktyvi',
        'paused' => 'Pristabdyta',
        'completed' => 'Užbaigta',
        'cancelled' => 'Atšaukta',
        'scheduled' => 'Suplanuota',
    ],

    // Types
    'types' => [
        'email' => 'El. paštas',
        'sms' => 'SMS',
        'push' => 'Push pranešimas',
        'banner' => 'Baneris',
        'popup' => 'Iššokantis langas',
        'social_media' => 'Socialiniai tinklai',
        'display' => 'Ekrano reklama',
        'search' => 'Paieškos reklama',
    ],

    // Actions
    'actions' => [
        'create' => 'Sukurti kampaniją',
        'edit' => 'Redaguoti kampaniją',
        'delete' => 'Ištrinti kampaniją',
        'duplicate' => 'Dubliuoti kampaniją',
        'activate' => 'Aktyvuoti',
        'pause' => 'Pristabdyti',
        'resume' => 'Tęsti',
        'stop' => 'Sustabdyti',
        'preview' => 'Peržiūrėti',
        'test' => 'Testuoti',
        'send' => 'Siųsti',
        'schedule' => 'Suplanuoti',
    ],

    // Notifications
    'notifications' => [
        'created' => 'Kampanija sėkmingai sukurta',
        'updated' => 'Kampanija sėkmingai atnaujinta',
        'deleted' => 'Kampanija sėkmingai ištrinta',
        'activated' => 'Kampanija sėkmingai aktyvuota',
        'paused' => 'Kampanija sėkmingai pristabdyta',
        'resumed' => 'Kampanija sėkmingai tęsta',
        'stopped' => 'Kampanija sėkmingai sustabdyta',
        'sent' => 'Kampanija sėkmingai išsiųsta',
        'scheduled' => 'Kampanija sėkmingai suplanuota',
    ],

    // Filters
    'filters' => [
        'status' => 'Būsena',
        'type' => 'Tipas',
        'date_range' => 'Datos intervalas',
        'budget_range' => 'Biudžeto intervalas',
        'featured' => 'Rekomenduojamos',
        'active' => 'Aktyvios',
        'completed' => 'Užbaigtos',
        'inactive' => 'Neaktyvios',
    ],

    // Widgets
    'widgets' => [
        'total_campaigns' => 'Iš viso kampanijų',
        'active_campaigns' => 'Aktyvios kampanijos',
        'total_budget' => 'Bendras biudžetas',
        'total_revenue' => 'Bendros pajamos',
        'average_conversion_rate' => 'Vidutinis konversijos rodiklis',
        'top_performing_campaigns' => 'Geriausiai veikiančios kampanijos',
        'recent_campaigns' => 'Paskutinės kampanijos',
    ],

    'tabs' => [
        'all' => 'Visos kampanijos',
        'active' => 'Aktyvios',
        'scheduled' => 'Suplanuotos',
        'draft' => 'Juodraščiai',
        'paused' => 'Pristabdytos',
        'inactive' => 'Neaktyvios',
        'featured' => 'Išskirtinės',
    ],
];
