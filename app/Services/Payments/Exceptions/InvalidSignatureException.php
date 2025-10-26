<?php

declare(strict_types=1);

namespace App\Services\Payments\Exceptions;

use RuntimeException;

/**
 * Raised whenever the computed HMAC does not match the provided signature.
 */
final class InvalidSignatureException extends RuntimeException {}
