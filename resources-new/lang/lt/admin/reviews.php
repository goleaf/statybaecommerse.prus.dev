<?php

return [
    // Basic fields
    'title' => 'Atsiliepimai',
    'review' => 'Atsiliepimas',
    'product' => 'Produktas',
    'reviewer' => 'Atsiliepėjas',
    'reviewer_name' => 'Atsiliepėjo vardas',
    'reviewer_email' => 'Atsiliepėjo el. paštas',
    'rating' => 'Įvertinimas',
    'review_title' => 'Atsiliepimo pavadinimas',
    'review_comment' => 'Atsiliepimo komentaras',
    'approved' => 'Patvirtintas',
    'featured' => 'Išskirtinis',
    'locale' => 'Kalbos kodas',
    'approved_at' => 'Patvirtintas',
    'rejected_at' => 'Atmestas',

    // Status
    'status' => [
        'approved' => 'Patvirtintas',
        'rejected' => 'Atmestas',
        'pending' => 'Laukiantis',
        'unknown' => 'Nežinomas',
    ],

    // Ratings
    'ratings' => [
        'poor' => 'Prastas',
        'fair' => 'Patenkinamas',
        'good' => 'Geras',
        'very_good' => 'Labai geras',
        'excellent' => 'Puikus',
        'unknown' => 'Nežinomas',
    ],

    // Actions
    'actions' => [
        'create_review' => 'Sukurti atsiliepimą',
        'approve' => 'Patvirtinti',
        'reject' => 'Atmesti',
        'feature' => 'Išskirti',
        'unfeature' => 'Neišskirti',
        'approve_selected' => 'Patvirtinti pasirinktus',
        'reject_selected' => 'Atmesti pasirinktus',
        'feature_selected' => 'Išskirti pasirinktus',
        'unfeature_selected' => 'Neišskirti pasirinktus',
    ],

    // Filters
    'filters' => [
        'approved_only' => 'Tik patvirtinti',
        'pending_only' => 'Tik laukiantys',
        'featured_only' => 'Tik išskirtiniai',
        'high_rated_only' => 'Tik aukštai įvertinti',
        'low_rated_only' => 'Tik žemai įvertinti',
        'recent_only' => 'Tik neseniai',
    ],

    // Settings
    'review_settings' => 'Atsiliepimo nustatymai',
    'review_settings_description' => 'Konfigūruoti pagrindinius šio atsiliepimo nustatymus',
    'rating_help' => 'Įvertinimas turi būti nuo 1 iki 5 žvaigždučių',
    'approved_help' => 'Ar šis atsiliepimas yra patvirtintas rodymui',
    'featured_help' => 'Ar šis atsiliepimas turėtų būti išskirtinis',

    // Statistics
    'stats' => [
        'total_reviews' => 'Iš viso atsiliepimų',
        'total_reviews_description' => 'Visi sistemos atsiliepimai',
        'approved_reviews' => 'Patvirtintų atsiliepimų',
        'approved_reviews_description' => 'Atsiliepimai patvirtinti rodymui',
        'pending_reviews' => 'Laukiančių atsiliepimų',
        'pending_reviews_description' => 'Atsiliepimai laukiantys patvirtinimo',
        'featured_reviews' => 'Išskirtinių atsiliepimų',
        'featured_reviews_description' => 'Atsiliepimai pažymėti kaip išskirtiniai',
        'average_rating' => 'Vidutinis įvertinimas',
        'average_rating_description' => 'Vidutinis įvertinimas visuose patvirtintuose atsiliepimuose',
    ],

    // Widgets
    'widgets' => [
        'rating_distribution' => 'Įvertinimų pasiskirstymas',
        'review_count' => 'Atsiliepimų skaičius',
    ],

    // Empty states
    'empty_states' => [
        'no_reviews' => 'Atsiliepimų nerasta',
        'no_pending_reviews' => 'Nėra laukiančių atsiliepimų',
        'no_featured_reviews' => 'Nėra išskirtinių atsiliepimų',
    ],

    // Messages
    'messages' => [
        'created' => 'Atsiliepimas sėkmingai sukurtas',
        'updated' => 'Atsiliepimas sėkmingai atnaujintas',
        'deleted' => 'Atsiliepimas sėkmingai ištrintas',
        'approved' => 'Atsiliepimas sėkmingai patvirtintas',
        'rejected' => 'Atsiliepimas sėkmingai atmestas',
        'featured' => 'Atsiliepimas sėkmingai išskirtas',
        'unfeatured' => 'Atsiliepimas sėkmingai neišskirtas',
    ],

    // Validation
    'validation' => [
        'rating_required' => 'Įvertinimas yra privalomas',
        'rating_min' => 'Įvertinimas turi būti bent 1',
        'rating_max' => 'Įvertinimas turi būti daugiausia 5',
        'title_required' => 'Atsiliepimo pavadinimas yra privalomas',
        'comment_required' => 'Atsiliepimo komentaras yra privalomas',
        'product_required' => 'Produktas yra privalomas',
        'reviewer_name_required' => 'Atsiliepėjo vardas yra privalomas',
        'reviewer_email_required' => 'Atsiliepėjo el. paštas yra privalomas',
        'reviewer_email_email' => 'Atsiliepėjo el. paštas turi būti galiojantis el. pašto adresas',
    ],
];
