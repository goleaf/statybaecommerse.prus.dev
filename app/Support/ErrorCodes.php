<?php

declare(strict_types=1);

namespace App\Support;

final class ErrorCodes
{
    public const UNKNOWN_ERROR = 'unknown_error';
    public const VALIDATION_FAILED = 'validation_failed';
    public const AUTHENTICATION_FAILED = 'authentication_failed';
    public const AUTHORIZATION_FAILED = 'authorization_failed';
    public const ROUTE_NOT_FOUND = 'route_not_found';
    public const METHOD_NOT_ALLOWED = 'method_not_allowed';
    public const MODEL_NOT_FOUND = 'model_not_found';
    public const TOO_MANY_REQUESTS = 'too_many_requests';
    public const SERVICE_UNAVAILABLE = 'service_unavailable';
    public const RUNTIME_ERROR = 'runtime_error';
    public const CODE_STYLE_VIOLATION = 'code_style_violation';
}
