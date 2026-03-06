<?php

declare(strict_types=1);

namespace App\Support\Filesystem;

use Illuminate\Filesystem\Filesystem;

/**
 * Handles directory scanning with fallback mechanisms.
 */
final class DirectoryScanner
{
    public function __construct(
        private readonly Filesystem $filesystem,
        private readonly DirectoryMemoryManager $memoryManager
    ) {}

    /**
     * @return array<int, string>
     */
    public function scanDirectories(string $directory, int $depth): array
    {
        clearstatcache();

        $directories = $this->filesystem->directories($directory, $depth);

        // Fallback for Symfony Finder lag in fast test loops
        if (empty($directories) && $this->filesystem->isDirectory($directory)) {
            $directories = $this->fallbackGlobScan($directory);
        }

        return $this->mergeWithRememberedDirectories($directory, $directories);
    }

    /**
     * @return array<int, string>
     */
    private function fallbackGlobScan(string $directory): array
    {
        $entries = @scandir($directory);

        if (! is_array($entries)) {
            return [];
        }

        $paths = [];

        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $path = rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $entry;

            if (is_dir($path)) {
                $paths[] = $path;
            }
        }

        return $paths;
    }

    /**
     * @param  array<int, string> $directories
     * @return array<int, string>
     */
    private function mergeWithRememberedDirectories(string $directory, array $directories): array
    {
        $prefix = rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $rememberedDirectories = $this->memoryManager->getRecentDirectoriesForPrefix($prefix);

        return array_values(array_unique(array_merge($directories, $rememberedDirectories)));
    }
}
