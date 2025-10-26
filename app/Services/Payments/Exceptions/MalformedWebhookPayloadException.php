<?php

declare(strict_types=1);

namespace App\Services\Payments\Exceptions;

use RuntimeException;

/**
 * Indicates that required keys are missing or the payload could not be parsed
 * as valid JSON.
 */
final class MalformedWebhookPayloadException extends RuntimeException
{
}
