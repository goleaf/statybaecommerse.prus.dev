<?php

declare(strict_types=1);

use App\Support\ErrorCode;

return [
    'titles' => [
        // @translators: Wird angezeigt, wenn die angeforderte Seite oder Ressource fehlt (HTTP 404).
        ErrorCodes::key(ErrorCodes::NOT_FOUND) => 'Seite nicht gefunden',

        // @translators: Hinweis auf einen unerwarteten Serverfehler (HTTP 500).
        ErrorCodes::key(ErrorCodes::SERVER_ERROR) => 'Serverfehler',

        // @translators: Verwenden, wenn Benutzereingaben die Validierung nicht bestehen.
        ErrorCodes::key(ErrorCodes::VALIDATION_FAILED) => 'Bitte überprüfen Sie Ihre Eingaben',

        // @translators: Bedeutet, dass der Benutzer sich anmelden muss, um fortzufahren.
        ErrorCodes::key(ErrorCodes::UNAUTHORIZED) => 'Nicht autorisiert',

        // @translators: Bedeutet, dass dem angemeldeten Benutzer die Berechtigung fehlt.
        ErrorCodes::key(ErrorCodes::FORBIDDEN) => 'Zugriff verweigert',

        // @translators: Wird angezeigt, wenn eine Bestellung mit der angegebenen Nummer nicht gefunden wurde.
        ErrorCodes::key(ErrorCodes::ORDER_NOT_FOUND) => 'Bestellung :order wurde nicht gefunden.',

        // @translators: Hinweis darauf, dass nicht genügend Bestand für die angeforderte SKU vorhanden ist.
        ErrorCodes::key(ErrorCodes::INVENTORY_INSUFFICIENT) => 'Für SKU :sku ist nicht genügend Bestand verfügbar.',
        // @translators: Wird angezeigt, wenn das Profil des angemeldeten Benutzers nicht geladen werden kann.
        ErrorCodes::key(ErrorCodes::PROFILE_UNAVAILABLE) => 'Profil nicht verfügbar',
        // @translators: Wird angezeigt, wenn der Checkout abgebrochen wird, weil der Warenkorb leer ist.
        ErrorCodes::key(ErrorCodes::CHECKOUT_CART_EMPTY) => 'Warenkorb ist leer',
    ],

    'messages' => [
        // @translators: Generische Meldung für API-Antworten bei unerwarteten Serverfehlern.
        ErrorCodes::key(ErrorCodes::SERVER_ERROR) => 'Etwas ist schiefgelaufen. Bitte versuchen Sie es später erneut.',
        // @translators: Meldung, wenn eine Validierung scheitert und keine spezifische Nachricht vorhanden ist.
        ErrorCodes::key(ErrorCodes::VALIDATION_FAILED) => 'Bitte korrigieren Sie die markierten Eingaben und versuchen Sie es erneut.',
        // @translators: Meldung, wenn eine Anmeldung erforderlich ist.
        ErrorCodes::key(ErrorCodes::UNAUTHORIZED) => 'Bitte melden Sie sich an, um fortzufahren.',
        // @translators: Meldung, wenn dem Benutzer die Berechtigung fehlt.
        ErrorCodes::key(ErrorCodes::FORBIDDEN) => 'Sie haben keine Berechtigung für diese Aktion.',
        // @translators: Meldung, wenn die angeforderte Ressource nicht gefunden wird.
        ErrorCodes::key(ErrorCodes::NOT_FOUND) => 'Die angeforderte Ressource wurde nicht gefunden.',
        // @translators: Meldung, wenn das Benutzerprofil für die Antwort nicht erzeugt werden konnte.
        ErrorCodes::key(ErrorCodes::PROFILE_UNAVAILABLE) => 'Ihr Profil konnte nicht geladen werden. Bitte aktualisieren Sie die Seite und versuchen Sie es erneut.',
        // @translators: Meldung, wenn der Checkout an einem leeren Warenkorb scheitert.
        ErrorCodes::key(ErrorCodes::CHECKOUT_CART_EMPTY) => 'Ihr Warenkorb ist leer. Bitte fügen Sie Artikel hinzu, bevor Sie zur Kasse gehen.',
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
