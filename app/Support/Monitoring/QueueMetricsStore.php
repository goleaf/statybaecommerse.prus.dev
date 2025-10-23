<?php

declare(strict_types=1);

namespace App\Support\Monitoring;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

final class QueueMetricsStore
{
    private const DEFAULT_PAYLOAD = [
        'total_failed' => 0,
        'total_processed' => 0,
        'queues' => [],
        'updated_at' => null,
        'last_failure' => null,
    ];

    public function __construct(
        private readonly CacheRepository $repository,
        private readonly string $cacheKey,
    ) {
    }

    public function recordProcessed(JobProcessed $event): void
    {
        $this->update(function (array $payload) use ($event): array {
            $connection = $event->connectionName ?? config('queue.default');
            $queue = $event->job?->getQueue() ?? 'default';
            $key = $this->queueKey($connection, $queue);

            $payload['total_processed']++;
            $payload['queues'][$key] = $payload['queues'][$key] ?? $this->initialQueuePayload($connection, $queue);
            $payload['queues'][$key]['processed']++;
            $payload['queues'][$key]['last_processed_at'] = Carbon::now()->toIso8601String();

            return $payload;
        });
    }

    public function recordFailure(JobFailed $event): void
    {
        $this->update(function (array $payload) use ($event): array {
            $connection = $event->connectionName ?? config('queue.default');
            $queue = $event->job?->getQueue() ?? 'default';
            $jobName = method_exists($event->job, 'resolveName') ? (string) $event->job->resolveName() : $event->job?->getName();
            $key = $this->queueKey($connection, $queue);

            $payload['total_failed']++;
            $payload['queues'][$key] = $payload['queues'][$key] ?? $this->initialQueuePayload($connection, $queue);
            $payload['queues'][$key]['failed']++;
            $payload['queues'][$key]['last_failed_at'] = Carbon::now()->toIso8601String();
            $payload['queues'][$key]['last_failed_job'] = $jobName ?: 'unknown';
            $payload['queues'][$key]['last_exception_message'] = Str::limit($event->exception?->getMessage() ?? '', 240);

            $payload['last_failure'] = [
                'connection' => $connection,
                'queue' => $queue,
                'job' => $jobName ?: 'unknown',
                'failed_at' => $payload['queues'][$key]['last_failed_at'],
                'exception' => Str::limit($event->exception?->getMessage() ?? '', 240),
            ];

            return $payload;
        });
    }

    /**
     * @return array{
     *     total_failed: int,
     *     total_processed: int,
     *     queues: array<int, array{
     *         connection: string,
     *         queue: string,
     *         failed: int,
     *         processed: int,
     *         last_failed_at: string|null,
     *         last_failed_job: string|null,
     *         last_exception_message: string|null,
     *         last_processed_at: string|null,
     *     }>,
     *     updated_at: string|null,
     *     last_failure: array|null,
     * }
     */
    public function snapshot(): array
    {
        $payload = $this->repository->get($this->cacheKey, self::DEFAULT_PAYLOAD);

        $queues = [];
        foreach ($payload['queues'] ?? [] as $item) {
            $queues[] = [
                'connection' => (string) ($item['connection'] ?? 'default'),
                'queue' => (string) ($item['queue'] ?? 'default'),
                'failed' => (int) ($item['failed'] ?? 0),
                'processed' => (int) ($item['processed'] ?? 0),
                'last_failed_at' => $item['last_failed_at'] ?? null,
                'last_failed_job' => $item['last_failed_job'] ?? null,
                'last_exception_message' => $item['last_exception_message'] ?? null,
                'last_processed_at' => $item['last_processed_at'] ?? null,
            ];
        }

        return [
            'total_failed' => (int) ($payload['total_failed'] ?? 0),
            'total_processed' => (int) ($payload['total_processed'] ?? 0),
            'queues' => $queues,
            'updated_at' => $payload['updated_at'] ?? null,
            'last_failure' => $payload['last_failure'] ?? null,
        ];
    }

    private function update(callable $callback): void
    {
        $payload = $this->repository->get($this->cacheKey, self::DEFAULT_PAYLOAD);
        $payload = $callback($payload);
        $payload['updated_at'] = Carbon::now()->toIso8601String();

        $this->repository->forever($this->cacheKey, $payload);
    }

    private function initialQueuePayload(string $connection, string $queue): array
    {
        return [
            'connection' => $connection,
            'queue' => $queue,
            'failed' => 0,
            'processed' => 0,
            'last_failed_at' => null,
            'last_failed_job' => null,
            'last_exception_message' => null,
            'last_processed_at' => null,
        ];
    }

    private function queueKey(string $connection, string $queue): string
    {
        return $connection.':'.$queue;
    }
}
