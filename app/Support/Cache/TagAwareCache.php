<?php

declare(strict_types=1);

namespace App\Support\Cache;

use Closure;
use DateInterval;
use DateTimeInterface;
use Illuminate\Support\Facades\Cache;
use Stringable;

final class TagAwareCache
{
    private static ?TagAwareCacheFake $fake = null;

    /**
     * @param  callable(): mixed  $callback
     * @param  array<int, Stringable|scalar|null>  $tags
     * @return mixed
     */
    public static function remember(string $key, DateInterval|DateTimeInterface|int $ttl, callable $callback, array $tags = [])
    {
        if (! $callback instanceof Closure) {
            $callback = Closure::fromCallable($callback);
        }
        /** @var Closure(): mixed $callback */
        if (self::$fake instanceof TagAwareCacheFake) {
            return self::$fake->remember($key, $ttl, $callback, $tags);
        }

        $normalizedTags = self::normalizeTags($tags);

        if ($normalizedTags !== [] && Cache::supportsTags()) {
            return Cache::tags($normalizedTags)->remember($key, $ttl, $callback);
        }

        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * Flush caches for the given tag set.
     *
     * @param  array<int, Stringable|scalar|null>  $tags
     */
    public static function flush(array $tags): void
    {
        if (self::$fake instanceof TagAwareCacheFake) {
            self::$fake->flush($tags);

            return;
        }

        $normalizedTags = self::normalizeTags($tags);

        if ($normalizedTags === []) {
            return;
        }

        if (Cache::supportsTags()) {
            Cache::tags($normalizedTags)->flush();
        }
    }

    public static function fake(): TagAwareCacheFake
    {
        $fake = new TagAwareCacheFake;
        self::$fake = $fake;

        return $fake;
    }

    public static function restore(): void
    {
        self::$fake = null;
    }

    /**
     * @param  array<int, Stringable|scalar|null>  $tags
     * @return array<int, string>
     */
    private static function normalizeTags(array $tags): array
    {
        $normalized = [];

        foreach ($tags as $tag) {
            if ($tag instanceof Stringable) {
                $tag = (string) $tag;
            } elseif (is_scalar($tag)) {
                $tag = (string) $tag;
            } else {
                continue;
            }

            $tag = trim($tag);

            if ($tag === '') {
                continue;
            }

            $normalized[$tag] = $tag;
        }

        return array_values($normalized);
    }
}
