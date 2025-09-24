<?php

return [
    'title' => 'Rekomendacijų konfigūracijos',
    'plural' => 'Rekomendacijų konfigūracijos',
    'single' => 'Rekomendacijų konfigūracija',

    'basic_information' => 'Pagrindinė informacija',
    'name' => 'Pavadinimas',
    'type' => 'Tipas',
    'description' => 'Aprašymas',

    'algorithm_types' => [
        'collaborative' => 'Bendradarbiavimo filtravimas',
        'content_based' => 'Turinio pagrindu filtravimas',
        'hybrid' => 'Hibridinė rekomendacija',
        'popularity' => 'Populiarumo pagrindu',
        'trending' => 'Populiarūs produktai',
        'similarity' => 'Panašumo pagrindu',
        'custom' => 'Pritaikytas algoritmas',
    ],

    'algorithm_settings' => 'Algoritmo nustatymai',
    'min_score' => 'Minimalus balas',
    'max_results' => 'Maksimalus rezultatų skaičius',
    'decay_factor' => 'Suvėjimo koeficientas',
    'priority' => 'Prioritetas',

    'filtering' => 'Filtravimas',
    'products' => 'Produktai',
    'categories' => 'Kategorijos',
    'exclude_out_of_stock' => 'Išskirti išparduotus',
    'exclude_inactive' => 'Išskirti neaktyvius',

    'weighting' => 'Svorio koeficientai',
    'price_weight' => 'Kainos svoris',
    'rating_weight' => 'Įvertinimo svoris',
    'popularity_weight' => 'Populiarumo svoris',
    'recency_weight' => 'Naujumo svoris',
    'category_weight' => 'Kategorijos svoris',
    'custom_weight' => 'Pritaikytas svoris',

    'settings' => 'Nustatymai',
    'is_active' => 'Aktyvus',
    'is_default' => 'Pagrindinis',
    'cache_ttl' => 'Talpyklos TTL (minutės)',
    'sort_order' => 'Rūšiavimo tvarka',
    'notes' => 'Pastabos',
    'created_at' => 'Sukurta',
    'updated_at' => 'Atnaujinta',

    'products_count' => 'Produktų skaičius',
    'categories_count' => 'Kategorijų skaičius',

    'filters' => [
        'active_only' => 'Tik aktyvūs',
        'inactive_only' => 'Tik neaktyvūs',
        'default_only' => 'Tik pagrindiniai',
        'non_default_only' => 'Tik ne pagrindiniai',
        'exclude_out_of_stock_only' => 'Tik išskiriantys išparduotus',
        'include_out_of_stock_only' => 'Tik įskaitantys išparduotus',
        'exclude_inactive_only' => 'Tik išskiriantys neaktyvius',
        'include_inactive_only' => 'Tik įskaitantys neaktyvius',
    ],

    'actions' => [
        'activate' => 'Aktyvuoti',
        'deactivate' => 'Deaktyvuoti',
        'set_default' => 'Nustatyti kaip pagrindinį',
        'activated_successfully' => 'Sėkmingai aktyvuota',
        'deactivated_successfully' => 'Sėkmingai deaktyvuota',
        'set_as_default_successfully' => 'Sėkmingai nustatyta kaip pagrindinis',
        'bulk_activated_success' => 'Pasirinkti elementai sėkmingai aktyvuoti',
        'bulk_deactivated_success' => 'Pasirinkti elementai sėkmingai deaktyvuoti',
        'activate_selected' => 'Aktyvuoti pasirinktus',
        'deactivate_selected' => 'Deaktyvuoti pasirinktus',
    ],
];
