<?php

declare(strict_types=1);

return [
    'navigation_label' => 'Vartotojų Preferencijos',
    'plural_model_label' => 'Vartotojų Preferencijos',
    'model_label' => 'Vartotojo Preferencija',

    // Form fields
    'user' => 'Vartotojas',
    'preference_type' => 'Preferencijos Tipas',
    'preference_key' => 'Preferencijos Raktas',
    'preference_score' => 'Preferencijos Reitingas',
    'last_updated' => 'Paskutinį Kartą Atnaujinta',
    'metadata' => 'Metaduomenys',
    'key' => 'Raktas',
    'value' => 'Reikšmė',

    // Table columns
    'created_at' => 'Sukurta',

    // Filters
    'min_score' => 'Minimalus Reitingas',
    'max_score' => 'Maksimalus Reitingas',

    // Actions
    'reset_preference' => 'Atstatyti Preferenciją',
    'reset_preferences' => 'Atstatyti Preferencijas',

    // Notifications
    'preference_reset_successfully' => 'Preferencija buvo sėkmingai atstatyta.',
    'preferences_reset_successfully' => 'Pasirinktos preferencijos buvo sėkmingai atstatytos.',

    // Preference types
    'preference_types' => [
        'category' => 'Kategorija',
        'brand' => 'Prekės Ženklas',
        'price_range' => 'Kainų Intervalas',
        'color' => 'Spalva',
        'size' => 'Dydis',
        'material' => 'Medžiaga',
        'style' => 'Stilius',
        'feature' => 'Funkcija',
    ],

    // Validation messages
    'validation' => [
        'user_id_required' => 'Vartotojas yra privalomas.',
        'preference_type_required' => 'Preferencijos tipas yra privalomas.',
        'preference_score_numeric' => 'Preferencijos reitingas turi būti skaičius.',
        'preference_score_min' => 'Preferencijos reitingas turi būti ne mažiau nei 0.',
        'preference_score_max' => 'Preferencijos reitingas negali viršyti 1.',
    ],

    // Help text
    'help' => [
        'preference_score' => 'Reitingas nuo 0 iki 1, rodantis preferencijos stiprumą.',
        'metadata' => 'Papildomi duomenys, susiję su šia preferencija.',
        'last_updated' => 'Kada ši preferencija buvo paskutinį kartą atnaujinta.',
    ],

    // Breadcrumbs
    'breadcrumbs' => [
        'index' => 'Vartotojų Preferencijos',
        'create' => 'Sukurti Vartotojo Preferenciją',
        'edit' => 'Redaguoti Vartotojo Preferenciją',
        'view' => 'Peržiūrėti Vartotojo Preferenciją',
    ],
];
