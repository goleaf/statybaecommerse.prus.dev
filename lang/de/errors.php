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
];
