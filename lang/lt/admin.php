<?php declare(strict_types=1);

return [
    // Navigation
    'navigation' => [
        'dashboard' => 'Valdymo skydas',
        'products' => 'Produktai',
        'categories' => 'Kategorijos',
        'brands' => 'Prekės ženklai',
        'collections' => 'Kolekcijos',
        'attributes' => 'Atributai',
        'orders' => 'Užsakymai',
        'customers' => 'Klientai',
        'users' => 'Vartotojai',
        'discounts' => 'Nuolaidos',
        'campaigns' => 'Kampanijos',
        'coupons' => 'Kuponai',
        'partners' => 'Partneriai',
        'partner_tiers' => 'Partnerių lygiai',
        'customer_groups' => 'Klientų grupės',
        'reviews' => 'Atsiliepimai',
        'locations' => 'Vietos',
        'zones' => 'Zonos',
        'countries' => 'Šalys',
        'currencies' => 'Valiutos',
        'settings' => 'Nustatymai',
        'activity_log' => 'Veiklos žurnalas',
        'media' => 'Medija',
        'documents' => 'Dokumentai',
        'document_templates' => 'Dokumentų šablonai',
        'legals' => 'Teisiniai dokumentai',
        'addresses' => 'Adresai',
    ],

    // Models
    'models' => [
        'product' => 'Produktas',
        'products' => 'Produktai',
        'category' => 'Kategorija',
        'categories' => 'Kategorijos',
        'brand' => 'Prekės ženklas',
        'brands' => 'Prekės ženklai',
        'collection' => 'Kolekcija',
        'collections' => 'Kolekcijos',
        'order' => 'Užsakymas',
        'orders' => 'Užsakymai',
        'customer' => 'Klientas',
        'customers' => 'Klientai',
        'user' => 'Vartotojas',
        'users' => 'Vartotojai',
        'discount' => 'Nuolaida',
        'discounts' => 'Nuolaidos',
        'setting' => 'Nustatymas',
        'settings' => 'Nustatymai',
    ],

    // Fields
    'fields' => [
        'id' => 'ID',
        'name' => 'Pavadinimas',
        'title' => 'Pavadinimas',
        'slug' => 'Nuoroda',
        'description' => 'Aprašymas',
        'content' => 'Turinys',
        'price' => 'Kaina',
        'sku' => 'Prekės kodas',
        'stock_quantity' => 'Kiekis sandėlyje',
        'is_visible' => 'Matomas',
        'is_active' => 'Aktyvus',
        'is_public' => 'Viešas',
        'is_encrypted' => 'Šifruotas',
        'status' => 'Būsena',
        'type' => 'Tipas',
        'value' => 'Reikšmė',
        'key' => 'Raktas',
        'group' => 'Grupė',
        'sort_order' => 'Rūšiavimo tvarka',
        'validation_rules' => 'Validacijos taisyklės',
        'email' => 'El. paštas',
        'phone' => 'Telefonas',
        'created_at' => 'Sukurta',
        'updated_at' => 'Atnaujinta',
        'public' => 'Viešas',
        'encrypted' => 'Šifruotas',
    ],

    // Sections
    'sections' => [
        'basic_information' => 'Pagrindinė informacija',
        'advanced_options' => 'Išplėstinės parinktys',
        'value_configuration' => 'Reikšmės konfigūracija',
        'seo_information' => 'SEO informacija',
    ],

    // Setting Types
    'setting_types' => [
        'text' => 'Tekstas',
        'number' => 'Skaičius',
        'boolean' => 'Taip/Ne',
        'json' => 'JSON',
        'array' => 'Masyvas',
    ],

    // Help Text
    'help' => [
        'json_format' => 'Įveskite galiojantį JSON formatą',
        'public_setting' => 'Nustatymas bus matomas viešai',
        'encrypted_setting' => 'Reikšmė bus šifruojama duomenų bazėje',
        'validation_rules' => 'Laravel validacijos taisyklės',
        'slug_auto_generated' => 'Automatiškai generuojama iš pavadinimo',
        'seo_title_help' => 'Optimalus ilgis: 50-60 simbolių',
        'seo_description_help' => 'Optimalus ilgis: 150-160 simbolių',
    ],

    // Quick Actions
    'quick_actions' => [
        'create' => 'Sukurti',
        'create_product' => 'Sukurti produktą',
        'create_order' => 'Sukurti užsakymą',
        'create_user' => 'Sukurti vartotoją',
        'manage' => 'Valdyti',
        'view_products' => 'Peržiūrėti produktus',
        'view_orders' => 'Peržiūrėti užsakymus',
        'view_users' => 'Peržiūrėti vartotojus',
    ],

    // Widgets
    'widgets' => [
        'quick_actions' => 'Greiti veiksmai',
        'system_health' => 'Sistemos sveikata',
        'analytics' => 'Analitika',
        'recent_activity' => 'Paskutinė veikla',
        'low_stock_alerts' => 'Mažų atsargų perspėjimai',
    ],

    // Stats
    'stats' => [
        'total_orders' => 'Iš viso užsakymų',
        'orders_this_month' => 'Užsakymai šį mėnesį',
        'total_revenue' => 'Bendra apyvarta',
        'revenue_this_month' => 'Apyvarta šį mėnesį',
        'active_products' => 'Aktyvūs produktai',
        'products_in_stock' => 'Produktai sandėlyje',
        'registered_users' => 'Registruoti vartotojai',
        'new_users_this_week' => 'Nauji vartotojai šią savaitę',
    ],
];