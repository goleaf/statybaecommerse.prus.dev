<?php

return [
    // Basic fields
    'name' => 'Pavadinimas',
    'slug' => 'Nuoroda',
    'type' => 'Tipas',
    'description' => 'Aprašymas',
    'validation_rules' => 'Validacijos taisyklės',
    'default_value' => 'Numatytoji reikšmė',
    'is_required' => 'Privalomas',
    'is_filterable' => 'Filtruojamas',
    'is_searchable' => 'Ieškomas',
    'is_visible' => 'Matomas',
    'is_editable' => 'Redaguojamas',
    'is_sortable' => 'Rūšiuojamas',
    'sort_order' => 'Rūšiavimo tvarka',
    'is_enabled' => 'Įjungtas',
    'category_id' => 'Kategorija',
    'group_name' => 'Grupės pavadinimas',
    'icon' => 'Piktograma',
    'color' => 'Spalva',
    'min_value' => 'Minimali reikšmė',
    'max_value' => 'Maksimali reikšmė',
    'step_value' => 'Žingsnio reikšmė',
    'placeholder' => 'Vietos žymeklis',
    'help_text' => 'Pagalbos tekstas',
    'meta_data' => 'Meta duomenys',

    // Helpers
    'full_display_name' => 'Pilnas rodymo pavadinimas',
    'attribute_info' => 'Atributo informacija',
    'technical_info' => 'Techninė informacija',
    'business_info' => 'Verslo informacija',
    'complete_info' => 'Pilna informacija',
    'values_count' => 'Reikšmių skaičius',
    'usage_count' => 'Naudojimo skaičius',
    'popularity_score' => 'Populiarumo balas',

    // Types
    'text' => 'Tekstas',
    'number' => 'Skaičius',
    'boolean' => 'Bulio logika',
    'select' => 'Pasirinkimas',
    'multiselect' => 'Daugkartinis pasirinkimas',
    'color' => 'Spalva',
    'date' => 'Data',
    'textarea' => 'Teksto sritis',
    'file' => 'Failas',
    'image' => 'Paveikslėlis',

    // Status
    'disabled' => 'Išjungtas',
    'required' => 'Privalomas',
    'filterable' => 'Filtruojamas',
    'standard' => 'Standartinis',
    'unknown' => 'Nežinomas',

    // Filters
    'filters' => [
        'type' => 'Tipas',
        'group_name' => 'Grupės pavadinimas',
        'required_only' => 'Tik privalomi',
        'filterable_only' => 'Tik filtruojami',
        'searchable_only' => 'Tik ieškomi',
        'enabled_only' => 'Tik įjungti',
        'with_values_only' => 'Tik su reikšmėmis',
    ],

    // Actions
    'actions' => [
        'view' => 'Peržiūrėti',
        'edit' => 'Redaguoti',
        'delete' => 'Ištrinti',
        'restore' => 'Atkurti',
        'force_delete' => 'Priverstinai ištrinti',
        'duplicate' => 'Dublikuoti',
        'merge' => 'Sujungti',
    ],

    // Empty states
    'empty_states' => [
        'no_attributes' => 'Atributų nerasta',
        'no_values' => 'Šiam atributui nėra reikšmių',
        'no_translations' => 'Vertimų nėra',
    ],

    // Messages
    'messages' => [
        'created' => 'Atributas sėkmingai sukurtas',
        'updated' => 'Atributas sėkmingai atnaujintas',
        'deleted' => 'Atributas sėkmingai ištrintas',
        'restored' => 'Atributas sėkmingai atkurtas',
        'duplicated' => 'Atributas sėkmingai dublikuotas',
        'merged' => 'Atributai sėkmingai sujungti',
    ],

    // Validation
    'validation' => [
        'name_required' => 'Pavadinimas yra privalomas',
        'slug_required' => 'Nuoroda yra privaloma',
        'type_required' => 'Tipas yra privalomas',
        'slug_unique' => 'Nuoroda turi būti unikali',
        'description_max' => 'Aprašymas negali viršyti 1000 simbolių',
        'sort_order_numeric' => 'Rūšiavimo tvarka turi būti skaičius',
    ],

    // Statistics
    'stats' => [
        'total_attributes' => 'Iš viso atributų',
        'enabled_attributes' => 'Įjungtų atributų',
        'required_attributes' => 'Privalomų atributų',
        'filterable_attributes' => 'Filtruojamų atributų',
        'searchable_attributes' => 'Ieškotų atributų',
        'attributes_with_values' => 'Atributų su reikšmėmis',
        'avg_values_per_attribute' => 'Vidutinis reikšmių skaičius atribute',
        'most_popular_type' => 'Populiariausias tipas',
        'all_attributes' => 'Visi sistemos atributai',
        'enabled_for_use' => 'Įjungti naudojimui',
        'required_for_products' => 'Privalomi produktams',
        'can_be_filtered' => 'Gali būti filtruojami',
        'can_be_searched' => 'Gali būti ieškomi',
        'have_values' => 'Atributai, kurie turi reikšmes',
        'average_values' => 'Vidutinis reikšmių skaičius',
    ],

    // Widgets
    'widgets' => [
        'stats_heading' => 'Atributų statistika',
        'types_heading' => 'Atributų tipai',
        'usage_heading' => 'Atributų naudojimas',
        'analytics_heading' => 'Atributų analitika',
        'performance_heading' => 'Atributų veikimas',
        'charts' => [
            'types_distribution' => 'Tipų pasiskirstymas',
            'usage_overview' => 'Naudojimo apžvalga',
            'popularity_trends' => 'Populiarumo tendencijos',
        ],
    ],

    // Form labels
    'attribute_name' => 'Atributo pavadinimas',
    'attribute_description' => 'Atributo aprašymas',
    'attribute_settings' => 'Atributo nustatymai',
    'attribute_settings_description' => 'Konfigūruoti pagrindinius šio atributo nustatymus',
    'attribute_properties' => 'Atributo savybės',
    'attribute_properties_description' => 'Apibrėžti šio atributo elgseną ir matomumą',
    'numeric_settings' => 'Skaitmeniniai nustatymai',
    'numeric_settings_description' => 'Konfigūruoti skaitmeninius apribojimus šiam atributui',

    // Help text
    'help' => [
        'type' => 'Šio atributo įvesties lauko tipas',
        'sort_order' => 'Tvarka, kuria atributai rodomi',
        'group_name' => 'Grupė susijusių atributų organizavimui',
        'icon' => 'Piktograma, kurią rodyti šiam atributui',
        'color' => 'Šio atributo spalvos tema',
        'required' => 'Ar šis atributas yra privalomas',
        'filterable' => 'Ar šis atributas gali būti naudojamas filtravimui',
        'searchable' => 'Ar šis atributas gali būti ieškomas',
        'visible' => 'Ar šis atributas matomas vartotojams',
        'editable' => 'Ar šis atributas gali būti redaguojamas',
        'sortable' => 'Ar šis atributas gali būti rūšiuojamas',
        'enabled' => 'Ar šis atributas yra įjungtas',
        'min_value' => 'Minimali reikšmė skaitmeniniams atributams',
        'max_value' => 'Maksimali reikšmė skaitmeniniams atributams',
        'step_value' => 'Žingsnio reikšmė skaitmeniniams atributams',
        'default_value' => 'Numatytoji šio atributo reikšmė',
        'placeholder' => 'Vietos žymeklio tekstas įvesties laukams',
        'help_text' => 'Pagalbos tekstas vartotojų vadovavimui',
        'slug_auto_generated' => 'Nuoroda bus automatiškai sugeneruota iš pavadinimo',
    ],

    // Placeholders
    'placeholders' => [
        'name' => 'Įveskite atributo pavadinimą',
        'slug' => 'Įveskite atributo nuorodą',
        'description' => 'Įveskite atributo aprašymą',
        'group_name' => 'Įveskite grupės pavadinimą',
        'icon' => 'Įveskite piktogramos pavadinimą',
        'placeholder' => 'Įveskite vietos žymeklio tekstą',
        'help_text' => 'Įveskite pagalbos tekstą',
        'default_value' => 'Įveskite numatytąją reikšmę',
    ],
];
