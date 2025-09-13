<?php

return [
    'navigation_label' => 'Regionai',
    'model_label' => 'Regionas',
    'plural_model_label' => 'Regionai',
    
    // Page titles
    'title' => 'Regionai',
    'subtitle' => 'Naršykite regionus ir administracinius padalinius',
    'page_title' => 'Regionų katalogas',
    'page_description' => 'Naršykite visus prieinamus regionus ir administracinius padalinius',
    
    // Fields
    'fields' => [
        'name' => 'Pavadinimas',
        'name_official' => 'Oficialus pavadinimas',
        'code' => 'Kodas',
        'description' => 'Aprašymas',
        'is_enabled' => 'Įjungtas',
        'is_default' => 'Numatytasis',
        'country' => 'Šalis',
        'zone' => 'Zona',
        'parent' => 'Tėvinis regionas',
        'level' => 'Lygis',
        'sort_order' => 'Rūšiavimo tvarka',
        'metadata' => 'Metaduomenys',
        'created_at' => 'Sukurta',
        'updated_at' => 'Atnaujinta',
        'deleted_at' => 'Ištrinta',
        'yes' => 'Taip',
        'no' => 'Ne',
        'capital' => 'Sostinė',
    ],
    
    // Form sections
    'basic_information' => 'Pagrindinė informacija',
    'hierarchy_settings' => 'Hierarchijos nustatymai',
    'status_settings' => 'Būsenos nustatymai',
    'additional_data' => 'Papildomi duomenys',
    
    // Placeholders
    'placeholders' => [
        'name' => 'Įveskite regiono pavadinimą',
        'code' => 'Įveskite regiono kodą',
        'description' => 'Įveskite regiono aprašymą',
        'sort_order' => 'Įveskite rūšiavimo tvarką',
    ],
    
    // Help text
    'help' => [
        'name' => 'Regiono pavadinimas',
        'code' => 'Unikalus regiono kodas',
        'description' => 'Trumpas regiono aprašymas',
        'parent' => 'Pasirinkite tėvinį regioną, jei tai yra subregionas',
        'level' => 'Šio regiono administracinis lygis',
        'sort_order' => 'Tvarka, kuria šis regionas turėtų būti rodomas',
    ],
    
    // Actions
    'actions' => [
        'create' => 'Sukurti regioną',
        'edit' => 'Redaguoti regioną',
        'delete' => 'Ištrinti regioną',
        'view' => 'Peržiūrėti regioną',
        'back_to_list' => 'Grįžti į regionus',
        'view_details' => 'Peržiūrėti detales',
        'show_on_map' => 'Rodyti žemėlapyje',
    ],
    
    // Filters
    'filters' => [
        'search' => 'Paieška',
        'search_placeholder' => 'Ieškoti regionų...',
        'by_country' => 'Filtruoti pagal šalį',
        'by_zone' => 'Filtruoti pagal zoną',
        'by_level' => 'Filtruoti pagal lygį',
        'by_parent' => 'Filtruoti pagal tėvinį regioną',
        'all_countries' => 'Visos šalys',
        'all_zones' => 'Visos zonos',
        'all_levels' => 'Visi lygiai',
        'all_parents' => 'Visi tėviniai regionai',
        'enabled_only' => 'Tik įjungti',
        'disabled_only' => 'Tik išjungti',
        'default_only' => 'Tik numatytieji',
        'non_default_only' => 'Tik ne numatytieji',
        'has_children' => 'Turi vaikų',
        'has_cities' => 'Turi miestų',
        'root_regions' => 'Šakniniai regionai',
        'leaf_regions' => 'Lapai regionai',
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
    ],
    
    // Levels
    'levels' => [
        0 => 'Šaknis',
        1 => 'Valstija/Provincija',
        2 => 'Apskritis',
        3 => 'Rajonas',
        4 => 'Savivaldybė',
        5 => 'Kaimas',
    ],
    
    // Statistics
    'statistics' => [
        'total_regions' => 'Iš viso regionų',
        'total_regions_description' => 'Bendras regionų skaičius sistemoje',
        'enabled_regions' => 'Įjungti regionai',
        'enabled_regions_description' => 'Įjungtų regionų skaičius',
        'default_regions' => 'Numatytieji regionai',
        'default_regions_description' => 'Numatytųjų regionų skaičius',
        'root_regions' => 'Šakniniai regionai',
        'root_regions_description' => 'Šakninio lygio regionų skaičius',
        'regions_by_country' => 'Regionai pagal šalį',
        'regions_by_level' => 'Regionai pagal lygį',
        'recent_regions' => 'Naujausi regionai',
    ],
    
    // Widgets
    'widgets' => [
        'overview' => 'Regionų apžvalga',
        'by_country_chart' => 'Regionai pagal šalį',
        'by_level_chart' => 'Regionai pagal lygį',
        'recent_table' => 'Naujausi regionai',
        'hierarchy_tree' => 'Regionų hierarchija',
        'geographic_distribution' => 'Geografinis pasiskirstymas',
    ],
    
    // Messages
    'messages' => [
        'created' => 'Regionas sėkmingai sukurtas',
        'updated' => 'Regionas sėkmingai atnaujintas',
        'deleted' => 'Regionas sėkmingai ištrintas',
        'restored' => 'Regionas sėkmingai atkurtas',
        'no_regions_found' => 'Regionų nerasta',
        'try_different_filters' => 'Pabandykite pakeisti paieškos filtrus',
        'region_not_found' => 'Regionas nerastas',
        'cannot_delete_with_children' => 'Negalima ištrinti regiono su vaikais',
        'cannot_delete_with_cities' => 'Negalima ištrinti regiono su miestais',
    ],
    
    // Confirmations
    'confirmations' => [
        'delete' => 'Ar tikrai norite ištrinti šį regioną?',
        'delete_with_children' => 'Šis regionas turi vaikų regionų. Ar tikrai norite jį ištrinti?',
        'delete_with_cities' => 'Šis regionas turi miestų. Ar tikrai norite jį ištrinti?',
        'bulk_delete' => 'Ar tikrai norite ištrinti pasirinktus regionus?',
    ],
    
    // Empty states
    'empty_states' => [
        'no_regions' => 'Regionų nerasta',
        'no_regions_description' => 'Pradėkite sukurdami savo pirmą regioną',
        'no_regions_found' => 'Jūsų paieškos kriterijams atitinkančių regionų nerasta',
        'no_regions_found_description' => 'Pabandykite pakeisti paiešką ar filtrus',
    ],
    
    // Validation
    'validation' => [
        'name_required' => 'Regiono pavadinimas yra privalomas',
        'name_unique' => 'Regiono pavadinimas turi būti unikalus',
        'code_unique' => 'Regiono kodas turi būti unikalus',
        'parent_exists' => 'Pasirinktas tėvinis regionas neegzistuoja',
        'country_required' => 'Šalis yra privaloma',
        'level_invalid' => 'Neteisingas regiono lygis',
    ],
    
    // Details sections
    'details' => [
        'basic_info' => 'Pagrindinė informacija',
        'hierarchy_info' => 'Hierarchijos informacija',
        'geographic_info' => 'Geografinė informacija',
        'business_info' => 'Verslo informacija',
        'related_cities' => 'Susiję miestai',
        'child_regions' => 'Vaikų regionai',
        'contact_info' => 'Kontaktinė informacija',
        'actions' => 'Veiksmai',
        'major_cities' => 'Pagrindiniai miestai',
        'and_more' => 'ir dar :count',
    ],
    
    // API responses
    'api' => [
        'success' => 'Sėkmė',
        'error' => 'Klaida',
        'not_found' => 'Regionas nerastas',
        'validation_failed' => 'Patikrinimas nepavyko',
    ],
];