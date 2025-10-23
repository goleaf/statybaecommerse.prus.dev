<?php

declare(strict_types=1);

use App\Support\ErrorCodes;

return [
    // @translators: Displayed when a requested page or record is missing (HTTP 404).
    ErrorCodes::NOT_FOUND => 'Page not found',

    // @translators: Shown when the system encounters an unexpected failure (HTTP 500).
    ErrorCodes::SERVER_ERROR => 'Server error',

    // @translators: Used when form submission fails validation and users must review inputs.
    ErrorCodes::VALIDATION_FAILED => 'Please check your input',

    // @translators: Indicates the user needs to log in before accessing the requested content.
    ErrorCodes::UNAUTHORIZED => 'Unauthorized',

    // @translators: Indicates the user is logged in but does not have permission for the action.
    ErrorCodes::FORBIDDEN => 'Access forbidden',
];
