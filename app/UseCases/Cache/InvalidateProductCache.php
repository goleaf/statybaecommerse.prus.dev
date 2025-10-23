<?php

declare(strict_types=1);

namespace App\UseCases\Cache;

use App\Models\Product;
use App\Services\CacheInvalidationService;

final class InvalidateProductCache
{
    public function __construct(private readonly CacheInvalidationService $cacheInvalidationService) {}

    /**
     * Flush product related caches for both aggregate metrics and storefront widgets.
     */
    public function __invoke(?Product $product = null): void
    {
        $productId = $product?->getKey();

        $this->cacheInvalidationService->flushProducts(is_numeric($productId) ? (int) $productId : null);
    }
}
