<?php

return [
    // Navigation
    'navigation' => [
        'label' => 'Länder',
    ],

    // Model labels
    'model' => [
        'singular' => 'Land',
        'plural' => 'Länder',
    ],

    // Sections
    'sections' => [
        'basic_information' => 'Grundinformationen',
        'geographic_information' => 'Geografische Informationen',
        'currency_economic' => 'Währungs- und Wirtschaftsinformationen',
        'contact_information' => 'Kontaktinformationen',
        'additional_information' => 'Zusätzliche Informationen',
        'status_settings' => 'Status und Einstellungen',
    ],

    // Fields
    'fields' => [
        'name' => 'Name',
        'name_official' => 'Offizieller Name',
        'cca2' => 'CCA2-Code',
        'cca3' => 'CCA3-Code',
        'ccn3' => 'CCN3-Code',
        'region' => 'Region',
        'subregion' => 'Subregion',
        'latitude' => 'Breitengrad',
        'longitude' => 'Längengrad',
        'currency_code' => 'Währungscode',
        'currency_symbol' => 'Währungssymbol',
        'vat_rate' => 'MwSt-Satz',
        'timezone' => 'Zeitzone',
        'phone_code' => 'Telefoncode',
        'phone_calling_code' => 'Anrufcode',
        'flag' => 'Flagge',
        'svg_flag' => 'SVG-Flagge',
        'currencies' => 'Währungen',
        'languages' => 'Sprachen',
        'timezones' => 'Zeitzonen',
        'metadata' => 'Metadaten',
        'description' => 'Beschreibung',
        'is_active' => 'Aktiv',
        'is_enabled' => 'Aktiviert',
        'is_eu_member' => 'EU-Mitglied',
        'requires_vat' => 'MwSt erforderlich',
        'sort_order' => 'Sortierreihenfolge',
        'created_at' => 'Erstellt am',
        'updated_at' => 'Aktualisiert am',
    ],

    // Helpers
    'helpers' => [
        'active' => 'Ob dieses Land aktiv und sichtbar ist',
        'enabled' => 'Ob dieses Land für die Nutzung aktiviert ist',
        'eu_member' => 'Ob dieses Land Mitglied der Europäischen Union ist',
        'requires_vat' => 'Ob dieses Land MwSt für Transaktionen erfordert',
        'vat_rate' => 'MwSt-Satz als Prozentsatz (0-100)',
        'sort_order' => 'Reihenfolge für die Anzeige von Ländern (niedrigere Zahlen erscheinen zuerst)',
    ],

    // Placeholders
    'placeholders' => [
        'no_flag' => 'Keine Flagge',
        'no_description' => 'Keine Beschreibung',
    ],

    // Actions
    'actions' => [
        'activate' => 'Aktivieren',
        'deactivate' => 'Deaktivieren',
        'activate_selected' => 'Ausgewählte aktivieren',
        'deactivate_selected' => 'Ausgewählte deaktivieren',
        'activated_successfully' => 'Länder erfolgreich aktiviert',
        'deactivated_successfully' => 'Länder erfolgreich deaktiviert',
    ],

    // Filters
    'filters' => [
        'active' => 'Aktivierungsstatus',
        'enabled' => 'Aktivierungsstatus',
        'eu_member' => 'EU-Mitgliedschaftsstatus',
        'requires_vat' => 'MwSt-Erforderlichkeitsstatus',
        'region' => 'Region',
        'currency_code' => 'Währungscode',
    ],

    // Statistics
    'stats' => [
        'total_countries' => 'Gesamtländer',
        'active_countries' => 'Aktive Länder',
        'eu_members' => 'EU-Mitglieder',
        'countries_with_vat' => 'Länder mit MwSt',
    ],
];
