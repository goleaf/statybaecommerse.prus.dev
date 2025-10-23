<?php

declare(strict_types=1);

return [
    'orders' => [
        // @translators: Wird angezeigt, wenn die angegebene Bestellnummer nicht existiert.
        'not_found' => 'Bestellung :order wurde nicht gefunden.',
    ],
    'inventory' => [
        // @translators: Wird angezeigt, wenn nicht genügend Bestand für die angeforderte SKU vorhanden ist.
        'insufficient' => 'Bestand für SKU :sku ist nicht verfügbar.',
    ],
    'http' => [
        // @translators: Allgemeine Meldung für fehlende Ressourcen (HTTP 404).
        'not_found' => 'Die angeforderte Ressource wurde nicht gefunden.',
        // @translators: Wird angezeigt, wenn der Benutzer nicht angemeldet ist (HTTP 401).
        'unauthorized' => 'Für den Zugriff auf diese Ressource ist eine Anmeldung erforderlich.',
        // @translators: Wird angezeigt, wenn dem Benutzer die Berechtigung fehlt (HTTP 403).
        'forbidden' => 'Sie verfügen nicht über die erforderlichen Berechtigungen.',
        // @translators: Wird angezeigt, wenn die verwendete HTTP-Methode nicht erlaubt ist (HTTP 405).
        'method_not_allowed' => 'Die verwendete HTTP-Methode ist nicht zulässig.',
        // @translators: Wird angezeigt, wenn die Client-Anfrage fehlerhaft ist (HTTP 400).
        'bad_request' => 'Die Anfrage konnte vom Server nicht verarbeitet werden.',
        // @translators: Wird angezeigt, wenn der Client zu viele Anfragen sendet (HTTP 429).
        'too_many_requests' => 'Zu viele Anfragen. Bitte versuchen Sie es später erneut.',
    ],
    'validation' => [
        // @translators: Wird angezeigt, wenn die übermittelten Daten ungültig sind.
        'failed' => 'Die übermittelten Daten sind ungültig.',
    ],
    'internal' => [
        // @translators: Allgemeine Meldung für unerwartete Serverfehler (HTTP 500).
        'server_error' => 'Es ist ein unerwarteter Fehler aufgetreten.',
    ],
];
