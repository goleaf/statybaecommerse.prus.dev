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

    'inventory' => [
        // @translators: Shown when there is not enough stock to fulfill a request for a SKU.
        'insufficient' => 'Inventory for SKU :sku is unavailable.',
    ],
];
