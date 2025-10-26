<?php

declare(strict_types=1);

namespace App\Support\Cache;

use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\RateLimiter as BaseRateLimiter;
use Illuminate\Cache\Repository;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use ReflectionClass;

final class RateLimiter extends BaseRateLimiter
{
    public static function fromBase(BaseRateLimiter $limiter): self
    {
        $reflection = new ReflectionClass(BaseRateLimiter::class);

        $cacheProperty = $reflection->getProperty('cache');
        $cacheProperty->setAccessible(true);
        /** @var CacheRepository $cache */
        $cache = $cacheProperty->getValue($limiter);

        $instance = new self($cache);

        $limitersProperty = $reflection->getProperty('limiters');
        $limitersProperty->setAccessible(true);
        /** @var array $limiters */
        $limiters = $limitersProperty->getValue($limiter);

        $instance->limiters = $limiters;

        return $instance;
    }

    public function fake(?CacheRepository $repository = null): void
    {
        $this->cache = $repository ?? new Repository(new ArrayStore);
    }
}
