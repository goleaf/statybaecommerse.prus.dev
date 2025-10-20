<?php

declare(strict_types=1);

namespace App\UseCases\Category;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

final class InvalidateCategoryCache
{
    public const TREE_VERSION_KEY = 'categories:cache:tree-version';

    public function __invoke(): void
    {
        Cache::forever(self::TREE_VERSION_KEY, Str::uuid()->toString());
    }
}
