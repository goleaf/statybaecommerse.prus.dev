<?php

declare(strict_types=1);

namespace App\Support\Filesystem;

/**
 * Manages recently created directories for immediate visibility in subsequent listings.
 */
final class DirectoryMemoryManager
{
    /** @var array<string, int> */
    private array $recentDirectories = [];

    private const MAX_REMEMBERED_DIRECTORIES = 100;

    private const CLEANUP_THRESHOLD = 110;

    public function remember(string $directory): void
    {
        if ($directory === '') {
            return;
        }

        $normalizedDirectory = rtrim($directory, "/\\");
        $this->recentDirectories[$normalizedDirectory] = time();

        $this->cleanupIfNeeded();
    }

    /**
     * @return array<int, string>
     */
    public function getRecentDirectoriesForPrefix(string $prefix): array
    {
        if ($prefix === '') {
            return [];
        }

        $normalizedPrefix = $this->normalizeForMatching(rtrim($prefix, "/\\") . DIRECTORY_SEPARATOR);

        return array_keys(array_filter(
            $this->recentDirectories,
            fn (int $timestamp, string $directory): bool => str_starts_with(
                $this->normalizeForMatching($directory),
                $normalizedPrefix
            ),
            ARRAY_FILTER_USE_BOTH
        ));
    }

    public function clear(): void
    {
        $this->recentDirectories = [];
    }

    public function count(): int
    {
        return count($this->recentDirectories);
    }

    private function cleanupIfNeeded(): void
    {
        $currentCount = count($this->recentDirectories);

        if ($currentCount <= self::CLEANUP_THRESHOLD) {
            return;
        }

        // Keep only the most recent directories
        arsort($this->recentDirectories);
        $this->recentDirectories = array_slice(
            $this->recentDirectories,
            0,
            self::MAX_REMEMBERED_DIRECTORIES,
            true
        );
    }

    private function normalizeForMatching(string $path): string
    {
        return str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
    }
}
