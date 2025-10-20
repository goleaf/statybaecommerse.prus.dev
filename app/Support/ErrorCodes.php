<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Application-wide error code definitions for consistent API and UI messaging.
 */
final class ErrorCodes
{
    /**
     * Code for resources that cannot be found (HTTP 404).
     */
    public const NOT_FOUND = 'error.not_found';

    /**
     * Code for unexpected server failures (HTTP 500).
     */
    public const SERVER_ERROR = 'error.server';

    /**
     * Code for validation failures when provided data is invalid.
     */
    public const VALIDATION_FAILED = 'error.validation';

    /**
     * Code for requests made without proper authentication.
     */
    public const UNAUTHORIZED = 'error.unauthorized';

    /**
     * Code for requests that are authenticated but lack permission.
     */
    public const FORBIDDEN = 'error.forbidden';

    private function __construct()
    {
    }
}
