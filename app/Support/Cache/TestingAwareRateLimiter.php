<?php

declare(strict_types=1);

namespace App\Support\Cache;

use Illuminate\Cache\RateLimiter;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;

final class TestingAwareRateLimiter extends RateLimiter
{
    public function __construct(Repository $cache, array $limiters = [])
    {
        parent::__construct($cache);

        // Carry over any named limiter definitions that were registered before the decorator wrapped the instance.
        $this->limiters = $limiters;
    }

    public function fake(): void
    {
        // Swap the cache store to an in-memory array driver so repeated tests do not leak state across assertions.
        $this->cache = Cache::driver('array');
        $this->cache->clear();
    }
}
