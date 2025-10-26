<?php

declare(strict_types=1);

namespace App\Services\Payments\Exceptions;

use RuntimeException;

/**
 * Triggered when a webhook payload reports monetary values that differ from
 * the authoritative order totals stored in the application.
 */
final class AmountMismatchException extends RuntimeException
{
}
