<?php

declare(strict_types=1);

return [
    'error' => [
        // @translators: Wird angezeigt, wenn die angeforderte Seite oder Ressource fehlt (HTTP 404).
        'not_found' => 'Seite nicht gefunden',

        // @translators: Hinweis auf einen unerwarteten Serverfehler (HTTP 500).
        'server' => 'Serverfehler',

        // @translators: Verwenden, wenn Benutzereingaben die Validierung nicht bestehen.
        'validation' => 'Bitte überprüfen Sie Ihre Eingaben',

        // @translators: Bedeutet, dass der Benutzer sich anmelden muss, um fortzufahren.
        'unauthorized' => 'Nicht autorisiert',

        // @translators: Bedeutet, dass dem angemeldeten Benutzer die Berechtigung fehlt.
        'forbidden' => 'Zugriff verweigert',
    ],

    'orders' => [
        // @translators: Wird angezeigt, wenn eine Bestellung mit der angegebenen Nummer nicht gefunden wurde.
        'not_found' => 'Bestellung :order wurde nicht gefunden.',
    ],

    'inventory' => [
        // @translators: Hinweis darauf, dass nicht genügend Bestand für die angeforderte SKU vorhanden ist.
        'insufficient' => 'Für SKU :sku ist nicht genügend Bestand verfügbar.',
    ],
];
