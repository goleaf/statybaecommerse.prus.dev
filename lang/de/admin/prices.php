<?php

return [
    'navigation' => [
        'label' => 'Preise',
    ],

    'model' => [
        'singular' => 'Preis',
        'plural' => 'Preise',
    ],

    'sections' => [
        'basic_information' => 'Grundinformationen',
        'pricing' => 'Preisgestaltung',
        'validity' => 'Gültigkeitszeitraum',
        'metadata' => 'Metadaten',
    ],

    'fields' => [
        'priceable' => 'Verknüpftes Objekt',
        'priceable_type' => 'Objekttyp',
        'priceable_name' => 'Name',
        'currency' => 'Währung',
        'type' => 'Preistyp',
        'amount' => 'Betrag',
        'compare_amount' => 'Vergleichsbetrag',
        'cost_amount' => 'Kosten',
        'is_enabled' => 'Aktiviert',
        'starts_at' => 'Beginn',
        'ends_at' => 'Ende',
        'metadata' => 'Metadaten',
        'created_at' => 'Erstellt am',
        'updated_at' => 'Aktualisiert am',
    ],

    'filters' => [
        'priceable_type' => 'Objekttyp',
        'currency' => 'Währung',
        'type' => 'Preistyp',
        'is_enabled' => 'Aktivierungsstatus',
        'active' => 'Aktive Preise',
    ],

    'priceable_types' => [
        'product' => 'Produkt',
        'variant' => 'Variante',
    ],

    'types' => [
        'retail' => 'Einzelhandel',
        'wholesale' => 'Großhandel',
        'special' => 'Spezial',
        'sale' => 'Sale',
    ],
];
