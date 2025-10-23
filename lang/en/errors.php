<?php

declare(strict_types=1);

use App\Support\ErrorCodes;

return [
    // @translators: Displayed when a requested page or record is missing (HTTP 404).
    ErrorCodes::normalizedTranslationKey(ErrorCodes::NOT_FOUND) => 'Page not found.',

    // @translators: Returned when request parameters are malformed or missing (HTTP 400).
    ErrorCodes::normalizedTranslationKey(ErrorCodes::BAD_REQUEST) => 'The request could not be processed.',

    // @translators: Returned when the HTTP method is not allowed for the endpoint (HTTP 405).
    ErrorCodes::normalizedTranslationKey(ErrorCodes::METHOD_NOT_ALLOWED) => 'Method not allowed.',

    // @translators: Shown when the system encounters an unexpected failure (HTTP 500).
    ErrorCodes::normalizedTranslationKey(ErrorCodes::SERVER_ERROR) => 'Something went wrong. Please try again later.',

    // @translators: Used when form submission fails validation and users must review inputs.
    ErrorCodes::normalizedTranslationKey(ErrorCodes::VALIDATION_FAILED) => 'One or more fields require your attention.',

    // @translators: Indicates the user needs to log in before accessing the requested content.
    ErrorCodes::normalizedTranslationKey(ErrorCodes::UNAUTHORIZED) => 'Please sign in to continue.',

    // @translators: Indicates the user is logged in but does not have permission for the action.
    ErrorCodes::normalizedTranslationKey(ErrorCodes::FORBIDDEN) => 'You do not have permission to perform this action.',

    // @translators: Used when the user has exceeded the allowed number of requests (HTTP 429).
    ErrorCodes::normalizedTranslationKey(ErrorCodes::TOO_MANY_REQUESTS) => 'Too many attempts. Please slow down.',

    // @translators: Domain-specific error when an order cannot be found.
    ErrorCodes::normalizedTranslationKey(ErrorCodes::ORDER_NOT_FOUND) => 'Order :order could not be found.',

    // @translators: Domain-specific error when stock levels are insufficient.
    ErrorCodes::normalizedTranslationKey(ErrorCodes::INVENTORY_INSUFFICIENT) => 'Inventory for SKU :sku is unavailable.',
];
