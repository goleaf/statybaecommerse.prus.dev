<?php

declare(strict_types=1);

namespace App\Domain\Product\Specifications;

use App\Domain\Product\Entities\Product;

final class DisplayableProductSpecification
{
    public function isSatisfiedBy(Product $product): bool
    {
        if (! $product->isVisible()) {
            return false;
        }

        if ($product->getPrice() <= 0.0) {
            return false;
        }

        return $product->getName() !== '' && $product->getSlug() !== '';
    }
}
