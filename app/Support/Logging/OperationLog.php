<?php

declare(strict_types=1);

namespace App\Support\Logging;

use Throwable;

final class OperationLog
{
    private bool $completed = false;

    public function __construct(
        private readonly StructuredLogger $logger,
        private readonly string $operation,
        private readonly array $context,
        private readonly float $startedAt
    ) {}

    public function context(): array
    {
        return $this->context;
    }

    public function operation(): string
    {
        return $this->operation;
    }

    public function startedAt(): float
    {
        return $this->startedAt;
    }

    public function finish(array $metrics = [], ?string $message = null): void
    {
        if ($this->completed) {
            return;
        }

        $this->completed = true;
        $this->logger->logFinish($this, $metrics, $message);
    }

    public function fail(Throwable $throwable, array $context = []): void
    {
        if ($this->completed) {
            return;
        }

        $this->completed = true;
        $this->logger->logFailure($this, $throwable, $context);
    }
}
