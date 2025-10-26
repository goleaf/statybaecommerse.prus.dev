<?php

declare(strict_types=1);

namespace App\Services\Payments\Exceptions;

use RuntimeException;

/**
 * Used when a webhook references an order that cannot be located in storage.
 */
final class OrderNotFoundException extends RuntimeException {}
