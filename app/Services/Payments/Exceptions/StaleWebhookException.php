<?php

declare(strict_types=1);

namespace App\Services\Payments\Exceptions;

use RuntimeException;

/**
 * Signifies that the webhook timestamp falls outside the configured tolerance.
 */
final class StaleWebhookException extends RuntimeException {}
