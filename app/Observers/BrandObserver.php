<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Brand;
use App\Services\CacheInvalidationService;

/**
 * BrandObserver
 *
 * Keeps the storefront brand caches in sync whenever brand records change so
 * widget data and navigation payloads stay fresh.
 */
final class BrandObserver
{
    public function __construct(
        private readonly CacheInvalidationService $cacheInvalidationService,
    ) {}

    public function created(Brand $brand): void
    {
        // Flush featured brand listings when a new brand is added.
        $this->cacheInvalidationService->flushBrands();
    }

    public function updated(Brand $brand): void
    {
        // Refresh cached brand information after edits to keep listings current.
        $this->cacheInvalidationService->flushBrands();
    }

    public function deleted(Brand $brand): void
    {
        // Remove deleted brands from cached widgets immediately.
        $this->cacheInvalidationService->flushBrands();
    }

    public function restored(Brand $brand): void
    {
        // Reinstate restored brands within the cached payloads.
        $this->cacheInvalidationService->flushBrands();
    }

    public function forceDeleted(Brand $brand): void
    {
        // Ensure permanently removed brands disappear from all caches.
        $this->cacheInvalidationService->flushBrands();
    }
}
