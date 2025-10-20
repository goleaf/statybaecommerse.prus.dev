<?php

declare(strict_types=1);

namespace App\Application\Product\DTOs;

final class GetProductDetailsInputDto
{
    public function __construct(private readonly string $slug)
    {
        if ($slug === '') {
            throw new \InvalidArgumentException('Product slug cannot be empty.');
        }
    }

    public function getSlug(): string
    {
        return $this->slug;
    }
}
