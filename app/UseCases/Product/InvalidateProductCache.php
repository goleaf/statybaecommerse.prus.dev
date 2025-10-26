<?php

declare(strict_types=1);

namespace App\UseCases\Product;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

final class InvalidateProductCache
{
    public const SEARCH_VERSION_KEY = 'products:cache:search-version';

    public const SHOW_VERSION_KEY = 'products:cache:show-version';

    public function __invoke(): void
    {
        $this->bump(self::SEARCH_VERSION_KEY);
        $this->bump(self::SHOW_VERSION_KEY);
    }

    private function bump(string $key): void
    {
        Cache::forever($key, Str::uuid()->toString());
    }
}
