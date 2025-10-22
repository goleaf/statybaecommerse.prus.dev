<?php

declare(strict_types=1);

namespace App\Application\Product\DTOs;

use App\Domain\Product\Entities\Product;

/**
 * DTO wrapping a full product view derived from the domain model.
 */
final class ProductDetailsDto
{
    public function __construct(private readonly ProductSummaryDto $summary)
    {
        // The heavy lifting happens when we build the summary from the domain entity.
    }

    public static function fromDomain(Product $product): self
    {
        return new self(ProductSummaryDto::fromDomain($product));
    }

    public function getSummary(): ProductSummaryDto
    {
        return $this->summary;
    }
}
