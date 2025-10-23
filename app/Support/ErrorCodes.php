<?php

declare(strict_types=1);

namespace App\Support;

final class ErrorCodes
{
    /**
     * Generic HTTP & framework level errors.
     */
    public const SERVER_ERROR = 'server.error';

    public const BAD_REQUEST = 'http.bad_request';

    public const NOT_FOUND = 'http.not_found';

    public const METHOD_NOT_ALLOWED = 'http.method_not_allowed';

    public const TOO_MANY_REQUESTS = 'http.too_many_requests';

    public const UNAUTHORIZED = 'auth.unauthorized';

    public const FORBIDDEN = 'auth.forbidden';

    public const VALIDATION_FAILED = 'validation.failed';

    /**
     * Domain level error codes.
     */
    public const ORDER_NOT_FOUND = 'orders.not_found';

    public const INVENTORY_INSUFFICIENT = 'inventory.insufficient';

    /**
     * Returns the canonical translation key for a given error code.
     */
    public static function translationKey(string $errorCode): string
    {
        return 'errors.'.$errorCode;
    }

    /**
     * Returns the normalized translation key used by the TranslationService implementation.
     */
    public static function normalizedTranslationKey(string $errorCode): string
    {
        return str_replace('.', '_', self::translationKey($errorCode));
    }
}
