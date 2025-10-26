<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Product;
use App\Services\ProductLifecycleService;

/**
 * ProductObserver
 *
 * Model observer for ProductObserver Eloquent model events with automatic side effect handling and data consistency.
 */
final class ProductObserver
{
    public function __construct(
        private readonly ProductLifecycleService $productLifecycleService,
    ) {}

    /**
     * React to product creation by delegating to the lifecycle service for cache flushing and media handling.
     */
    public function created(Product $product): void
    {
        $this->productLifecycleService->handleCreated($product);
    }

    public function updated(Product $product): void
    {
        // Ensure all mutation events go through the same lifecycle pipeline for consistency.
        $this->productLifecycleService->handleMutated($product);
    }

    public function deleted(Product $product): void
    {
        // Mirror the update behaviour so removals also refresh cached aggregates.
        $this->productLifecycleService->handleMutated($product);
    }

    public function restored(Product $product): void
    {
        // Restores impact storefront listings, so reuse the shared mutation handler.
        $this->productLifecycleService->handleMutated($product);
    }

    public function forceDeleted(Product $product): void
    {
        // Force deletes should also invalidate caches via the lifecycle service.
        $this->productLifecycleService->handleMutated($product);
    }
}
