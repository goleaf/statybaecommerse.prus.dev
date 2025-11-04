<?php

return [
    // Navigation
    'navigation' => [
        'order_shippings' => 'Užsakymų pristatymai',
    ],

    // Models
    'models' => [
        'order_shipping' => 'Užsakymo pristatymas',
        'order_shippings' => 'Užsakymų pristatymai',
    ],

    // Fields
    'fields' => [
        'order' => 'Užsakymas',
        'carrier_name' => 'Vežėjo pavadinimas',
        'service' => 'Paslauga',
        'tracking_number' => 'Sekimo numeris',
        'tracking_url' => 'Sekimo nuoroda',
        'shipped_at' => 'Išsiųsta',
        'estimated_delivery' => 'Numatomas pristatymas',
        'delivered_at' => 'Pristatyta',
        'weight' => 'Svoris',
        'cost' => 'Kaina',
        'dimensions' => 'Matmenys',
        'metadata' => 'Metaduomenys',
        'created_at' => 'Sukurta',
        'updated_at' => 'Atnaujinta',
    ],

    // Sections
    'sections' => [
        'basic_information' => 'Pagrindinė informacija',
        'shipping_information' => 'Pristatymo informacija',
        'tracking_information' => 'Sekimo informacija',
        'physical_properties' => 'Fizinės savybės',
    ],

    // Statuses
    'status' => [
        'pending' => 'Laukiantis',
        'shipped' => 'Išsiųstas',
        'delivered' => 'Pristatytas',
    ],

    // Actions
    'actions' => [
        'create' => 'Sukurti',
        'view' => 'Peržiūrėti',
        'edit' => 'Redaguoti',
        'delete' => 'Ištrinti',
        'mark_shipped' => 'Pažymėti kaip išsiųstą',
        'mark_delivered' => 'Pažymėti kaip pristatytą',
    ],

    // Bulk actions
    'bulk_mark_shipped' => 'Pažymėti kaip išsiųstus',
    'bulk_mark_delivered' => 'Pažymėti kaip pristatytus',

    // Help text
    'carrier_name_help' => 'Vežėjo pavadinimas (pvz., DHL, FedEx)',
    'service_help' => 'Pristatymo paslauga (pvz., Express, Standard)',
    'tracking_number_help' => 'Sekimo numeris',
    'tracking_url_help' => 'Sekimo nuoroda',
    'weight_help' => 'Svoris kilogramais',
    'cost_help' => 'Pristatymo kaina',
    'dimensions_help' => 'Matmenys (pvz., 30x20x10 cm)',
    'metadata_help' => 'Papildomi metaduomenys',

    // Filters
    'shipped_from' => 'Išsiųsta nuo',
    'shipped_until' => 'Išsiųsta iki',
];
