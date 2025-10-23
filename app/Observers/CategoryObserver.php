<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Category;
use App\UseCases\Category\InvalidateCategoryCache;

final class CategoryObserver
{
    public function saved(Category $category): void
    {
        app(InvalidateCategoryCache::class)();
    }

    public function deleted(Category $category): void
    {
        app(InvalidateCategoryCache::class)();
    }
}
