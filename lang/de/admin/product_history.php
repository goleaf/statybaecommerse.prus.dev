<?php

declare(strict_types=1);

return [
    'navigation_label'   => 'Produktverlauf',
    'plural_model_label' => 'Produktverläufe',
    'model_label'        => 'Produktverlauf',
    'actions'            => [
        'created'          => 'Erstellt',
        'updated'          => 'Aktualisiert',
        'deleted'          => 'Gelöscht',
        'restored'         => 'Wiederhergestellt',
        'price_changed'    => 'Preis geändert',
        'stock_updated'    => 'Bestand aktualisiert',
        'stock_changed'    => 'Bestand aktualisiert',
        'status_changed'   => 'Status geändert',
        'category_changed' => 'Kategorie geändert',
        'image_changed'    => 'Bild geändert',
        'custom'           => 'Benutzerdefinierte Aktion',
    ],
    'fields' => [
        'action'         => 'Aktion',
        'field_name'     => 'Feldname',
        'old_value'      => 'Alter Wert',
        'new_value'      => 'Neuer Wert',
        'price'          => 'Preis',
        'sale_price'     => 'Aktionspreis',
        'stock_quantity' => 'Bestand',
        'status'         => 'Status',
        'is_visible'     => 'Sichtbarkeit',
        'description'    => 'Beschreibung',
        'name'           => 'Name',
        'category'       => 'Kategorie',
        'image'          => 'Bild',
        'metadata'       => 'Metadaten',
    ],
    'summaries' => [
        'created' => ':field erstellt',
        'deleted' => ':field gelöscht',
        'updated' => ':field aktualisiert von :from auf :to',
    ],
    'impact' => [
        'high'   => 'Änderung mit hoher Auswirkung',
        'medium' => 'Änderung mit mittlerer Auswirkung',
        'low'    => 'Änderung mit geringer Auswirkung',
    ],
];
