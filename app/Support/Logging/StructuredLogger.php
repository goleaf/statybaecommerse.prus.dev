<?php

declare(strict_types=1);

namespace App\Support\Logging;

use Illuminate\Support\Facades\Log;
use Throwable;

final class StructuredLogger
{
    public function __construct(private readonly LogContext $context) {}

    /**
     * @param array<string, mixed> $context
     */
    public function operation(string $operation, array $context = []): OperationLog
    {
        $operationLog = new OperationLog($this, $operation, $context, microtime(true));

        $this->log('info', 'operation_started', [
            'event'     => 'start',
            'operation' => $operation,
            'context'   => $context,
        ]);

        return $operationLog;
    }

    public function logFinish(OperationLog $operation, array $metrics = [], ?string $message = null): void
    {
        $durationMs = (int) round((microtime(true) - $operation->startedAt()) * 1000);

        $this->log('info', $message ?? 'operation_finished', [
            'event'       => 'finish',
            'operation'   => $operation->operation(),
            'context'     => $operation->context(),
            'metrics'     => $metrics,
            'duration_ms' => $durationMs,
        ]);
    }

    public function logFailure(OperationLog $operation, Throwable $throwable, array $context = []): void
    {
        $durationMs = (int) round((microtime(true) - $operation->startedAt()) * 1000);

        $this->log('error', 'operation_failed', [
            'event'       => 'error',
            'operation'   => $operation->operation(),
            'context'     => $operation->context(),
            'metrics'     => $context,
            'duration_ms' => $durationMs,
            'exception'   => $throwable::class,
            'error'       => $throwable->getMessage(),
        ]);
    }

    /**
     * @param array<string, mixed> $context
     */
    public function log(string $level, string $message, array $context = []): void
    {
        Log::channel(config('logging.default'))
            ->log($level, $message, array_merge($this->context->toArray(), $context));
    }
}
