<?php

declare(strict_types=1);

return [
    'name'                         => 'Pavadinimas',
    'code'                         => 'Kodas',
    'slug'                         => 'Slug',
    'description'                  => 'Aprašymas',
    'color'                        => 'Spalva',
    'icon'                         => 'Piktograma',
    'discount_percentage'          => 'Nuolaidos procentas',
    'discount_percentage_help'     => 'Nuolaidos procentas, taikomas šiai grupei priklausantiems klientams.',
    'discount_fixed'               => 'Fiksuota nuolaida',
    'discount_fixed_help'          => 'Fiksuota eurų suma, atimama apmokėjimo metu.',
    'minimum_order_amount'         => 'Minimalus užsakymo dydis',
    'credit_limit'                 => 'Kredito limitas',
    'payment_terms'                => 'Apmokėjimo sąlygos',
    'payment_terms_due_on_receipt' => 'Apmokėti gavus',
    'payment_terms_net_15'         => 'Net 15 dienų',
    'payment_terms_net_30'         => 'Net 30 dienų',
    'payment_terms_net_45'         => 'Net 45 dienų',
    'payment_terms_net_60'         => 'Net 60 dienų',
    'is_enabled'                   => 'Įjungta',
    'is_active'                    => 'Aktyvi',
    'is_default'                   => 'Numatytoji',
    'has_special_pricing'          => 'Specialios kainos',
    'has_volume_discounts'         => 'Tūrio nuolaidos',
    'can_view_prices'              => 'Gali matyti kainas',
    'can_place_orders'             => 'Gali pateikti užsakymus',
    'can_view_catalog'             => 'Gali matyti katalogą',
    'can_use_coupons'              => 'Gali naudoti kuponus',
    'sort_order'                   => 'Rikiavimo eilė',
    'type'                         => 'Tipas',
    'conditions'                   => 'Sąlygos',
    'users_count'                  => 'Vartotojų skaičius',
    'created_at'                   => 'Sukurta',
    'updated_at'                   => 'Atnaujinta',

    // Sekcijų antraštės
    'basic_information' => 'Pagrindinė informacija',
    'pricing_settings' => 'Kainodaros nustatymai',
    'permissions' => 'Leidimai',
    'settings' => 'Nustatymai',

    // Navigation
    'navigation_label' => 'Klientų grupės',
    'navigation_group' => 'Klientų valdymas',

    // Table columns
    'table_name'                => 'Pavadinimas',
    'table_slug'                => 'Slug',
    'table_description'         => 'Aprašymas',
    'table_discount_percentage' => 'Nuolaida %',
    'table_is_enabled'          => 'Įjungta',
    'table_users_count'         => 'Vartotojai',
    'table_created_at'          => 'Sukurta',
    'table_updated_at'          => 'Atnaujinta',

    // Filters
    'filter_enabled'           => 'Įjungta',
    'filter_with_discount'     => 'Su nuolaida',
    'filter_discount_range'    => 'Nuolaidos diapazonas',
    'filter_users_count_range' => 'Vartotojų skaičiaus diapazonas',
    'filter_created_date'      => 'Sukūrimo data',

    // Actions
    'action_view'   => 'Peržiūrėti',
    'action_edit'   => 'Redaguoti',
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
    'widget_total_groups'         => 'Iš viso grupių',
    'widget_active_groups'        => 'Aktyvios grupės',
    'widget_groups_with_discount' => 'Grupės su nuolaida',
    'widget_total_customers'      => 'Iš viso klientų',
    'widget_average_discount'     => 'Vidutinė nuolaida',

    // Relations
    'relation_users'       => 'Vartotojai',
    'relation_discounts'   => 'Nuolaidos',
    'relation_price_lists' => 'Kainų sąrašai',
    'relation_campaigns'   => 'Kampanijos',

    // Form validation
    'validation_name_required'               => 'Pavadinimas yra privalomas',
    'validation_slug_required'               => 'Slug yra privalomas',
    'validation_slug_unique'                 => 'Slug jau egzistuoja',
    'validation_discount_percentage_numeric' => 'Nuolaidos procentas turi būti skaičius',
    'validation_discount_percentage_min'     => 'Nuolaidos procentas negali būti mažesnis nei 0',
    'validation_discount_percentage_max'     => 'Nuolaidos procentas negali būti didesnis nei 100',

    // Additional translations
    'no_discount'   => 'Be nuolaidos',
    'all_groups'    => 'Visos grupės',
    'enabled_only'  => 'Tik įjungtos',
    'disabled_only' => 'Tik išjungtos',
    'discount_from' => 'Nuolaida nuo',
    'discount_to'   => 'Nuolaida iki',
    'users_from'    => 'Vartotojai nuo',
    'users_to'      => 'Vartotojai iki',

    // Relation actions
    'attach_user'       => 'Pridėti vartotoją',
    'detach_user'       => 'Pašalinti vartotoją',
    'attach_discount'   => 'Pridėti nuolaidą',
    'detach_discount'   => 'Pašalinti nuolaidą',
    'attach_price_list' => 'Pridėti kainų sąrašą',
    'detach_price_list' => 'Pašalinti kainų sąrašą',
    'attach_campaign'   => 'Pridėti kampaniją',
    'detach_campaign'   => 'Pašalinti kampaniją',
    'detach_selected'   => 'Pašalinti pasirinktus',
];
