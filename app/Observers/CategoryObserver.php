<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Category;
use App\UseCases\Cache\InvalidateCategoryCache;

final class CategoryObserver
{
    public function __construct(
        private readonly InvalidateCategoryCache $invalidateCategoryCache,
    ) {}

    public function created(Category $category): void
    {
        ($this->invalidateCategoryCache)($category->id);
    }

    public function updated(Category $category): void
    {
        ($this->invalidateCategoryCache)($category->id);
    }

    public function deleted(Category $category): void
    {
        ($this->invalidateCategoryCache)($category->id);
    }

    public function restored(Category $category): void
    {
        ($this->invalidateCategoryCache)($category->id);
    }

    public function forceDeleted(Category $category): void
    {
        ($this->invalidateCategoryCache)($category->id);
    }
}
