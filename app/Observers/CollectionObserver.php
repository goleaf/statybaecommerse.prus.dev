<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Collection;
use App\Services\CacheInvalidationService;

/**
 * CollectionObserver
 *
 * Ensures curated collection widgets rebuild promptly whenever collections are
 * created, updated, or removed from the catalogue.
 */
final class CollectionObserver
{
    public function __construct(
        private readonly CacheInvalidationService $cacheInvalidationService,
    ) {}

    public function created(Collection $collection): void
    {
        // Rebuild storefront collection grids after new records are seeded.
        $this->cacheInvalidationService->flushCollections();
    }

    public function updated(Collection $collection): void
    {
        // Refresh cached showcases when collection metadata changes.
        $this->cacheInvalidationService->flushCollections();
    }

    public function deleted(Collection $collection): void
    {
        // Remove stale collection entries from cached widgets immediately.
        $this->cacheInvalidationService->flushCollections();
    }

    public function restored(Collection $collection): void
    {
        // Rehydrate caches to include collections brought back online.
        $this->cacheInvalidationService->flushCollections();
    }

    public function forceDeleted(Collection $collection): void
    {
        // Guarantee permanently deleted collections disappear from caches.
        $this->cacheInvalidationService->flushCollections();
    }
}
