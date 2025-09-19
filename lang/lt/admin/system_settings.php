<?php

declare(strict_types=1);

return [
    // Navigation and Labels
    'navigation_label' => 'Sistemos nustatymai',
    'model_label' => 'Sistemos nustatymas',
    'plural_model_label' => 'Sistemos nustatymai',

    // Form Sections
    'setting_information' => 'Nustatymo informacija',
    'value_configuration' => 'Reikšmės konfigūracija',
    'advanced_options' => 'Papildomi nustatymai',

    // Form Fields
    'category' => 'Kategorija',
    'key' => 'Raktas',
    'key_help' => 'Unikalus nustatymo raktas (pvz., app.name)',
    'key_copied' => 'Raktas nukopijuotas',
    'name' => 'Pavadinimas',
    'group' => 'Grupė',
    'sort_order' => 'Rūšiavimo tvarka',
    'type' => 'Tipas',
    'value' => 'Reikšmė',
    'options' => 'Parinktys',
    'option_key' => 'Parinkties raktas',
    'option_value' => 'Parinkties reikšmė',
    'options_help' => 'Parinktys select tipo laukams',
    'default_value' => 'Numatytoji reikšmė',
    'default_value_help' => 'Numatytoji reikšmė, jei nustatymas nėra nustatytas',
    'description' => 'Aprašymas',
    'help_text' => 'Pagalbos tekstas',
    'help_text_help' => 'Papildomas tekstas, kuris padės vartotojams suprasti nustatymą',
    'validation_rules' => 'Validacijos taisyklės',
    'rule_name' => 'Taisyklės pavadinimas',
    'rule_value' => 'Taisyklės reikšmė',
    'validation_rules_help' => 'Laravel validacijos taisyklės (pvz., min:1, max:100)',
    'validation_message' => 'Validacijos pranešimas',
    'validation_message_help' => 'Pritaikytas validacijos klaidos pranešimas',

    // Advanced Fields
    'placeholder' => 'Vietos žymeklis',
    'placeholder_help' => 'Tekstas, kuris rodomas tuščiame lauke',
    'tooltip' => 'Paaiškinimas',
    'tooltip_help' => 'Trumpas paaiškinimas, kuris rodomas užvedus pelę',
    'metadata' => 'Metaduomenys',
    'metadata_key' => 'Metaduomenų raktas',
    'metadata_value' => 'Metaduomenų reikšmė',
    'metadata_help' => 'Papildomi metaduomenys JSON formatu',

    // Status Fields
    'is_public' => 'Viešas',
    'is_public_help' => 'Ar nustatymas gali būti pasiekiamas viešai',
    'is_required' => 'Privalomas',
    'is_required_help' => 'Ar nustatymas yra privalomas',
    'is_encrypted' => 'Šifruotas',
    'is_encrypted_help' => 'Ar nustatymo reikšmė yra šifruota',
    'is_readonly' => 'Tik skaitymui',
    'is_readonly_help' => 'Ar nustatymas gali būti redaguojamas',
    'is_active' => 'Aktyvus',
    'is_active_help' => 'Ar nustatymas yra aktyvus',
    'is_cacheable' => 'Talpinamas',
    'is_cacheable_help' => 'Ar nustatymas gali būti talpinamas',

    // Cache and Environment
    'cache_ttl' => 'Talpyklos laikas',
    'cache_ttl_help' => 'Talpyklos laikas sekundėmis',
    'seconds' => 'sek.',
    'environment' => 'Aplinka',
    'all_environments' => 'Visos aplinkos',
    'production' => 'Gamybos',
    'staging' => 'Testavimo',
    'development' => 'Plėtros',

    // Additional Fields
    'tags' => 'Žymės',
    'tags_help' => 'Žymės nustatymo kategorizavimui',
    'version' => 'Versija',
    'version_help' => 'Nustatymo versija',
    'access_count' => 'Prieigos skaičius',
    'last_accessed_at' => 'Paskutinė prieiga',

    // Table Columns
    'updated_by' => 'Atnaujino',
    'updated_at' => 'Atnaujinta',

    // Filters
    'has_dependencies' => 'Turi priklausomybių',
    'has_dependents' => 'Turi priklausančių',
    'recently_updated' => 'Neseniai atnaujinta',
    'frequently_accessed' => 'Dažnai naudojama',

    // Actions
    'view_history' => 'Žiūrėti istoriją',
    'view_dependencies' => 'Žiūrėti priklausomybes',
    'duplicate' => 'Dublikuoti',
    'export' => 'Eksportuoti',
    'activate_selected' => 'Aktyvuoti pasirinktus',
    'deactivate_selected' => 'Deaktyvuoti pasirinktus',
    'export_selected' => 'Eksportuoti pasirinktus',

    // Widgets
    'total_settings' => 'Iš viso nustatymų',
    'active_settings' => 'Aktyvūs nustatymai',
    'public_settings' => 'Vieši nustatymai',
    'encrypted_settings' => 'Šifruoti nustatymai',
    'total_categories' => 'Iš viso kategorijų',
    'active_categories' => 'Aktyvios kategorijos',
    'recent_changes' => 'Neseniai pakeista',
    'most_used_group' => 'Dažniausiai naudojama grupė: :group',
    'settings_count' => 'Nustatymų skaičius',
    'settings_by_group' => 'Nustatymai pagal grupę',
    'recent_activity' => 'Neseniai atlikti veiksmai',
    'settings_by_category' => 'Nustatymai pagal kategoriją',

    // History
    'change_type' => 'Pakeitimo tipas',
    'old_value' => 'Sena reikšmė',
    'new_value' => 'Nauja reikšmė',
    'reason' => 'Priežastis',
    'changed_by' => 'Pakeitė',
    'changed_at' => 'Pakeista',

    // Dependencies
    'dependencies' => 'Priklausomybės',
    'dependents' => 'Priklausantys',
    'condition' => 'Sąlyga',
    'condition_value' => 'Sąlygos reikšmė',

    // Common
    'yes' => 'Taip',
    'no' => 'Ne',
    'not_set' => 'Nenustatyta',
    'unknown' => 'Nežinoma',
    'created' => 'Sukurta',
    'updated' => 'Atnaujinta',
    'deleted' => 'Ištrinta',
    'equals' => 'Lygus',
    'not_equals' => 'Nelygus',
    'greater_than' => 'Didesnis nei',
    'less_than' => 'Mažesnis nei',
    'contains' => 'Turi',
    'not_contains' => 'Neturi',
    'is_empty' => 'Tuščias',
    'is_not_empty' => 'Netuščias',
    'is_true' => 'Tiesa',
    'is_false' => 'Netiesa',
    'unknown_setting' => 'Nežinomas nustatymas',
];
