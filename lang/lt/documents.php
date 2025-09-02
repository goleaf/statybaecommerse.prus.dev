<?php declare(strict_types=1);

return [
    // General
    'documents' => 'Dokumentai',
    'document' => 'Dokumentas',
    'templates' => 'Šablonai',
    'template' => 'Šablonas',
    'generate_document' => 'Generuoti dokumentą',
    'document_generated' => 'Dokumentas sėkmingai sugeneruotas',
    'generate' => 'Generuoti',
    'generate_pdf' => 'Generuoti PDF',
    'download' => 'Atsisiųsti',
    'yes' => 'Taip',
    'no' => 'Ne',

    // Fields
    'name' => 'Pavadinimas',
    'slug' => 'Nuoroda',
    'title' => 'Antraštė',
    'description' => 'Aprašymas',
    'content' => 'Turinys',
    'type' => 'Tipas',
    'category' => 'Kategorija',
    'status' => 'Būsena',
    'format' => 'Formatas',
    'variables' => 'Kintamieji',
    'settings' => 'Nustatymai',
    'is_active' => 'Aktyvus',
    'created_at' => 'Sukurta',
    'created_by' => 'Sukūrė',
    'generated_at' => 'Sugeneruota',
    'file_path' => 'Failo kelias',
    'notes' => 'Pastabos',
    'documents_count' => 'Dokumentų skaičius',

    // Types
    'types' => [
        'invoice' => 'Sąskaita faktūra',
        'receipt' => 'Kvitas',
        'contract' => 'Sutartis',
        'agreement' => 'Susitarimas',
        'catalog' => 'Katalogas',
        'report' => 'Ataskaita',
        'certificate' => 'Sertifikatas',
        'document' => 'Dokumentas',
    ],

    // Categories
    'categories' => [
        'sales' => 'Pardavimai',
        'marketing' => 'Rinkodara',
        'legal' => 'Teisiniai',
        'finance' => 'Finansai',
        'operations' => 'Operacijos',
        'customer_service' => 'Klientų aptarnavimas',
    ],

    // Statuses
    'statuses' => [
        'draft' => 'Juodraštis',
        'published' => 'Publikuotas',
        'archived' => 'Archyvuotas',
    ],

    // Sections
    'template_information' => 'Šablono informacija',
    'template_content' => 'Šablono turinys',
    'print_settings' => 'Spausdinimo nustatymai',
    'document_information' => 'Dokumento informacija',
    'metadata' => 'Metaduomenys',

    // Form fields
    'variable_name' => 'Kintamojo pavadinimas',
    'variable_value' => 'Kintamojo reikšmė',
    'add_variable' => 'Pridėti kintamąjį',
    'setting_key' => 'Nustatymo raktas',
    'setting_value' => 'Nustatymo reikšmė',
    'add_setting' => 'Pridėti nustatymą',
    'related_model' => 'Susijęs modelis',
    'related_model_type' => 'Susijusio modelio tipas',
    'related_model_id' => 'Susijusio modelio ID',
    'created_from' => 'Sukurta nuo',
    'created_until' => 'Sukurta iki',

    // Help texts
    'content_help' => 'Naudokite kintamuosius kaip $CUSTOMER_NAME, $ORDER_TOTAL savo turinyje. Jie bus pakeisti tikromis reikšmėmis generuojant dokumentus.',
    'variables_help' => 'Apibrėžkite galimus šio šablono kintamuosius. Naudokite formatą: $VARIABLE_NAME',
    
    // Print-specific
    'phone' => 'Telefonas',
    'email' => 'El. paštas',
    'vat_number' => 'PVM mokėtojo kodas',
    'generated_on' => 'Sugeneruota',
    'all_rights_reserved' => 'Visos teisės saugomos',
    'preview' => 'Peržiūra',

    // Email notifications
    'email' => [
        'subject' => 'Dokumentas sugeneruotas: :title',
        'greeting' => 'Sveiki :name,',
        'generated' => 'Naujas :type dokumentas ":title" buvo sukurtas jums.',
        'details' => 'Sugeneruota :date su būsena: :status',
        'view_document' => 'Peržiūrėti dokumentą',
        'footer' => 'Ačiū, kad naudojatės mūsų paslaugomis!',
    ],

    // Database notifications
    'notification' => [
        'generated' => 'Dokumentas ":title" buvo sugeneruotas',
    ],

    // Error messages
    'errors' => [
        'dangerous_content' => 'Šablono turinys turi potencialiai pavojingų elementų',
        'malformed_html' => 'Šablono turinys turi netaisyklingą HTML',
        'generation_failed' => 'Dokumento generavimas nepavyko',
        'pdf_generation_failed' => 'PDF generavimas nepavyko',
    ],
];
