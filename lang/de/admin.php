<?php

declare(strict_types=1);

return [
    'brands' => require __DIR__ . '/admin/brands.php',
    'countries' => require __DIR__ . '/admin/countries.php',
    'menu_items' => require __DIR__ . '/admin/menu_items.php',
    'product_history' => require __DIR__ . '/admin/product_history.php',
    'common' => [
        'id' => 'ID',
        'created_at' => 'Erstellt am',
        'updated_at' => 'Aktualisiert am',
        'view' => 'Anzeigen',
        'edit' => 'Bearbeiten',
        'delete_selected' => 'Auswahl löschen',
        'none' => 'Keine',
        'yes' => 'Ja',
        'no' => 'Nein',
    ],
];
