<?php

declare(strict_types=1);

use App\Support\ErrorCodes;

return [
    // @translators: Wird angezeigt, wenn die angeforderte Seite oder Ressource fehlt (HTTP 404).
    ErrorCodes::normalizedTranslationKey(ErrorCodes::NOT_FOUND) => 'Seite nicht gefunden.',

    // @translators: Wird angezeigt, wenn die Anfrage ungültig ist oder Pflichtdaten fehlen (HTTP 400).
    ErrorCodes::normalizedTranslationKey(ErrorCodes::BAD_REQUEST) => 'Die Anfrage konnte nicht verarbeitet werden.',

    // @translators: Wird angezeigt, wenn eine HTTP-Methode nicht erlaubt ist (HTTP 405).
    ErrorCodes::normalizedTranslationKey(ErrorCodes::METHOD_NOT_ALLOWED) => 'Methode nicht erlaubt.',

    // @translators: Hinweis auf einen unerwarteten Serverfehler (HTTP 500).
    ErrorCodes::normalizedTranslationKey(ErrorCodes::SERVER_ERROR) => 'Es ist ein unerwarteter Fehler aufgetreten. Bitte versuchen Sie es später erneut.',

    // @translators: Verwenden, wenn Benutzereingaben die Validierung nicht bestehen.
    ErrorCodes::normalizedTranslationKey(ErrorCodes::VALIDATION_FAILED) => 'Bitte überprüfen Sie Ihre Eingaben.',

    // @translators: Bedeutet, dass der Benutzer sich anmelden muss, um fortzufahren.
    ErrorCodes::normalizedTranslationKey(ErrorCodes::UNAUTHORIZED) => 'Bitte melden Sie sich an, um fortzufahren.',

    // @translators: Bedeutet, dass dem angemeldeten Benutzer die Berechtigung fehlt.
    ErrorCodes::normalizedTranslationKey(ErrorCodes::FORBIDDEN) => 'Sie haben keine Berechtigung für diese Aktion.',

    // @translators: Wird angezeigt, wenn zu viele Anfragen gesendet wurden (HTTP 429).
    ErrorCodes::normalizedTranslationKey(ErrorCodes::TOO_MANY_REQUESTS) => 'Zu viele Anfragen. Bitte versuchen Sie es später erneut.',

    // @translators: Domänenspezifischer Fehler, wenn eine Bestellung nicht gefunden wird.
    ErrorCodes::normalizedTranslationKey(ErrorCodes::ORDER_NOT_FOUND) => 'Bestellung :order wurde nicht gefunden.',

    // @translators: Domänenspezifischer Fehler, wenn der Bestand nicht ausreicht.
    ErrorCodes::normalizedTranslationKey(ErrorCodes::INVENTORY_INSUFFICIENT) => 'Bestand für SKU :sku ist nicht verfügbar.',
];
