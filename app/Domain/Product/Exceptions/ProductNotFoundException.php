<?php

declare(strict_types=1);

namespace App\Domain\Product\Exceptions;

use RuntimeException;

use function sprintf;

/**
 * Domain-specific exception used when a product cannot be located.
 */
final class ProductNotFoundException extends RuntimeException
{
    public static function forSlug(string $slug): self
    {
        return new self(sprintf('Product with slug "%s" was not found.', $slug));
    }
}
