<?php

declare(strict_types=1);

namespace App\Services\VersionCompatibility\ValueObjects;

/**
 * Value object for tracking transformation performance metrics
 */
final readonly class TransformationMetrics
{
    private function __construct(
        private float $startTime,
        private ?float $endTime = null,
        private int $transformationsApplied = 0
    ) {}

    public static function start(): self
    {
        return new self(microtime(true));
    }

    public function finish(int $transformationsApplied): self
    {
        return new self(
            $this->startTime,
            microtime(true),
            $transformationsApplied
        );
    }

    public function getDurationMs(): float
    {
        $endTime = $this->endTime ?? microtime(true);

        return round(($endTime - $this->startTime) * 1000, 2);
    }

    public function getTransformationsApplied(): int
    {
        return $this->transformationsApplied;
    }

    public function isCompleted(): bool
    {
        return $this->endTime !== null;
    }

    public function toArray(): array
    {
        return [
            'duration_ms'             => $this->getDurationMs(),
            'transformations_applied' => $this->transformationsApplied,
            'is_completed'            => $this->isCompleted(),
        ];
    }
}
