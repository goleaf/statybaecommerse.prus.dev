<?php

return [
    'navigation' => [
        'label' => 'Marken',
    ],

    'model' => [
        'singular' => 'Marke',
        'plural' => 'Marken',
    ],

    'tabs' => [
        'basic_information' => 'Grundinformationen',
        'seo' => 'SEO',
        'translations' => 'Übersetzungen',
        'with_products' => 'Mit Produkten',
        'without_products' => 'Ohne Produkte',
    ],

    'sections' => [
        'basic_information' => 'Grundinformationen',
        'media' => 'Medien',
        'seo' => 'SEO-Einstellungen',
        'translations' => 'Übersetzungen',
        'description' => 'Beschreibung',
        'statistics' => 'Statistiken',
        'timestamps' => 'Zeitstempel',
    ],

    'fields' => [
        'name' => 'Name',
        'slug' => 'Slug',
        'description' => 'Beschreibung',
        'website' => 'Website',
        'is_enabled' => 'Aktiviert',
        'logo' => 'Logo',
        'banner' => 'Banner',
        'seo_title' => 'SEO-Titel',
        'seo_description' => 'SEO-Beschreibung',
        'translations' => 'Übersetzungen',
        'locale' => 'Sprache',
        'products_count' => 'Produktanzahl',
        'active_products_count' => 'Aktive Produktanzahl',
        'translations_count' => 'Übersetzungsanzahl',
        'created_at' => 'Erstellt am',
        'updated_at' => 'Aktualisiert am',
    ],

    'helpers' => [
        'seo_title' => 'Empfohlene Länge: 50-60 Zeichen',
        'seo_description' => 'Empfohlene Länge: 150-160 Zeichen',
        'enabled' => 'Aktivierte Marken werden auf der Website angezeigt',
    ],

    'placeholders' => [
        'no_website' => 'Keine Website',
        'no_description' => 'Keine Beschreibung',
    ],

    'filters' => [
        'enabled' => 'Aktivierungsstatus',
        'all_brands' => 'Alle Marken',
        'enabled_only' => 'Nur aktivierte',
        'disabled_only' => 'Nur deaktivierte',
        'has_products' => 'Hat Produkte',
        'has_website' => 'Hat Website',
        'has_logo' => 'Hat Logo',
        'has_banner' => 'Hat Banner',
        'has_translations' => 'Hat Übersetzungen',
        'translation_locale' => 'Übersetzungssprache',
        'created_from' => 'Erstellt von',
        'created_until' => 'Erstellt bis',
    ],

    'actions' => [
        'create' => 'Marke erstellen',
        'create_first_brand' => 'Erste Marke erstellen',
        'add_translation' => 'Übersetzung hinzufügen',
        'enable_selected' => 'Ausgewählte aktivieren',
        'disable_selected' => 'Ausgewählte deaktivieren',
        'enable' => 'Aktivieren',
        'disable' => 'Deaktivieren',
        'manage_translations' => 'Übersetzungen verwalten',
        'bulk_actions' => 'Massenaktionen',
    ],

    'messages' => [
        'slug_copied' => 'Slug in die Zwischenablage kopiert',
    ],

    'notifications' => [
        'created' => 'Marke erfolgreich erstellt',
        'created_description' => 'Marke ":name" wurde erfolgreich erstellt.',
        'updated' => 'Marke erfolgreich aktualisiert',
        'updated_description' => 'Marke ":name" wurde erfolgreich aktualisiert.',
        'deleted' => 'Marke erfolgreich gelöscht',
    ],

    'empty_state' => [
        'heading' => 'Keine Marken gefunden',
        'description' => 'Beginnen Sie mit der Erstellung Ihrer ersten Marke.',
    ],
];

