<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Category;
use App\Support\Cache\CacheInvalidator;

final class CategoryObserver
{
    public function saved(Category $category): void
    {
        // Trigger the centralized cache invalidation pipeline for this category instance.
        ($this->invalidateCategoryCache)($category);
    }

    public function updated(Category $category): void
    {
        ($this->invalidateCategoryCache)($category);
    }

    public function deleted(Category $category): void
    {
        ($this->invalidateCategoryCache)($category);
    }

    public function restored(Category $category): void
    {
        ($this->invalidateCategoryCache)($category);
    }

    public function forceDeleted(Category $category): void
    {
        ($this->invalidateCategoryCache)($category);
    }
}
