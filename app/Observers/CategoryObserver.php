<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Category;
use App\Support\Cache\CacheInvalidator;

final class CategoryObserver
{
    public function __construct(
        private readonly CacheInvalidator $cacheInvalidator,
    ) {
    }

    public function saved(Category $category): void
    {
        $this->cacheInvalidator->categoryChanged($category);
    }

    public function updated(Category $category): void
    {
        $this->cacheInvalidator->categoryChanged($category);
    }

    public function deleted(Category $category): void
    {
        $this->cacheInvalidator->categoryChanged($category);
    }

    public function restored(Category $category): void
    {
        $this->cacheInvalidator->categoryChanged($category);
    }

    public function forceDeleted(Category $category): void
    {
        $this->cacheInvalidator->categoryChanged($category);
    }
}
