<?php

declare(strict_types=1);

namespace App\UseCases\Cache;

use App\Models\Category;
use App\Services\CacheInvalidationService;

final class InvalidateCategoryCache
{
    public function __construct(private readonly CacheInvalidationService $cacheInvalidationService) {}

    public function __invoke(?Category $category = null): void
    {
        $categoryId = $category?->getKey();

        $this->cacheInvalidationService->flushCategories(is_numeric($categoryId) ? (int) $categoryId : null);
    }
}
