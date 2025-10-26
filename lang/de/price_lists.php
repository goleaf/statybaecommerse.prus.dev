<?php

declare(strict_types=1);

return [
    'title'  => 'Preislisten',
    'plural' => 'Preislisten',
    'single' => 'Preisliste',

    // Sections
    'basic_information' => 'Grundinformationen',
    'availability'      => 'Verfügbarkeit und Bedingungen',
    'settings'          => 'Einstellungen',

    // Fields
    'name'             => 'Name',
    'code'             => 'Code',
    'currency'         => 'Währung',
    'priority'         => 'Priorität',
    'description'      => 'Beschreibung',
    'is_enabled'       => 'Aktiviert',
    'is_default'       => 'Standard',
    'auto_apply'       => 'Automatisch anwenden',
    'starts_at'        => 'Beginn',
    'ends_at'          => 'Ende',
    'starts_at_from'   => 'Startdatum ab',
    'starts_at_until'  => 'Startdatum bis',
    'ends_at_from'     => 'Enddatum ab',
    'ends_at_until'    => 'Enddatum bis',
    'min_order_amount' => 'Mindestbestellwert',
    'max_order_amount' => 'Höchstbestellwert',
    'created_at'       => 'Erstellt am',
    'updated_at'       => 'Aktualisiert am',

    // Filters & options
    'all_records'      => 'Alle Einträge',
    'enabled_only'     => 'Nur aktivierte',
    'disabled_only'    => 'Nur deaktivierte',
    'default_only'     => 'Nur Standard',
    'non_default_only' => 'Nur Nicht-Standard',
    'auto_apply_only'  => 'Nur automatisch angewandte',
    'manual_only'      => 'Nur manuell angewandte',

    // Relation data
    'customer_group'      => 'Kundengruppe',
    'discount_percentage' => 'Rabatt in %',
    'is_active'           => 'Aktiv',
    'partner'             => 'Partner',
    'email'               => 'E-Mail',
    'phone'               => 'Telefon',
    'commission_rate'     => 'Provisionssatz',

    // Tabs
    'tabs' => [
        'all'        => 'Alle Preislisten',
        'active'     => 'Aktiv',
        'default'    => 'Standard',
        'auto_apply' => 'Automatisch anwenden',
    ],

    // Relation managers
    'relation_managers' => [
        'customer_groups' => [
            'title' => 'Kundengruppen',
        ],
        'partners' => [
            'title' => 'Partner',
        ],
        'items' => [
            'title' => 'Preislistenpositionen',
        ],
    ],

    // Widgets & stats
    'stats' => [
        'total_price_lists'                  => 'Preislisten insgesamt',
        'total_price_lists_description'      => 'Alle Preislisten im Katalog',
        'enabled_price_lists'                => 'Aktivierte Preislisten',
        'enabled_price_lists_description'    => 'Preislisten, die derzeit aktiviert sind',
        'active_price_lists'                 => 'Aktive Preislisten',
        'active_price_lists_description'     => 'Preislisten, die aktuell laufen',
        'default_price_lists'                => 'Standard-Preislisten',
        'default_price_lists_description'    => 'Preislisten, die automatisch angewendet werden',
        'auto_apply_price_lists'             => 'Automatisch angewandte Preislisten',
        'auto_apply_price_lists_description' => 'Preislisten, die Kund:innen automatisch zugewiesen werden',
    ],

    'charts' => [
        'activity_over_time'  => 'Preislistenaktivität im Zeitverlauf',
        'price_lists_created' => 'Erstellte Preislisten',
    ],
];
