<?php

return [
    'navigation_label' => 'Vietos',
    'model_label' => 'Vieta',
    'plural_model_label' => 'Vietos',
    
    // Page titles
    'title' => 'Vietos',
    'subtitle' => 'Raskite mūsų parduotuves, sandėlius ir paėmimo punktus',
    'page_title' => 'Vietų katalogas',
    'page_description' => 'Naršykite visas mūsų vietas, įskaitant parduotuves, sandėlius ir paėmimo punktus',
    
    // Fields
    'fields' => [
        'name' => 'Pavadinimas',
        'description' => 'Aprašymas',
        'code' => 'Kodas',
        'slug' => 'Slug',
        'type' => 'Tipas',
        'address_line_1' => 'Adreso eilutė 1',
        'address_line_2' => 'Adreso eilutė 2',
        'city' => 'Miestas',
        'state' => 'Valstija',
        'postal_code' => 'Pašto kodas',
        'country_code' => 'Šalies kodas',
        'phone' => 'Telefonas',
        'email' => 'El. paštas',
        'is_enabled' => 'Įjungtas',
        'is_default' => 'Numatytasis',
        'latitude' => 'Platuma',
        'longitude' => 'Ilguma',
        'opening_hours' => 'Darbo valandos',
        'contact_info' => 'Kontaktinė informacija',
        'sort_order' => 'Rūšiavimo tvarka',
        'created_at' => 'Sukurta',
        'updated_at' => 'Atnaujinta',
        'deleted_at' => 'Ištrinta',
        'yes' => 'Taip',
        'no' => 'Ne',
        'coordinates' => 'Koordinatės',
        'full_address' => 'Pilnas adresas',
    ],
    
    // Form sections
    'basic_information' => 'Pagrindinė informacija',
    'address_information' => 'Adreso informacija',
    'contact_information' => 'Kontaktinė informacija',
    'location_details' => 'Vietos detalės',
    'business_settings' => 'Verslo nustatymai',
    'additional_data' => 'Papildomi duomenys',
    
    // Placeholders
    'placeholders' => [
        'name' => 'Įveskite vietos pavadinimą',
        'code' => 'Įveskite vietos kodą',
        'description' => 'Įveskite vietos aprašymą',
        'slug' => 'Įveskite vietos slug',
        'address_line_1' => 'Įveskite gatvės adresą',
        'city' => 'Įveskite miestą',
        'state' => 'Įveskite valstiją/provinciją',
        'postal_code' => 'Įveskite pašto kodą',
        'phone' => 'Įveskite telefono numerį',
        'email' => 'Įveskite el. pašto adresą',
        'latitude' => 'Įveskite platumą',
        'longitude' => 'Įveskite ilgumą',
        'sort_order' => 'Įveskite rūšiavimo tvarką',
    ],
    
    // Help text
    'help' => [
        'name' => 'Vietos pavadinimas',
        'code' => 'Unikalus vietos kodas',
        'description' => 'Trumpas vietos aprašymas',
        'type' => 'Vietos tipas (parduotuvė, sandėlis, biuras ir kt.)',
        'address_line_1' => 'Pagrindinis gatvės adresas',
        'city' => 'Miestas, kuriame yra vieta',
        'phone' => 'Kontaktinis telefono numeris',
        'email' => 'Kontaktinis el. pašto adresas',
        'latitude' => 'Geografinė platuma',
        'longitude' => 'Geografinė ilguma',
        'opening_hours' => 'Verslo darbo valandos kiekvienai dienai',
        'sort_order' => 'Tvarka, kuria ši vieta turėtų būti rodoma',
    ],
    
    // Location types
    'type_warehouse' => 'Sandėlis',
    'type_store' => 'Parduotuvė',
    'type_office' => 'Biuras',
    'type_pickup_point' => 'Paėmimo punktas',
    'type_other' => 'Kita',
    
    // Actions
    'actions' => [
        'create' => 'Sukurti vietą',
        'edit' => 'Redaguoti vietą',
        'delete' => 'Ištrinti vietą',
        'view' => 'Peržiūrėti vietą',
        'back_to_list' => 'Grįžti į vietas',
        'view_details' => 'Peržiūrėti detales',
        'show_on_map' => 'Rodyti žemėlapyje',
        'get_directions' => 'Gauti maršrutą',
        'contact_location' => 'Susisiekti su vieta',
    ],
    
    // Filters
    'filters' => [
        'search' => 'Paieška',
        'search_placeholder' => 'Ieškoti vietų...',
        'by_type' => 'Filtruoti pagal tipą',
        'by_country' => 'Filtruoti pagal šalį',
        'by_city' => 'Filtruoti pagal miestą',
        'all_types' => 'Visi tipai',
        'all_countries' => 'Visos šalys',
        'all_cities' => 'Visi miestai',
        'enabled_only' => 'Tik įjungti',
        'disabled_only' => 'Tik išjungti',
        'default_only' => 'Tik numatytieji',
        'non_default_only' => 'Tik ne numatytieji',
        'has_coordinates' => 'Turi koordinates',
        'has_opening_hours' => 'Turi darbo valandas',
        'is_open_now' => 'Atidaryta dabar',
        'apply_filters' => 'Taikyti filtrus',
        'clear_filters' => 'Išvalyti filtrus',
    ],
    
    // Status
    'status' => [
        'enabled' => 'Įjungtas',
        'disabled' => 'Išjungtas',
        'default' => 'Numatytasis',
        'active' => 'Aktyvus',
        'inactive' => 'Neaktyvus',
        'open' => 'Atidaryta',
        'closed' => 'Uždaryta',
    ],
    
    // Days of the week
    'monday' => 'Pirmadienis',
    'tuesday' => 'Antradienis',
    'wednesday' => 'Trečiadienis',
    'thursday' => 'Ketvirtadienis',
    'friday' => 'Penktadienis',
    'saturday' => 'Šeštadienis',
    'sunday' => 'Sekmadienis',
    
    // Statistics
    'statistics' => [
        'total_locations' => 'Iš viso vietų',
        'total_locations_description' => 'Bendras vietų skaičius sistemoje',
        'enabled_locations' => 'Įjungtos vietos',
        'enabled_locations_description' => 'Įjungtų vietų skaičius',
        'disabled_locations' => 'Išjungtos vietos',
        'disabled_locations_description' => 'Išjungtų vietų skaičius',
        'warehouse_count' => 'Sandėliai',
        'warehouse_count_description' => 'Sandėlių vietų skaičius',
        'store_count' => 'Parduotuvės',
        'store_count_description' => 'Parduotuvių vietų skaičius',
        'office_count' => 'Biurai',
        'office_count_description' => 'Biurų vietų skaičius',
        'pickup_point_count' => 'Paėmimo punktai',
        'pickup_point_count_description' => 'Paėmimo punktų vietų skaičius',
        'locations_by_type' => 'Vietos pagal tipą',
        'locations_count' => 'Vietų skaičius',
        'total_inventory_value' => 'Bendra inventoriaus vertė',
        'total_inventory_value_description' => 'Bendra inventoriaus vertė visose vietose',
        'total_products' => 'Iš viso produktų',
        'total_products_description' => 'Bendras produktų skaičius inventoriuje',
        'low_stock_products' => 'Produktai su mažu atsargų kiekium',
        'low_stock_products_description' => 'Produktų skaičius su mažu atsargų kiekium',
        'out_of_stock_products' => 'Produktai be atsargų',
        'out_of_stock_products_description' => 'Produktų skaičius be atsargų',
    ],
    
    // Widgets
    'widgets' => [
        'overview' => 'Vietų apžvalga',
        'by_type_chart' => 'Vietos pagal tipą',
        'inventory_overview' => 'Inventoriaus apžvalga',
        'recent_locations' => 'Naujausios vietos',
        'geographic_distribution' => 'Geografinis pasiskirstymas',
        'opening_hours_summary' => 'Darbo valandų suvestinė',
    ],
    
    // Messages
    'messages' => [
        'created' => 'Vieta sėkmingai sukurta',
        'updated' => 'Vieta sėkmingai atnaujinta',
        'deleted' => 'Vieta sėkmingai ištrinta',
        'restored' => 'Vieta sėkmingai atkurtą',
        'no_locations_found' => 'Vietų nerasta',
        'try_different_filters' => 'Pabandykite pakeisti paieškos filtrus',
        'location_not_found' => 'Vieta nerasta',
        'cannot_delete_with_inventory' => 'Negalima ištrinti vietos su inventoriumi',
        'opening_hours_updated' => 'Darbo valandos sėkmingai atnaujintos',
    ],
    
    // Confirmations
    'confirmations' => [
        'delete' => 'Ar tikrai norite ištrinti šią vietą?',
        'delete_with_inventory' => 'Ši vieta turi inventorių. Ar tikrai norite ją ištrinti?',
        'bulk_delete' => 'Ar tikrai norite ištrinti pasirinktas vietas?',
        'bulk_enable' => 'Ar tikrai norite įjungti pasirinktas vietas?',
        'bulk_disable' => 'Ar tikrai norite išjungti pasirinktas vietas?',
    ],
    
    // Empty states
    'empty_states' => [
        'no_locations' => 'Vietų nerasta',
        'no_locations_description' => 'Pradėkite sukurdami savo pirmą vietą',
        'no_locations_found' => 'Jūsų paieškos kriterijams atitinkančių vietų nerasta',
        'no_locations_found_description' => 'Pabandykite pakeisti paiešką ar filtrus',
    ],
    
    // Validation
    'validation' => [
        'name_required' => 'Vietos pavadinimas yra privalomas',
        'name_unique' => 'Vietos pavadinimas turi būti unikalus',
        'code_required' => 'Vietos kodas yra privalomas',
        'code_unique' => 'Vietos kodas turi būti unikalus',
        'type_required' => 'Vietos tipas yra privalomas',
        'type_invalid' => 'Neteisingas vietos tipas',
        'latitude_numeric' => 'Platuma turi būti skaičius',
        'longitude_numeric' => 'Ilguma turi būti skaičius',
        'phone_format' => 'Neteisingas telefono numerio formatas',
        'email_format' => 'Neteisingas el. pašto adreso formatas',
    ],
    
    // Details sections
    'details' => [
        'basic_info' => 'Pagrindinė informacija',
        'address_info' => 'Adreso informacija',
        'contact_info' => 'Kontaktinė informacija',
        'business_info' => 'Verslo informacija',
        'opening_hours_info' => 'Darbo valandos',
        'inventory_info' => 'Inventoriaus informacija',
        'actions' => 'Veiksmai',
        'related_locations' => 'Susijusios vietos',
        'nearby_locations' => 'Artimos vietos',
    ],
    
    // Bulk actions
    'bulk_enable' => 'Įjungti pasirinktus',
    'bulk_disable' => 'Išjungti pasirinktus',
    'bulk_delete' => 'Ištrinti pasirinktus',
    
    // API responses
    'api' => [
        'success' => 'Sėkmė',
        'error' => 'Klaida',
        'not_found' => 'Vieta nerasta',
        'validation_failed' => 'Patikrinimas nepavyko',
        'inventory_updated' => 'Inventorius sėkmingai atnaujintas',
    ],
];
