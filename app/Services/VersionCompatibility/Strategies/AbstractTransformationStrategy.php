<?php

declare(strict_types=1);

namespace App\Services\VersionCompatibility\Strategies;

use App\Services\VersionCompatibility\Contracts\TransformationStrategyInterface;
use App\Services\VersionCompatibility\TransformationResult;

/**
 * Base class for transformation strategies with common functionality
 */
abstract class AbstractTransformationStrategy implements TransformationStrategyInterface
{
    /**
     * Apply multiple string replacements to content with optimization
     *
     * Performance improvement: Use strtr() for multiple replacements
     * which is significantly faster than multiple str_replace() calls
     */
    protected function applyReplacements(string $content, array $replacements): string
    {
        // Use strtr() for better performance with multiple replacements
        return strtr($content, $replacements);
    }

    /**
     * Apply regex replacements to content with compiled pattern caching
     */
    protected function applyRegexReplacements(string $content, array $patterns): string
    {
        foreach ($patterns as $pattern => $replacement) {
            // Validate pattern once and cache if needed
            if (! isset($this->compiledPatterns[$pattern])) {
                // Validate regex pattern
                if (@preg_match($pattern, '') === false) {
                    continue; // Skip invalid patterns
                }
                $this->compiledPatterns[$pattern] = true;
            }

            $content = preg_replace($pattern, $replacement, $content);
        }

        return $content;
    }

    private array $compiledPatterns = [];

    /**
     * Check if content was actually transformed
     */
    protected function wasContentChanged(string $original, string $transformed): bool
    {
        return $original !== $transformed;
    }

    /**
     * Create transformation result
     */
    protected function createResult(string $originalContent, string $transformedContent): TransformationResult
    {
        $wasTransformed = $this->wasContentChanged($originalContent, $transformedContent);

        return new TransformationResult(
            $transformedContent,
            $wasTransformed,
            $wasTransformed ? [static::class] : []
        );
    }
}
