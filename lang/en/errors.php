<?php

declare(strict_types=1);

return [
    'titles' => [
        // @translators: Displayed when a requested page or record is missing (HTTP 404).
        ErrorCodes::key(ErrorCodes::NOT_FOUND) => 'Page not found',

        // @translators: Shown when the system encounters an unexpected failure (HTTP 500).
        ErrorCodes::key(ErrorCodes::SERVER_ERROR) => 'Server error',

        // @translators: Used when form submission fails validation and users must review inputs.
        ErrorCodes::key(ErrorCodes::VALIDATION_FAILED) => 'Please check your input',

        // @translators: Indicates the user needs to log in before accessing the requested content.
        ErrorCodes::key(ErrorCodes::UNAUTHORIZED) => 'Unauthorized',

        // @translators: Indicates the user is logged in but does not have permission for the action.
        ErrorCodes::key(ErrorCodes::FORBIDDEN) => 'Access forbidden',

        // @translators: Displayed when an order number could not be located in the system.
        ErrorCodes::key(ErrorCodes::ORDER_NOT_FOUND) => 'Order :order could not be found.',

        // @translators: Shown when there is not enough stock to fulfill a request for a SKU.
        ErrorCodes::key(ErrorCodes::INVENTORY_INSUFFICIENT) => 'Inventory for SKU :sku is unavailable.',
        // @translators: Displayed when the authenticated user's profile data cannot be loaded.
        ErrorCodes::key(ErrorCodes::PROFILE_UNAVAILABLE) => 'Profile unavailable',
        // @translators: Displayed when checkout cannot proceed because the cart is empty.
        ErrorCodes::key(ErrorCodes::CHECKOUT_CART_EMPTY) => 'Cart is empty',
    ],

    'messages' => [
        // @translators: Generic API-friendly message for unexpected server failures.
        ErrorCodes::key(ErrorCodes::SERVER_ERROR) => 'Something went wrong. Please try again later.',
        // @translators: API-friendly message when form validation fails without a specific message.
        ErrorCodes::key(ErrorCodes::VALIDATION_FAILED) => 'Some fields need your attention before we can continue.',
        // @translators: API-friendly message when authentication is required.
        ErrorCodes::key(ErrorCodes::UNAUTHORIZED) => 'You need to sign in before continuing.',
        // @translators: API-friendly message when the user lacks permission.
        ErrorCodes::key(ErrorCodes::FORBIDDEN) => 'You do not have permission to perform this action.',
        // @translators: API-friendly message when a requested resource cannot be found.
        ErrorCodes::key(ErrorCodes::NOT_FOUND) => 'We could not locate the requested resource.',
        // @translators: API-friendly message when the authenticated profile payload cannot be produced.
        ErrorCodes::key(ErrorCodes::PROFILE_UNAVAILABLE) => 'We could not resolve your profile. Please refresh and try again.',
        // @translators: API-friendly message when checkout is blocked by an empty cart.
        ErrorCodes::key(ErrorCodes::CHECKOUT_CART_EMPTY) => 'Your cart is empty. Add items before checking out.',
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
