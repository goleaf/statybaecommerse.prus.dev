<?php

declare(strict_types=1);

namespace App\Support\Health;

use App\Contracts\HealthReporter as HealthReporterContract;
use Illuminate\Contracts\Cache\Factory as CacheFactory;
use Illuminate\Contracts\Queue\Factory as QueueFactory;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Throwable;

final class HealthReporter implements HealthReporterContract
{
    public function __construct(
        private readonly DatabaseManager $database,
        private readonly CacheFactory $cache,
        private readonly QueueFactory $queue,
    ) {}

    /**
     * @return array{status: string, checks: array<string, array<string, mixed>>, timestamp: string}
     */
    public function report(bool $includeQueue = false): array
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
        ];

        if ($includeQueue) {
            $queueCheck = $this->checkQueue();

            if ($queueCheck !== null) {
                $checks['queue'] = $queueCheck;
            }
        }

        return [
            'status' => $this->evaluateStatus($checks),
            'checks' => $checks,
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * @return array{status: string, latency_ms: float, message?: string}
     */
    private function checkDatabase(): array
    {
        $startedAt = microtime(true);

        try {
            $connection = $this->database->connection();
            $connection->select('select 1');

            return $this->formatResult($startedAt);
        } catch (Throwable $exception) {
            return $this->formatResult($startedAt, $exception);
        }
    }

    /**
     * @return array{status: string, latency_ms: float, message?: string}
     */
    private function checkCache(): array
    {
        $startedAt = microtime(true);
        $repository = $this->cache->store();
        $key = 'health-check-'.Str::uuid()->toString();

        try {
            $repository->put($key, 'ok', 5);
            $repository->forget($key);

            return $this->formatResult($startedAt);
        } catch (Throwable $exception) {
            return $this->formatResult($startedAt, $exception);
        }
    }

    /**
     * @return array{status: string, latency_ms: float, message?: string, meta?: array{connection: string, driver: string}}|null
     */
    private function checkQueue(): ?array
    {
        $defaultConnection = config('queue.default');

        if (! \is_string($defaultConnection) || $defaultConnection === '') {
            return null;
        }

        $driver = Arr::get(config('queue.connections'), $defaultConnection.'.driver');

        if (! \is_string($driver) || \in_array($driver, ['sync', 'null'], true)) {
            return null;
        }

        $startedAt = microtime(true);

        try {
            $connection = $this->queue->connection($defaultConnection);

            if (method_exists($connection, 'getConnectionName')) {
                $connection->getConnectionName();
            }

            return $this->formatResult($startedAt, null, [
                'connection' => $defaultConnection,
                'driver' => $driver,
            ]);
        } catch (Throwable $exception) {
            return $this->formatResult($startedAt, $exception, [
                'connection' => $defaultConnection,
                'driver' => (string) $driver,
            ]);
        }
    }

    /**
     * @param array<string, array{status: string, latency_ms: float, message?: string}> $checks
     */
    private function evaluateStatus(array $checks): string
    {
        foreach ($checks as $check) {
            if ($check['status'] !== 'ok') {
                return 'error';
            }
        }

        return 'ok';
    }

    /**
     * @param float $startedAt microtime(true) value
     * @param array{connection: string, driver: string}|null $meta
     *
     * @return array{status: string, latency_ms: float, message?: string, meta?: array{connection: string, driver: string}}
     */
    private function formatResult(float $startedAt, ?Throwable $exception = null, ?array $meta = null): array
    {
        $result = [
            'status' => $exception === null ? 'ok' : 'failed',
            'latency_ms' => $this->elapsedMilliseconds($startedAt),
        ];

        if ($exception !== null) {
            $result['message'] = $exception->getMessage();
        }

        if ($meta !== null) {
            $result['meta'] = $meta;
        }

        return $result;
    }

    private function elapsedMilliseconds(float $startedAt): float
    {
        return round((microtime(true) - $startedAt) * 1000, 2);
    }
}
