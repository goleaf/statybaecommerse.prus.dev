<?php

declare(strict_types=1);

namespace App\Services\VersionCompatibility;

/**
 * Value object representing the result of a transformation operation
 */
final readonly class TransformationResult
{
    public function __construct(
        private string $content,
        private bool $wasTransformed,
        private array $appliedTransformations = [],
        private ?string $error = null
    ) {}

    public function getContent(): string
    {
        return $this->content;
    }

    public function wasTransformed(): bool
    {
        return $this->wasTransformed;
    }

    public function getAppliedTransformations(): array
    {
        return $this->appliedTransformations;
    }

    public function hasError(): bool
    {
        return $this->error !== null;
    }

    public function getError(): ?string
    {
        return $this->error;
    }

    public function isSuccessful(): bool
    {
        return ! $this->hasError();
    }
}
