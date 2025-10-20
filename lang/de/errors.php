<?php

declare(strict_types=1);

use App\Support\ErrorCodes;

return [
    // @translators: Wird angezeigt, wenn die angeforderte Seite oder Ressource fehlt (HTTP 404).
    ErrorCodes::NOT_FOUND => 'Seite nicht gefunden',

    // @translators: Hinweis auf einen unerwarteten Serverfehler (HTTP 500).
    ErrorCodes::SERVER_ERROR => 'Serverfehler',

    // @translators: Verwenden, wenn Benutzereingaben die Validierung nicht bestehen.
    ErrorCodes::VALIDATION_FAILED => 'Bitte überprüfen Sie Ihre Eingaben',

    // @translators: Bedeutet, dass der Benutzer sich anmelden muss, um fortzufahren.
    ErrorCodes::UNAUTHORIZED => 'Nicht autorisiert',

    // @translators: Bedeutet, dass dem angemeldeten Benutzer die Berechtigung fehlt.
    ErrorCodes::FORBIDDEN => 'Zugriff verweigert',

    // @translators: Wird angezeigt, wenn eine Bestellung mit der angegebenen Nummer nicht gefunden wurde.
    ErrorCodes::ORDER_NOT_FOUND => 'Bestellung :order wurde nicht gefunden.',

    // @translators: Hinweis darauf, dass nicht genügend Bestand für die angeforderte SKU vorhanden ist.
    ErrorCodes::INVENTORY_INSUFFICIENT => 'Für SKU :sku ist nicht genügend Bestand verfügbar.',
];
