<?php

declare(strict_types=1);

return [
    'orders' => [
        // @translators: Displayed when a requested order number does not exist.
        'not_found' => 'Order :order could not be found.',
    ],
    'inventory' => [
        // @translators: Displayed when stock is insufficient for the requested SKU.
        'insufficient' => 'Inventory for SKU :sku is unavailable.',
    ],
    'http' => [
        // @translators: Generic message for missing resources (HTTP 404).
        'not_found' => 'The requested resource could not be found.',
        // @translators: Displayed when a user is not authenticated (HTTP 401).
        'unauthorized' => 'Authentication is required to access this resource.',
        // @translators: Displayed when a user lacks permission (HTTP 403).
        'forbidden' => 'You do not have permission to perform this action.',
        // @translators: Displayed when the HTTP method is not allowed (HTTP 405).
        'method_not_allowed' => 'The requested HTTP method is not allowed.',
        // @translators: Displayed for malformed client requests (HTTP 400).
        'bad_request' => 'The request could not be understood by the server.',
        // @translators: Displayed when the client is rate limited (HTTP 429).
        'too_many_requests' => 'Too many requests were made. Please try again later.',
    ],
    'validation' => [
        // @translators: Displayed when validation fails for submitted data.
        'failed' => 'The submitted data is invalid.',
    ],
    'internal' => [
        // @translators: Fallback message for unexpected server errors (HTTP 500).
        'server_error' => 'An unexpected error occurred.',
    ],
];
