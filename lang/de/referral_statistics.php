<?php

declare(strict_types=1);

return [
    'title'    => 'Empfehlungsstatistiken',
    'plural'   => 'Empfehlungsstatistiken',
    'single'   => 'Empfehlungsstatistik',
    'sections' => [
        'basic_info'                  => 'Grundinformationen',
        'basic_info_description'      => 'Nachverfolgen, wem die Statistik gehört und wann sie erfasst wurde.',
        'referral_stats'              => 'Empfehlungsleistung',
        'referral_stats_description'  => 'Anzahlen aller Empfehlungsergebnisse für den ausgewählten Zeitraum.',
        'financial_stats'             => 'Finanzielle Auswirkungen',
        'financial_stats_description' => 'Summen der verdienten Prämien und gewährten Rabatte.',
        'advanced'                    => 'Erweiterte Details',
        'advanced_description'        => 'Zusätzliche Metadaten zu diesem Statistikdatensatz speichern.',
        'timestamps'                  => 'Zeitstempel',
    ],
    'fields' => [
        'user_id'               => 'Benutzer',
        'user_name'             => 'Benutzer',
        'date'                  => 'Datum',
        'total_referrals'       => 'Gesamtanzahl Empfehlungen',
        'completed_referrals'   => 'Abgeschlossene Empfehlungen',
        'pending_referrals'     => 'Ausstehende Empfehlungen',
        'total_rewards_earned'  => 'Verdiente Prämien',
        'total_discounts_given' => 'Gewährte Rabatte',
        'metadata'              => 'Metadaten',
        'metadata_key'          => 'Schlüssel',
        'metadata_value'        => 'Wert',
        'created_at'            => 'Erstellt am',
        'updated_at'            => 'Aktualisiert am',
    ],
    'filters' => [
        'user'          => 'Benutzer',
        'date_range'    => 'Datumsbereich',
        'from_date'     => 'Von Datum',
        'until_date'    => 'Bis Datum',
        'has_referrals' => 'Mit Empfehlungen',
        'has_rewards'   => 'Mit Prämien',
    ],
    'actions' => [
        'add_metadata'      => 'Metadatum hinzufügen',
        'refresh_stats'     => 'Statistik aktualisieren',
        'refresh_all_stats' => 'Alle Statistiken aktualisieren',
    ],
    'notifications' => [
        'stats_refreshed'     => 'Empfehlungsstatistik wurde erfolgreich aktualisiert.',
        'all_stats_refreshed' => 'Alle Empfehlungsstatistiken wurden erfolgreich aktualisiert.',
    ],
    'placeholders' => [
        'no_metadata' => 'Noch keine Metadaten vorhanden.',
    ],
];
