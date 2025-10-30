<?php

declare(strict_types=1);

return [
    'plural' => 'Währungen',
    'single' => 'Währung',

    'basic_information' => 'Grundinformationen',
    'name'              => 'Name',
    'code'              => 'Währungscode',
    'code_help'         => 'Verwenden Sie den dreistelligen ISO-Währungscode, z. B. EUR oder USD.',
    'symbol'            => 'Symbol',
    'symbol_help'       => 'Zeichen, das zusammen mit Preisen angezeigt wird, z. B. €, $ oder £.',
    'iso_code'          => 'ISO-Kennung',
    'iso_code_help'     => 'Optionaler erweiterter ISO- oder Bank-Identifier für interne Zwecke.',
    'description'       => 'Beschreibung',

    'exchange_rates'     => 'Wechselkurse',
    'exchange_rate'      => 'Wechselkurs',
    'exchange_rate_help' => 'Legen Sie den Kurs im Verhältnis zur gewählten Basiswährung fest.',
    'base_currency'      => 'Basiswährung',
    'base_currency_help' => 'Die Währung, die als Referenz für Umrechnungen dient.',

    'formatting'      => 'Formatierung',
    'decimal_places'  => 'Nachkommastellen',
    'symbol_position' => 'Symbolposition',
    'positions'       => [
        'before' => 'Vor dem Betrag',
        'after'  => 'Nach dem Betrag',
    ],
    'thousands_separator'      => 'Tausendertrennzeichen',
    'thousands_separator_help' => 'Zeichen, das Tausender trennt, z. B. Komma oder Leerzeichen.',
    'decimal_separator'        => 'Dezimaltrennzeichen',
    'decimal_separator_help'   => 'Zeichen, das Dezimalstellen trennt, z. B. Punkt oder Komma.',

    'settings'         => 'Einstellungen',
    'is_active'        => 'Aktiv',
    'is_default'       => 'Standardwährung',
    'sort_order'       => 'Sortierreihenfolge',
    'auto_update_rate' => 'Kurs automatisch aktualisieren',

    'created_at' => 'Erstellt am',
    'updated_at' => 'Aktualisiert am',

    'active_only'        => 'Nur aktive',
    'inactive_only'      => 'Nur inaktive',
    'default_only'       => 'Nur Standard',
    'non_default_only'   => 'Nur nicht Standard',
    'auto_update_only'   => 'Nur automatische Aktualisierung',
    'manual_update_only' => 'Nur manuelle Aktualisierung',

    'deactivate'                  => 'Deaktivieren',
    'activate'                    => 'Aktivieren',
    'activated_successfully'      => 'Währung wurde erfolgreich aktiviert.',
    'deactivated_successfully'    => 'Währung wurde erfolgreich deaktiviert.',
    'set_default'                 => 'Als Standard festlegen',
    'set_as_default_successfully' => 'Währung wurde erfolgreich als Standard festgelegt.',
    'update_rate'                 => 'Kurs aktualisieren',
    'rate_updated_successfully'   => 'Währungskurs wurde erfolgreich aktualisiert.',
    'rate_update_failed'          => 'Der Wechselkurs konnte nicht aktualisiert werden.',

    'activate_selected'          => 'Auswahl aktivieren',
    'deactivate_selected'        => 'Auswahl deaktivieren',
    'bulk_activated_success'     => 'Ausgewählte Währungen wurden erfolgreich aktiviert.',
    'bulk_deactivated_success'   => 'Ausgewählte Währungen wurden erfolgreich deaktiviert.',
    'update_rates'               => 'Kurse aktualisieren',
    'rates_updated_successfully' => 'Wechselkurse wurden erfolgreich aktualisiert.',
    'rates_update_failed'        => 'Es wurden keine Wechselkurse aktualisiert. Bitte Konfiguration prüfen.',
];
