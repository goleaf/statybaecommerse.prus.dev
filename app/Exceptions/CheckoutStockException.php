<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * CheckoutStockException
 *
 * Exception thrown when available inventory changes during checkout.
 */
final class CheckoutStockException extends RuntimeException {}
