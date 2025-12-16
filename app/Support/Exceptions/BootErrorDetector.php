<?php

declare(strict_types=1);

namespace App\Support\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Detects boot-related errors with optimized pattern matching.
 */
final class BootErrorDetector
{
    private static ?bool $enabled = null;

    private static ?array $patterns = null;

    private static ?array $paths = null;

    public function shouldProcess(Throwable $e): bool
    {
        // In testing environment, always check config fresh to allow dynamic changes
        if (app()->environment('testing')) {
            $enabled = config('exception-handling.boot_error_detection.enabled', true);
        } else {
            // Cache the config value to avoid repeated config() calls in production
            if (self::$enabled === null) {
                self::$enabled = config('exception-handling.boot_error_detection.enabled', true);
            }
            $enabled = self::$enabled;
        }

        // Fast exit if boot error detection is disabled
        if (! $enabled) {
            return false;
        }

        // Fast exit for common non-boot exceptions (most frequent first)
        if ($e instanceof ValidationException || $e instanceof AuthenticationException) {
            return false;
        }

        return $this->isBootError($e);
    }

    public function isBootError(Throwable $e): bool
    {
        return $this->matchesBootErrorPatterns($e) || $this->isBootRelatedFile($e);
    }

    public function isTranslatableRecordError(Throwable $e): bool
    {
        $message = $e->getMessage();

        return str_contains($message, 'translations()') || str_contains($message, 'TranslatableRecord');
    }

    public function identifyErrorPattern(Throwable $e): string
    {
        $message = $e->getMessage();
        $patterns = $this->getPatterns();

        foreach ($patterns as $pattern) {
            if (str_contains($message, $pattern)) {
                return $pattern;
            }
        }

        return 'unknown';
    }

    public function identifyFileType(Throwable $e): string
    {
        $file = $e->getFile();
        $paths = $this->getPaths();

        foreach ($paths as $path) {
            if (str_contains($file, $path)) {
                return trim($path, '/');
            }
        }

        return 'other';
    }

    private function matchesBootErrorPatterns(Throwable $e): bool
    {
        $message = $e->getMessage();
        $patterns = $this->getPatterns();

        // Optimized pattern matching - check most common patterns first
        // Use stripos for case-insensitive matching
        foreach ($patterns as $pattern) {
            if (stripos($message, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    private function isBootRelatedFile(Throwable $e): bool
    {
        $file = $e->getFile();
        $paths = $this->getPaths();

        // Optimized path checking - most common paths first
        foreach ($paths as $path) {
            if (str_contains($file, $path)) {
                return true;
            }
        }

        return false;
    }

    private function getPatterns(): array
    {
        // In testing environment, always get fresh config to allow dynamic changes
        if (app()->environment('testing')) {
            return config('exception-handling.boot_error_detection.patterns', [
                'Interface',
                'not found',
                'undefined method',
                'Cannot declare class',
                'Fatal error',
                'Parse error',
                'Syntax error',
                'translations()',
                'TranslatableRecord',
            ]);
        }

        if (self::$patterns === null) {
            self::$patterns = config('exception-handling.boot_error_detection.patterns', [
                'Interface',
                'not found',
                'undefined method',
                'Cannot declare class',
                'Fatal error',
                'Parse error',
                'Syntax error',
                'translations()',
                'TranslatableRecord',
            ]);
        }

        return self::$patterns;
    }

    private function getPaths(): array
    {
        // In testing environment, always get fresh config to allow dynamic changes
        if (app()->environment('testing')) {
            return config('exception-handling.boot_error_detection.paths', [
                '/Models/',
                '/Contracts/',
                '/Providers/',
                '/bootstrap/',
            ]);
        }

        if (self::$paths === null) {
            self::$paths = config('exception-handling.boot_error_detection.paths', [
                '/Models/',
                '/Contracts/',
                '/Providers/',
                '/bootstrap/',
            ]);
        }

        return self::$paths;
    }

    /**
     * Reset cached configuration (for testing).
     */
    public static function resetCache(): void
    {
        self::$enabled = null;
        self::$patterns = null;
        self::$paths = null;
    }
}
