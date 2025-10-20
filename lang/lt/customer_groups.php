<?php

return [
    // Ištekliaus pavadinimai
    'single' => 'Klientų grupė',
    'plural' => 'Klientų grupės',

    // Laukai
    'name' => 'Pavadinimas',
    'code' => 'Kodas',
    'slug' => 'Slug',
    'description' => 'Aprašymas',
    'discount_percentage' => 'Nuolaidos procentas',
    'discount_percentage_help' => 'Nustatykite procentinę nuolaidą šiai grupei.',
    'discount_fixed' => 'Fiksuota nuolaida',
    'discount_fixed_help' => 'Nustatykite fiksuotą nuolaidos sumą šiai grupei.',
    'has_special_pricing' => 'Specialios kainos',
    'has_volume_discounts' => 'Kiekio nuolaidos',
    'can_view_prices' => 'Gali matyti kainas',
    'can_place_orders' => 'Gali pateikti užsakymus',
    'can_view_catalog' => 'Gali peržiūrėti katalogą',
    'can_use_coupons' => 'Gali naudoti kuponus',
    'is_enabled' => 'Įjungta',
    'is_active' => 'Aktyvi',
    'is_default' => 'Numatytasis',
    'sort_order' => 'Rikiavimo tvarka',
    'type' => 'Tipas',
    'customers_count' => 'Klientų skaičius',
    'conditions' => 'Sąlygos',
    'users_count' => 'Vartotojų skaičius',
    'created_at' => 'Sukurta',
    'updated_at' => 'Atnaujinta',

    // Sekcijų antraštės
    'basic_information' => 'Pagrindinė informacija',
    'pricing_settings' => 'Kainodaros nustatymai',
    'permissions' => 'Leidimai',
    'settings' => 'Nustatymai',

    // Navigation
    'navigation_label' => 'Klientų grupės',
    'navigation_group' => 'Klientų valdymas',

    // Table columns
    'table_name' => 'Pavadinimas',
    'table_code' => 'Kodas',
    'table_slug' => 'Slug',
    'table_description' => 'Aprašymas',
    'table_discount_percentage' => 'Nuolaida %',
    'table_is_enabled' => 'Įjungta',
    'table_customers_count' => 'Klientai',
    'table_users_count' => 'Vartotojai',
    'table_created_at' => 'Sukurta',
    'table_updated_at' => 'Atnaujinta',

    // Filters
    'filter_enabled' => 'Įjungta',
    'filter_with_discount' => 'Su nuolaida',
    'filter_discount_range' => 'Nuolaidos diapazonas',
    'filter_users_count_range' => 'Vartotojų skaičiaus diapazonas',
    'filter_created_date' => 'Sukūrimo data',
    'active_only' => 'Tik aktyvūs',
    'inactive_only' => 'Tik neaktyvūs',
    'default_only' => 'Tik numatytieji',
    'non_default_only' => 'Tik nenumatytieji',
    'special_pricing_only' => 'Tik su specialiomis kainomis',
    'no_special_pricing' => 'Be specialių kainų',
    'volume_discounts_only' => 'Tik su kiekio nuolaidomis',
    'no_volume_discounts' => 'Be kiekio nuolaidų',

    // Actions
    'action_view' => 'Peržiūrėti',
    'action_edit' => 'Redaguoti',
    'action_delete' => 'Ištrinti',
    'action_create' => 'Sukurti naują',
    'activate' => 'Aktyvuoti',
    'deactivate' => 'Deaktyvuoti',
    'set_default' => 'Nustatyti numatytuoju',
    'activate_selected' => 'Aktyvuoti pasirinktus',
    'deactivate_selected' => 'Deaktyvuoti pasirinktus',

    // Messages
    'created_successfully' => 'Klientų grupė sėkmingai sukurta',
    'updated_successfully' => 'Klientų grupė sėkmingai atnaujinta',
    'deleted_successfully' => 'Klientų grupė sėkmingai ištrinta',
    'activated_successfully' => 'Klientų grupė sėkmingai aktyvuota',
    'deactivated_successfully' => 'Klientų grupė sėkmingai deaktyvuota',
    'set_as_default_successfully' => 'Klientų grupė sėkmingai nustatyta kaip numatytoji',
    'bulk_activated_success' => 'Pasirinktos klientų grupės sėkmingai aktyvuotos',
    'bulk_deactivated_success' => 'Pasirinktos klientų grupės sėkmingai deaktyvuotos',

    // Widgets
    'widget_total_groups' => 'Iš viso grupių',
    'widget_active_groups' => 'Aktyvios grupės',
    'widget_groups_with_discount' => 'Grupės su nuolaida',
    'widget_total_customers' => 'Iš viso klientų',
    'widget_average_discount' => 'Vidutinė nuolaida',

    // Relations
    'relation_users' => 'Vartotojai',
    'relation_discounts' => 'Nuolaidos',
    'relation_price_lists' => 'Kainų sąrašai',
    'relation_campaigns' => 'Kampanijos',

    // Form validation
    'validation_name_required' => 'Pavadinimas yra privalomas',
    'validation_slug_required' => 'Slug yra privalomas',
    'validation_slug_unique' => 'Slug jau egzistuoja',
    'validation_discount_percentage_numeric' => 'Nuolaidos procentas turi būti skaičius',
    'validation_discount_percentage_min' => 'Nuolaidos procentas negali būti mažesnis nei 0',
    'validation_discount_percentage_max' => 'Nuolaidos procentas negali būti didesnis nei 100',

    // Additional translations
    'types' => [
        'regular' => 'Standartinė',
        'vip' => 'VIP',
        'wholesale' => 'Didmeninė',
        'retail' => 'Mažmeninė',
        'corporate' => 'Verslo',
    ],
    'no_discount' => 'Be nuolaidos',
    'all_groups' => 'Visos grupės',
    'enabled_only' => 'Tik įjungtos',
    'disabled_only' => 'Tik išjungtos',
    'discount_from' => 'Nuolaida nuo',
    'discount_to' => 'Nuolaida iki',
    'users_from' => 'Vartotojai nuo',
    'users_to' => 'Vartotojai iki',

    // Relation actions
    'attach_user' => 'Pridėti vartotoją',
    'detach_user' => 'Pašalinti vartotoją',
    'attach_discount' => 'Pridėti nuolaidą',
    'detach_discount' => 'Pašalinti nuolaidą',
    'attach_price_list' => 'Pridėti kainų sąrašą',
    'detach_price_list' => 'Pašalinti kainų sąrašą',
    'attach_campaign' => 'Pridėti kampaniją',
    'detach_campaign' => 'Pašalinti kampaniją',
    'detach_selected' => 'Pašalinti pasirinktus',
];
