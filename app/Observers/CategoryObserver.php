<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Category;
use App\Services\CacheInvalidationService;
use App\Support\Cache\CacheInvalidator;

final class CategoryObserver
{
    public function __construct(
        private readonly CacheInvalidator $cacheInvalidator,
        private readonly CacheInvalidationService $cacheInvalidationService,
    ) {}

    public function saved(Category $category): void
    {
        $this->cacheInvalidator->categoryChanged($category);
        $this->cacheInvalidationService->flushCategories($category);
    }

    public function updated(Category $category): void
    {
        $this->cacheInvalidator->categoryChanged($category);
        $this->cacheInvalidationService->flushCategories($category);
    }

    public function deleted(Category $category): void
    {
        $this->cacheInvalidator->categoryChanged($category);
        $this->cacheInvalidationService->flushCategories($category);
    }

    public function restored(Category $category): void
    {
        $this->cacheInvalidator->categoryChanged($category);
        $this->cacheInvalidationService->flushCategories($category);
    }

    public function forceDeleted(Category $category): void
    {
        $this->cacheInvalidator->categoryChanged($category);
        $this->cacheInvalidationService->flushCategories($category);
    }
}
