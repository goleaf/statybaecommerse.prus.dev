<?php

declare(strict_types=1);

use App\Support\ErrorCode;

return [
    // @translators: Displayed when a requested page or record is missing (HTTP 404).
    ErrorCode::NotFound->value => 'Page not found',

    // @translators: Shown when the system encounters an unexpected failure (HTTP 500).
    ErrorCode::ServerError->value => 'Server error',

    // @translators: Used when form submission fails validation and users must review inputs.
    ErrorCode::ValidationFailed->value => 'Please check your input',

    // @translators: Indicates the user needs to log in before accessing the requested content.
    ErrorCode::Unauthorized->value => 'Unauthorized',

    // @translators: Indicates the user is logged in but does not have permission for the action.
    ErrorCode::Forbidden->value => 'Access forbidden',

    // @translators: Displayed when an order number could not be located in the system.
    ErrorCode::OrderNotFound->value => 'Order :order could not be found.',

    // @translators: Shown when there is not enough stock to fulfill a request for a SKU.
    ErrorCodes::INVENTORY_INSUFFICIENT => 'Inventory for SKU :sku is unavailable.',
    // @translators: Displayed when the authenticated user's profile data cannot be loaded.
    ErrorCodes::PROFILE_UNAVAILABLE => 'Profile unavailable',
    // @translators: Displayed when checkout cannot proceed because the cart is empty.
    ErrorCodes::CHECKOUT_CART_EMPTY => 'Cart is empty',

    'messages' => [
        // @translators: Generic API-friendly message for unexpected server failures.
        'server_error' => 'Something went wrong. Please try again later.',
        // @translators: API-friendly message when the authenticated profile payload cannot be produced.
        'profile_unavailable' => 'We could not resolve your profile. Please refresh and try again.',
        // @translators: API-friendly message when checkout is blocked by an empty cart.
        'checkout_empty' => 'Your cart is empty. Add items before checking out.',
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
