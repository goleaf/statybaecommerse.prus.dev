<?php

declare(strict_types=1);

namespace App\Domain\Product\Specifications;

use App\Domain\Product\Entities\Product;

/**
 * Specification ensuring a product is suitable for public display.
 */
final class DisplayableProductSpecification
{
    public function isSatisfiedBy(Product $product): bool
    {
        if (! $product->isAvailableForPurchase()) {
            return false;
        }

        // By reaching this branch the product has satisfied all visibility and pricing rules.
        return true;
    }
}
