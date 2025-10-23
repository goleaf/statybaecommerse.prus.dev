<?php

declare(strict_types=1);

namespace App\Domain\Product\Exceptions;

use RuntimeException;

final class ProductNotFoundException extends RuntimeException
{
    public static function forSlug(string $slug): self
    {
        return new self(sprintf('Product with slug "%s" was not found.', $slug));
    }
}
