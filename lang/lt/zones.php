<?php

return [
    // Basic Information
    'name' => 'Pavadinimas',
    'slug' => 'URL adresas',
    'code' => 'Kodas',
    'description' => 'Aprašymas',
    
    // Configuration
    'currency' => 'Valiuta',
    'tax_rate' => 'Mokesčio tarifas',
    'shipping_rate' => 'Pristatymo kaina',
    'sort_order' => 'Rūšiavimo tvarka',
    
    // Status
    'is_enabled' => 'Aktyvus',
    'is_default' => 'Numatytasis',
    
    // Relations
    'countries' => 'Šalys',
    'countries_count' => 'Šalių skaičius',
    'regions' => 'Regionai',
    'cities' => 'Miestai',
    'orders' => 'Užsakymai',
    'price_lists' => 'Kainų sąrašai',
    'discounts' => 'Nuolaidos',
    
    // Translations
    'translations' => 'Vertimai',
    'locale' => 'Kalba',
    'add_translation' => 'Pridėti vertimą',
    
    // Metadata
    'metadata' => 'Metaduomenys',
    'key' => 'Raktas',
    'value' => 'Reikšmė',
    
    // Timestamps
    'created_at' => 'Sukurta',
    'updated_at' => 'Atnaujinta',
    'deleted_at' => 'Ištrinta',
    
    // Actions
    'create_zone' => 'Sukurti zoną',
    'edit_zone' => 'Redaguoti zoną',
    'view_zone' => 'Peržiūrėti zoną',
    'delete_zone' => 'Ištrinti zoną',
    'duplicate_zone' => 'Dublikuoti zoną',
    
    // Messages
    'zone_created' => 'Zona sėkmingai sukurta',
    'zone_updated' => 'Zona sėkmingai atnaujinta',
    'zone_deleted' => 'Zona sėkmingai ištrinta',
    'zone_duplicated' => 'Zona sėkmingai dublikuota',
    
    // Validation
    'name_required' => 'Pavadinimas yra privalomas',
    'slug_required' => 'URL adresas yra privalomas',
    'code_required' => 'Kodas yra privalomas',
    'currency_required' => 'Valiuta yra privaloma',
    'slug_unique' => 'URL adresas jau egzistuoja',
    'code_unique' => 'Kodas jau egzistuoja',
    
    // Filters
    'filter_enabled' => 'Filtruoti pagal aktyvumą',
    'filter_default' => 'Filtruoti pagal numatytumą',
    'filter_currency' => 'Filtruoti pagal valiutą',
    'filter_countries' => 'Filtruoti pagal šalis',
    
    // Statistics
    'total_zones' => 'Iš viso zonų',
    'active_zones' => 'Aktyvių zonų',
    'default_zones' => 'Numatytųjų zonų',
    'zones_with_countries' => 'Zonų su šalimis',
    'average_tax_rate' => 'Vidutinis mokesčio tarifas',
    'total_shipping_cost' => 'Bendra pristatymo kaina',
    
    // Frontend
    'select_zone' => 'Pasirinkite zoną',
    'zone_not_found' => 'Zona nerasta',
    'shipping_to_zone' => 'Pristatymas į zoną',
    'tax_included' => 'Su mokesčiais',
    'tax_excluded' => 'Be mokesčių',
    'free_shipping' => 'Nemokamas pristatymas',
    'shipping_calculated' => 'Pristatymo kaina apskaičiuojama',
    
    // Widgets
    'zone_overview' => 'Zonų apžvalga',
    'zone_statistics' => 'Zonų statistika',
    'recent_zones' => 'Paskutinės zonos',
    'zone_performance' => 'Zonų veikla',
    'zone_distribution' => 'Zonų pasiskirstymas',
    
    // Export/Import
    'export_zones' => 'Eksportuoti zonas',
    'import_zones' => 'Importuoti zonas',
    'export_success' => 'Zonos sėkmingai eksportuotos',
    'import_success' => 'Zonos sėkmingai importuotos',
    'import_errors' => 'Importavimo klaidos',
    
    // Bulk Actions
    'bulk_enable' => 'Aktyvuoti pasirinktas',
    'bulk_disable' => 'Deaktyvuoti pasirinktas',
    'bulk_delete' => 'Ištrinti pasirinktas',
    'bulk_export' => 'Eksportuoti pasirinktas',
    
    // Search
    'search_zones' => 'Ieškoti zonų...',
    'no_zones_found' => 'Zonų nerasta',
    'search_results' => 'Paieškos rezultatai',
    
    // Help
    'zone_help' => 'Zonos pagalba',
    'zone_description_help' => 'Zonos aprašymas padeda identifikuoti jos paskirtį',
    'tax_rate_help' => 'Mokesčio tarifas procentais (pvz., 21.00)',
    'shipping_rate_help' => 'Pristatymo kaina eurais (pvz., 5.99)',
    'metadata_help' => 'Papildomi metaduomenys JSON formatu',
    
    // Additional fields
    'type' => 'Tipas',
    'type_shipping' => 'Pristatymas',
    'type_tax' => 'Mokesčiai',
    'type_payment' => 'Mokėjimas',
    'type_delivery' => 'Pristatymas',
    'type_general' => 'Bendras',
    'priority' => 'Prioritetas',
    'min_order_amount' => 'Min. užsakymo suma',
    'max_order_amount' => 'Maks. užsakymo suma',
    'free_shipping_threshold' => 'Nemokamo pristatymo riba',
    'is_active' => 'Aktyvus',
    'short_description' => 'Trumpas aprašymas',
    'long_description' => 'Išsamus aprašymas',
    'meta_title' => 'Meta pavadinimas',
    'meta_description' => 'Meta aprašymas',
    'meta_keywords' => 'Meta raktažodžiai',
    'meta_keywords_help' => 'Raktažodžiai atskirti kableliais',
    
    // Help texts
    'is_enabled_help' => 'Ar zona yra įjungta ir gali būti naudojama',
    'is_active_help' => 'Ar zona yra aktyvi sistemoje',
    'is_default_help' => 'Ar tai yra numatytoji zona',
    'priority_help' => 'Zonos prioritetas (didesnis skaičius = aukštesnis prioritetas)',
    'min_order_amount_help' => 'Minimali užsakymo suma šiai zonai',
    'max_order_amount_help' => 'Maksimali užsakymo suma šiai zonai',
    'free_shipping_threshold_help' => 'Užsakymo suma, nuo kurios pristatymas nemokamas',
    
    // Bulk actions
    'bulk_activate' => 'Aktyvuoti pasirinktas',
    'bulk_deactivate' => 'Deaktyvuoti pasirinktas',
    
    // Filters
    'has_countries' => 'Turintys šalis',
    'free_shipping_available' => 'Su nemokamu pristatymu',
    
    // Widget translations
    'all_zones' => 'Visos zonos sistemoje',
    'available_zones' => 'Galimos naudoti zonos',
    'enabled_zones_desc' => 'Įjungtos zonos',
    'default_zones_desc' => 'Numatytosios zonos',
    'shipping_zone_count' => 'Pristatymo zonos',
    'tax_zone_count' => 'Mokesčių zonos',
    'payment_zone_count' => 'Mokėjimo zonos',
    'delivery_zone_count' => 'Pristatymo zonos',
    'general_zone_count' => 'Bendros zonos',
    'zones_with_countries_desc' => 'Zonos su priskirtomis šalimis',
    'zones_with_free_shipping_desc' => 'Zonos su nemokamu pristatymu',
    'average_tax_rate_desc' => 'Vidutinis mokesčio tarifas',
    'total_shipping_cost_desc' => 'Bendra pristatymo kaina',
    'created_this_month' => 'Sukurta šį mėnesį',
    'zone_distribution' => 'Zonų pasiskirstymas',
    'zone_type_distribution_desc' => 'Zonų pasiskirstymas pagal tipą',
    'zone_count' => 'Zonų skaičius',
    'recent_zones' => 'Paskutinės zonos',
    'recent_zones_desc' => 'Paskutinės sukurtos zonos',
    
    // Frontend specific
    'zones_description' => 'Peržiūrėkite visas mūsų paslaugų zonas',
    'view_details' => 'Peržiūrėti detales',
    'no_zones_available' => 'Šiuo metu zonų nėra',
    'shipping_calculator' => 'Pristatymo skaičiuoklė',
    'order_amount' => 'Užsakymo suma',
    'weight' => 'Svoris',
    'calculate_shipping' => 'Apskaičiuoti pristatymą',
    'calculation_results' => 'Skaičiavimo rezultatai',
    'shipping_cost' => 'Pristatymo kaina',
    'tax_amount' => 'Mokesčio suma',
    'total_with_shipping' => 'Iš viso su pristatymu',
    'back_to_zones' => 'Grįžti į zonas',
    'please_enter_order_amount' => 'Įveskite užsakymo sumą',
    'calculation_error' => 'Skaičiavimo klaida',
];
