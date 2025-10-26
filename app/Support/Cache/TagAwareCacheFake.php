<?php

declare(strict_types=1);

namespace App\Support\Cache;

use DateInterval;
use DateTimeInterface;
use PHPUnit\Framework\Assert;
use Stringable;

final class TagAwareCacheFake
{
    /** @var array<int, array<int, string>> */
    private array $flushed = [];

    /**
     * @param  callable(): mixed                  $callback
     * @param  array<int, Stringable|scalar|null> $tags
     * @return mixed
     */
    public function remember(string $key, DateInterval|DateTimeInterface|int $ttl, callable $callback, array $tags = [])
    {
        // Execute the callback immediately for deterministic behaviour in tests.
        return $callback();
    }

    /**
     * @param array<int, Stringable|scalar|null> $tags
     */
    public function flush(array $tags): void
    {
        $normalized = $this->normalize($tags);

        if ($normalized === []) {
            return;
        }

        $this->flushed[] = $normalized;
    }

    /**
     * Assert that the provided tags were flushed at least once.
     *
     * @param array<int, string> $expectedTags
     */
    public function assertFlushed(array $expectedTags): void
    {
        $expected = $this->normalize($expectedTags);

        foreach ($this->flushed as $actual) {
            if ($this->containsAll($actual, $expected)) {
                return;
            }
        }

        Assert::fail('Failed asserting that the expected cache tags were flushed.');
    }

    public function assertNothingFlushed(): void
    {
        Assert::assertSame([], $this->flushed, 'Expected no cache tags to be flushed.');
    }

    /**
     * @param array<int, string> $haystack
     * @param array<int, string> $needles
     */
    private function containsAll(array $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (! in_array($needle, $haystack, true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array<int, Stringable|scalar|null> $tags
     * @return array<int, string>
     */
    private function normalize(array $tags): array
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

        ksort($normalized);

        return array_values($normalized);
    }
}
