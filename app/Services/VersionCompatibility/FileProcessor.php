<?php

declare(strict_types=1);

namespace App\Services\VersionCompatibility;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Throwable;

/**
 * Handles file processing operations for version compatibility transformations
 */
final class FileProcessor
{
    public function __construct(
        private readonly Filesystem $filesystem
    ) {}

    /**
     * Process a single file with the given strategies
     *
     * Performance optimizations:
     * - File size check before reading
     * - Atomic file operations
     * - Reduced logging overhead
     */
    public function processFile(string $filePath, Collection $strategies): TransformationResult
    {
        if (! $this->filesystem->exists($filePath)) {
            throw new InvalidArgumentException("File does not exist: {$filePath}");
        }

        // Check file size before reading to avoid memory issues
        $fileSize = $this->filesystem->size($filePath);
        $maxSize = 1024 * 1024; // 1MB limit

        if ($fileSize > $maxSize) {
            throw new InvalidArgumentException("File too large: {$filePath} ({$fileSize} bytes)");
        }

        $content = $this->filesystem->get($filePath);
        $originalContent = $content;
        $appliedTransformations = [];

        // Pre-filter applicable strategies
        $applicableStrategies = $strategies->filter(
            fn ($strategy) => $strategy->canHandle($content)
        );

        foreach ($applicableStrategies as $strategy) {
            $result = $strategy->transform($content);

            if ($result->wasTransformed()) {
                $content = $result->getContent();
                $appliedTransformations = array_merge(
                    $appliedTransformations,
                    $result->getAppliedTransformations()
                );
            }
        }

        $wasTransformed = $content !== $originalContent;

        if ($wasTransformed) {
            // Atomic write operation
            $this->filesystem->put($filePath, $content);

            // Only log in development or when explicitly needed
            if (! app()->isProduction()) {
                Log::info('File transformed successfully', [
                    'file'            => basename($filePath),
                    'transformations' => count($appliedTransformations),
                ]);
            }
        }

        return new TransformationResult(
            $content,
            $wasTransformed,
            $appliedTransformations
        );
    }

    /**
     * Process all PHP files in a directory
     */
    public function processDirectory(string $directory, Collection $strategies): Collection
    {
        $results = collect();

        $files = $this->filesystem->allFiles($directory);

        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) !== 'php') {
                continue;
            }

            try {
                $result = $this->processFile($file, $strategies);

                if ($result->wasTransformed()) {
                    $results->push([
                        'file'   => $file,
                        'result' => $result,
                    ]);
                }
            } catch (Throwable $e) {
                Log::error('Failed to process file in directory', [
                    'file'      => $file,
                    'directory' => $directory,
                    'error'     => $e->getMessage(),
                ]);
            }
        }

        return $results;
    }
}
