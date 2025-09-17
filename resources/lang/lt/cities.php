<?php

return [
    // Navigation
    'navigation_label' => 'Miestai',
    'navigation_group' => 'Turinys',
    'model_label' => 'Miestas',
    'plural_model_label' => 'Miestai',

    // Basic Information
    'basic_information' => 'Pagrindinė informacija',
    'name' => 'Pavadinimas',
    'slug' => 'URL nuoroda',
    'code' => 'Kodas',
    'description' => 'Aprašymas',

    // Location
    'location' => 'Vietovė',
    'country' => 'Šalis',
    'zone' => 'Zona',
    'region' => 'Regionas',
    'parent_city' => 'Tėvinis miestas',
    'level' => 'Lygis',
    'level_city' => 'Miestas',
    'level_district' => 'Rajonas',
    'level_neighborhood' => 'Mikrorajonas',
    'level_suburb' => 'Priemiestis',
    'level_help' => 'Hierarchijos lygis: 0=miestas, 1=rajonas, 2=mikrorajonas, 3=priemiestis',

    // Geographic Data
    'geographic_data' => 'Geografiniai duomenys',
    'latitude' => 'Platuma',
    'longitude' => 'Ilguma',
    'population' => 'Gyventojų skaičius',
    'postal_codes' => 'Pašto kodai',
    'postal_codes_placeholder' => 'Įveskite pašto kodus',

    // Status
    'status' => 'Būsena',
    'is_enabled' => 'Įjungta',
    'is_default' => 'Pagal nutylėjimą',
    'is_capital' => 'Sostinė',
    'is_active' => 'Aktyvus',
    'sort_order' => 'Rikiavimo tvarka',

    // Translations
    'translations' => 'Vertimai',
    'locale' => 'Kalbos kodas',
    'locale_lt' => 'Lietuvių kalba',
    'locale_en' => 'Anglų kalba',
    'locale_de' => 'Vokiečių kalba',
    'locale_ru' => 'Rusų kalba',
    'add_translation' => 'Pridėti vertimą',

    // Metadata
    'metadata' => 'Metaduomenys',
    'key' => 'Raktas',
    'value' => 'Reikšmė',

    // Actions
    'view' => 'Peržiūrėti',
    'edit' => 'Redaguoti',
    'delete' => 'Ištrinti',
    'bulk_delete' => 'Ištrinti pažymėtus',
    'create' => 'Sukurti',
    'save' => 'Išsaugoti',
    'cancel' => 'Atšaukti',

    // Filters
    'filter_all' => 'Visi',
    'filter_enabled' => 'Įjungti',
    'filter_disabled' => 'Išjungti',
    'filter_capital' => 'Sostinės',
    'filter_non_capital' => 'Ne sostinės',
    'filter_default' => 'Pagal nutylėjimą',
    'filter_non_default' => 'Ne pagal nutylėjimą',
    'with_coordinates' => 'Su koordinatėmis',
    'with_population' => 'Su gyventojų skaičiumi',
    'population_from' => 'Gyventojų skaičius nuo',
    'population_to' => 'Gyventojų skaičius iki',

    // Messages
    'created_successfully' => 'Miestas sėkmingai sukurtas',
    'updated_successfully' => 'Miestas sėkmingai atnaujintas',
    'deleted_successfully' => 'Miestas sėkmingai ištrintas',
    'bulk_deleted_successfully' => 'Pažymėti miestai sėkmingai ištrinti',
    'restored_successfully' => 'Miestas sėkmingai atkurtas',
    'force_deleted_successfully' => 'Miestas visam laikui ištrintas',

    // Validation
    'validation_name_required' => 'Pavadinimas yra privalomas',
    'validation_name_max' => 'Pavadinimas negali būti ilgesnis nei 255 simboliai',
    'validation_slug_required' => 'URL nuoroda yra privaloma',
    'validation_slug_unique' => 'URL nuoroda jau egzistuoja',
    'validation_code_required' => 'Kodas yra privalomas',
    'validation_code_unique' => 'Kodas jau egzistuoja',
    'validation_country_required' => 'Šalis yra privaloma',

    // Statistics
    'total_cities' => 'Iš viso miestų',
    'enabled_cities' => 'Įjungti miestai',
    'capital_cities' => 'Sostinės',
    'cities_with_population' => 'Miestai su gyventojų skaičiumi',
    'cities_with_coordinates' => 'Miestai su koordinatėmis',

    // Frontend
    'select_city' => 'Pasirinkite miestą',
    'search_cities' => 'Ieškoti miestų...',
    'no_cities_found' => 'Miestų nerasta',
    'city_details' => 'Miesto detalės',
    'related_cities' => 'Susiję miestai',
    'nearby_cities' => 'Artimiausi miestai',

    // Additional fields
    'type' => 'Tipas',
    'area' => 'Plotas',
    'density' => 'Tankumas',
    'elevation' => 'Aukštis virš jūros lygio',
    'timezone' => 'Laiko juosta',
    'currency_code' => 'Valiutos kodas',
    'currency_symbol' => 'Valiutos simbolis',
    'language_code' => 'Kalbos kodas',
    'language_name' => 'Kalbos pavadinimas',
    'phone_code' => 'Telefono kodas',
    'postal_code' => 'Pašto kodas',

    // Timestamps
    'created_at' => 'Sukurta',
    'updated_at' => 'Atnaujinta',
    'deleted_at' => 'Ištrinta',

    // Export/Import
    'export' => 'Eksportuoti',
    'import' => 'Importuoti',
    'export_cities' => 'Eksportuoti miestus',
    'import_cities' => 'Importuoti miestus',

    // Bulk Actions
    'bulk_actions' => 'Masinės operacijos',
    'bulk_enable' => 'Įjungti pažymėtus',
    'bulk_disable' => 'Išjungti pažymėtus',
    'bulk_set_as_capital' => 'Nustatyti kaip sostines',
    'bulk_remove_capital' => 'Pašalinti iš sostinių',
];
