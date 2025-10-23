<?php

declare(strict_types=1);

namespace App\Domain\Product\Repositories;

use App\Domain\Product\Collections\ProductCollection;
use App\Domain\Product\Entities\Product;
use App\Domain\Product\ValueObjects\ProductCatalogQuery;
use App\Domain\Product\ValueObjects\ProductSearchCriteria;
use App\Domain\Product\ValueObjects\ProductSlug;

/**
 * Contract describing the read operations supported for products.
 */
interface ProductRepositoryInterface
{
    public function search(ProductSearchCriteria $criteria): ProductCollection;

    public function getCatalogProducts(ProductCatalogQuery $query): ProductCollection;

    public function findBySlug(ProductSlug $slug): ?Product;
}
