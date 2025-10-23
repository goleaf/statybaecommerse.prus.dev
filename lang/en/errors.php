<?php

declare(strict_types=1);

return [
    'error' => [
        // @translators: Displayed when a requested page or record is missing (HTTP 404).
        'not_found' => 'Page not found',

        // @translators: Shown when the system encounters an unexpected failure (HTTP 500).
        'server' => 'Server error',

        // @translators: Used when form submission fails validation and users must review inputs.
        'validation' => 'Please check your input',

        // @translators: Indicates the user needs to log in before accessing the requested content.
        'unauthorized' => 'Unauthorized',

        // @translators: Indicates the user is logged in but does not have permission for the action.
        'forbidden' => 'Access forbidden',
    ],

    'orders' => [
        // @translators: Displayed when an order number could not be located in the system.
        'not_found' => 'Order :order could not be found.',
    ],

    // @translators: Shown when there is not enough stock to fulfill a request for a SKU.
    ErrorCodes::INVENTORY_INSUFFICIENT => 'Inventory for SKU :sku is unavailable.',

    'messages' => [
        // @translators: Generic API-friendly message for unexpected server failures.
        'server_error' => 'Something went wrong. Please try again later.',
    ],

    'pages' => [
        'unexpected' => [
            // @translators: Title shown on the global error page when an unexpected failure occurs.
            'title' => 'We ran into a problem',
            // @translators: Description shown on the global error page when an unexpected failure occurs.
            'description' => 'Our team has been notified and is already looking into the issue. If it keeps happening, share the trace ID with support.',
            // @translators: Label for the primary action button on the unexpected error page.
            'primary' => 'Return Home',
            // @translators: Label for the secondary action button on the unexpected error page.
            'secondary' => 'Contact Support',
        ],
    ],
];
