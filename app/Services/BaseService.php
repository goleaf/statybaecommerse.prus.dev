<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\Common\ServiceResponseData;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Base service class providing standardized patterns for all business services
 *
 * Features:
 * - Standardized response format
 * - Transaction management
 * - Error handling and logging
 * - Context tracking for multi-tenancy
 * - Performance monitoring
 */
abstract class BaseService
{
    protected ?Authenticatable $user = null;

    protected array $context = [];

    protected bool $logPerformance = false;

    protected int $slowThresholdMs = 1000;

    public function __construct()
    {
        $this->user = auth()->user();
        $this->context = [
            'service'    => static::class,
            'user_id'    => $this->user?->id,
            'session_id' => session()->getId(),
            'timestamp'  => now()->toISOString(),
        ];
    }

    /**
     * Execute operation within database transaction
     */
    protected function executeInTransaction(callable $callback): ServiceResponseData
    {
        $startTime = microtime(true);

        try {
            $result = DB::transaction($callback);

            $this->logPerformanceIfNeeded($startTime, 'transaction_success');

            if ($result instanceof ServiceResponseData) {
                return $result;
            }

            return $this->success($result);
        } catch (Throwable $e) {
            $this->logPerformanceIfNeeded($startTime, 'transaction_failed');

            return $this->handleException($e);
        }
    }

    /**
     * Execute operation without transaction
     */
    protected function execute(callable $callback): ServiceResponseData
    {
        $startTime = microtime(true);

        try {
            $result = $callback();

            $this->logPerformanceIfNeeded($startTime, 'operation_success');

            if ($result instanceof ServiceResponseData) {
                return $result;
            }

            return $this->success($result);
        } catch (Throwable $e) {
            $this->logPerformanceIfNeeded($startTime, 'operation_failed');

            return $this->handleException($e);
        }
    }

    /**
     * Create success response
     */
    protected function success(mixed $data = null, string $message = ''): ServiceResponseData
    {
        return ServiceResponseData::success($data, $message);
    }

    /**
     * Create error response
     */
    protected function error(string $message, mixed $data = null, int $code = 400): ServiceResponseData
    {
        return ServiceResponseData::error($message, $data, $code);
    }

    /**
     * Handle exceptions with proper logging and response formatting
     */
    protected function handleException(Throwable $e): ServiceResponseData
    {
        $this->log('error', 'Service operation failed', [
            'exception' => get_class($e),
            'message'   => $e->getMessage(),
            'file'      => $e->getFile(),
            'line'      => $e->getLine(),
            'trace'     => $e->getTraceAsString(),
        ]);

        // Don't expose internal errors in production
        $message = app()->isProduction()
            ? __('common.operation_failed')
            : $e->getMessage();

        return $this->error($message, null, 500);
    }

    /**
     * Log with service context
     */
    protected function log(string $level, string $message, array $context = []): void
    {
        Log::log($level, $message, array_merge($this->context, $context));
    }

    /**
     * Add context for current operation
     */
    protected function withContext(array $context): static
    {
        $this->context = array_merge($this->context, $context);

        return $this;
    }

    /**
     * Enable performance logging for this service
     */
    protected function enablePerformanceLogging(int $thresholdMs = 1000): static
    {
        $this->logPerformance = true;
        $this->slowThresholdMs = $thresholdMs;

        return $this;
    }

    /**
     * Validate model ownership for multi-tenant operations
     */
    protected function validateOwnership(Model $model, ?int $userId = null): bool
    {
        $userId = $userId ?? auth()->id() ?? $this->user?->id;

        if (! $userId) {
            return false;
        }

        // Prefer explicit user_id ownership when it is populated.
        if ($model->hasAttribute('user_id') && $model->user_id !== null) {
            return $model->user_id === $userId;
        }

        // Fall back to legacy customer_id ownership for backward compatibility.
        if ($model->hasAttribute('customer_id') && $model->customer_id !== null) {
            return $model->customer_id === $userId;
        }

        if ($model->hasAttribute('user_id') || $model->hasAttribute('customer_id')) {
            return false;
        }

        return true; // No ownership field found, allow operation
    }

    /**
     * Log performance metrics if enabled and threshold exceeded
     */
    private function logPerformanceIfNeeded(float $startTime, string $operation): void
    {
        if (! $this->logPerformance) {
            return;
        }

        $duration = (microtime(true) - $startTime) * 1000;

        if ($duration > $this->slowThresholdMs) {
            $this->log('warning', 'Slow service operation detected', [
                'operation'    => $operation,
                'duration_ms'  => round($duration, 2),
                'threshold_ms' => $this->slowThresholdMs,
            ]);
        }
    }
}
