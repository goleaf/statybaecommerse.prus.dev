<?php

declare(strict_types=1);

use App\Support\ErrorCode;

return [
    // @translators: Displayed when a requested page or record is missing (HTTP 404).
    ErrorCode::NotFound->value => 'Puslapis nerastas',

    // @translators: Shown when the system encounters an unexpected failure (HTTP 500).
    ErrorCode::ServerError->value => 'Serverio klaida',

    // @translators: Used when form submission fails validation and users must review inputs.
    ErrorCode::ValidationFailed->value => 'Patikrinkite įvestus duomenis',

    // @translators: Indicates the user needs to log in before accessing the requested content.
    ErrorCode::Unauthorized->value => 'Neturite teisių',

    // @translators: Indicates the user is logged in but does not have permission for the action.
    ErrorCode::Forbidden->value => 'Prieiga uždrausta',

    // @translators: Displayed when an order number could not be located in the system.
    ErrorCode::OrderNotFound->value => 'Užsakymas :order nerastas.',

    // @translators: Shown when there is not enough stock to fulfill a request for a SKU.
    ErrorCode::InventoryInsufficient->value => 'SKU :sku atsargų nepakanka.',
];
