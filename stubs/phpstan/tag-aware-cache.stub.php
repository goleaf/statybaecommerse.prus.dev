<?php

namespace App\Support\Cache;

use Closure;
use DateInterval;
use DateTimeInterface;
use Stringable;

final class TagAwareCache
{
    /**
     * @param  callable(): mixed  $callback
     * @param  array<int, Stringable|scalar|null>  $tags
     * @return mixed
     */
    public static function remember(string $key, DateInterval|DateTimeInterface|int $ttl, callable $callback, array $tags = []);

    /**
     * @param  array<int, Stringable|scalar|null>  $tags
     */
    public static function flush(array $tags): void;

    public static function fake(): TagAwareCacheFake;

    public static function restore(): void;
}

final class TagAwareCacheFake
{
    /**
     * @param  callable(): mixed  $callback
     * @param  array<int, Stringable|scalar|null>  $tags
     * @return mixed
     */
    public function remember(string $key, DateInterval|DateTimeInterface|int $ttl, callable $callback, array $tags = []);

    /**
     * @param  array<int, Stringable|scalar|null>  $tags
     */
    public function flush(array $tags): void;

    /**
     * @param  array<int, string>  $expectedTags
     */
    public function assertFlushed(array $expectedTags): void;

    public function assertNothingFlushed(): void;
}
