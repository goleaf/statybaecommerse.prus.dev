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
    ErrorCode::InventoryInsufficient->value => 'Inventory for SKU :sku is unavailable.',
];
