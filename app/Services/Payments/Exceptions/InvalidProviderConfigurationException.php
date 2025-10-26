<?php

declare(strict_types=1);

namespace App\Services\Payments\Exceptions;

use RuntimeException;

/**
 * Thrown when the configured provider data is missing required webhook values
 * such as the shared secret or header definitions.
 */
final class InvalidProviderConfigurationException extends RuntimeException
{
}
