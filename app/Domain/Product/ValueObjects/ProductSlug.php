<?php

declare(strict_types=1);

namespace App\Domain\Product\ValueObjects;

use InvalidArgumentException;

/**
 * Strongly-typed slug value object.
 */
final class ProductSlug
{
    public function __construct(private readonly string $value)
    {
        if ($value === '') {
            throw new InvalidArgumentException('Product slug cannot be empty.');
        }
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
