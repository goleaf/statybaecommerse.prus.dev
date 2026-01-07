<?php

declare(strict_types=1);

namespace App\Services\VersionCompatibility\Strategies;

use App\Services\VersionCompatibility\TransformationResult;

/**
 * Transforms Filament v4 page configuration to v3.3 format
 */
final class PageConfigurationTransformationStrategy extends AbstractTransformationStrategy
{
    public function getName(): string
    {
        return 'Page Configuration Transformation';
    }

    public function canHandle(string $content): bool
    {
        return str_contains($content, 'Filament\Resources\Pages\Page');
    }

    public function transform(string $content): TransformationResult
    {
        $originalContent = $content;

        // Currently no specific page transformations needed for v3.3
        // This strategy is prepared for future page-specific transformations

        return $this->createResult($originalContent, $content);
    }
}
