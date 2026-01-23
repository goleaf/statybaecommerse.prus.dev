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
        $pattern = rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '*';
        $paths = glob($pattern, GLOB_ONLYDIR) ?: [];

        return array_values(array_filter(
            $paths,
            static fn ($path): bool => is_string($path) && $path !== ''
        ));
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
