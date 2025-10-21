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
    // @translators: Wird angezeigt, wenn das Profil des angemeldeten Benutzers nicht geladen werden kann.
    ErrorCodes::PROFILE_UNAVAILABLE => 'Profil nicht verfügbar',
    // @translators: Wird angezeigt, wenn der Checkout abgebrochen wird, weil der Warenkorb leer ist.
    ErrorCodes::CHECKOUT_CART_EMPTY => 'Warenkorb ist leer',

    'messages' => [
        // @translators: Generische Meldung für API-Antworten bei unerwarteten Serverfehlern.
        'server_error' => 'Etwas ist schiefgelaufen. Bitte versuche es später erneut.',
        // @translators: Meldung, wenn das Benutzerprofil für die Antwort nicht erzeugt werden konnte.
        'profile_unavailable' => 'Ihr Profil konnte nicht geladen werden. Bitte aktualisieren Sie die Seite und versuchen Sie es erneut.',
        // @translators: Meldung, wenn der Checkout an einem leeren Warenkorb scheitert.
        'checkout_empty' => 'Ihr Warenkorb ist leer. Fügen Sie Artikel hinzu, bevor Sie zur Kasse gehen.',
    ],

    'pages' => [
        'unexpected' => [
            // @translators: Überschrift auf der globalen Fehlerseite bei unerwarteten Fehlern.
            'title' => 'Ein unerwarteter Fehler ist aufgetreten',
            // @translators: Beschreibung auf der globalen Fehlerseite bei unerwarteten Fehlern.
            'description' => 'Unser Team wurde informiert und untersucht das Problem. Wenn es erneut auftritt, teilen Sie dem Support die Trace-ID mit.',
            // @translators: Text des primären Aktionsbuttons auf der Fehlerseite.
            'primary' => 'Zur Startseite',
            // @translators: Text des sekundären Aktionsbuttons auf der Fehlerseite.
            'secondary' => 'Support kontaktieren',
        ],
    ],
];
