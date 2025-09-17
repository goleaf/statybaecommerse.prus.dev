<?php

return [
    // Sections
    'sections' => [
        'basic_information' => 'Grundinformationen',
        'campaign_settings' => 'Kampagneneinstellungen',
        'content' => 'Inhalt',
        'media' => 'Medien',
        'targeting' => 'Zielgruppenausrichtung',
    ],

    // Fields
    'fields' => [
        'name' => 'Name',
        'description' => 'Beschreibung',
        'type' => 'Typ',
        'status' => 'Status',
        'start_date' => 'Startdatum',
        'end_date' => 'Enddatum',
        'budget' => 'Budget',
        'target_audience' => 'Zielgruppe',
        'subject' => 'Betreff',
        'content' => 'Inhalt',
        'cta_text' => 'Call-to-Action Text',
        'cta_url' => 'Call-to-Action URL',
        'image' => 'Bild',
        'banner' => 'Banner',
        'target_segments' => 'Zielsegmente',
        'target_products' => 'Zielprodukte',
        'target_categories' => 'Zielkategorien',
        'created_at' => 'Erstellt am',
    ],

    // Types
    'types' => [
        'email' => 'E-Mail',
        'sms' => 'SMS',
        'push' => 'Push-Benachrichtigung',
        'banner' => 'Banner',
        'popup' => 'Popup',
        'social' => 'Soziale Medien',
    ],

    // Status
    'status' => [
        'draft' => 'Entwurf',
        'scheduled' => 'Geplant',
        'active' => 'Aktiv',
        'paused' => 'Pausiert',
        'completed' => 'Abgeschlossen',
        'cancelled' => 'Abgebrochen',
    ],

    // Segments
    'segments' => [
        'all_customers' => 'Alle Kunden',
        'new_customers' => 'Neue Kunden',
        'returning_customers' => 'Wiederkehrende Kunden',
        'high_value_customers' => 'Hochwertige Kunden',
        'inactive_customers' => 'Inaktive Kunden',
    ],

    // Filters
    'filters' => [
        'active' => 'Aktive Kampagnen',
        'scheduled' => 'Geplante Kampagnen',
        'created_from' => 'Erstellt von',
        'created_until' => 'Erstellt bis',
    ],

    // Actions
    'actions' => [
        'activate' => 'Aktivieren',
        'pause' => 'Pausieren',
        'complete' => 'Abschließen',
    ],

    // Navigation
    'navigation' => [
        'campaigns' => 'Kampagnen',
    ],

    // Models
    'models' => [
        'campaign' => 'Kampagne',
        'campaigns' => 'Kampagnen',
    ],
];
