<?php

declare(strict_types=1);

namespace App\Services\Payments\Exceptions;

use RuntimeException;

/**
 * Raised when a webhook attempts to progress an order to a state that violates
 * the defined payment lifecycle transitions.
 */
final class IllegalStateTransitionException extends RuntimeException
{
}
