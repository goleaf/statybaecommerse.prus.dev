<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Category;
use App\Support\Cache\CacheInvalidator;

final class CategoryObserver
{
    public function saved(Category $category): void
    {
        $this->flushCategoryCaches($category);
    }

    public function deleted(Category $category): void
    {
        $this->flushCategoryCaches($category);
    }

    public function restored(Category $category): void
    {
        $this->flushCategoryCaches($category);
    }

    public function forceDeleted(Category $category): void
    {
        $this->flushCategoryCaches($category);
    }

    private function flushCategoryCaches(Category $category): void
    {
        app(CacheInvalidator::class)->categoryChanged($category);
    }
}
