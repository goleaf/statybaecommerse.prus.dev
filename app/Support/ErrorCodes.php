<?php

declare(strict_types=1);

namespace App\Support;

final class ErrorCodes
{
    public const ORDER_NOT_FOUND = 'orders.not_found';

    public const INVENTORY_INSUFFICIENT = 'inventory.insufficient';

    public const VALIDATION_FAILED = 'validation.failed';

    public const HTTP_NOT_FOUND = 'http.not_found';

    public const HTTP_METHOD_NOT_ALLOWED = 'http.method_not_allowed';

    public const HTTP_FORBIDDEN = 'http.forbidden';

    public const HTTP_UNAUTHORIZED = 'http.unauthorized';

    public const HTTP_TOO_MANY_REQUESTS = 'http.too_many_requests';

    public const HTTP_BAD_REQUEST = 'http.bad_request';

    public const INTERNAL_SERVER_ERROR = 'internal.server_error';

    public static function messageKey(string $code): string
    {
        return 'errors.'.$code;
    }
}
