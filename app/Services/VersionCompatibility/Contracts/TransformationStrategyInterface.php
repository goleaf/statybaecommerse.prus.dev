<?php

declare(strict_types=1);

namespace App\Services\VersionCompatibility\Contracts;

use App\Services\VersionCompatibility\TransformationResult;

/**
 * Interface for transformation strategies that convert Filament v4 code to v3.3 format
 */
interface TransformationStrategyInterface
{
    /**
     * Transform the given content
     */
    public function transform(string $content): TransformationResult;

    /**
     * Get the name of this transformation strategy
     */
    public function getName(): string;

    /**
     * Check if this strategy can handle the given content
     */
    public function canHandle(string $content): bool;
}
