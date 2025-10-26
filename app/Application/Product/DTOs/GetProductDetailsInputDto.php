<?php

declare(strict_types=1);

namespace App\Application\Product\DTOs;

use InvalidArgumentException;

/**
 * Simple DTO representing the input data for the product-details use case.
 */
final readonly class GetProductDetailsInputDto
{
    /**
     * @throws InvalidArgumentException when the slug is empty.
     */
    public function __construct(private string $slug)
    {
        // Guard against empty slugs to prevent unnecessary repository lookups.
        if ($slug === '') {
            throw new InvalidArgumentException('Product slug cannot be empty.');
        }
    }

    public function getSlug(): string
    {
        return $this->slug;
    }
}
