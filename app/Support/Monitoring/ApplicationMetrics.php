<?php

declare(strict_types=1);

namespace App\Support\Monitoring;

use Illuminate\Contracts\Database\ConnectionResolverInterface;
use Illuminate\Database\QueryException;
use Illuminate\Queue\QueueManager;
use Illuminate\Support\Facades\Log;
use function collect;

final class ApplicationMetrics
{
    public function __construct(
        private readonly CacheMetricsStore $cacheMetrics,
        private readonly QueueMetricsStore $queueMetrics,
        private readonly QueueManager $queueManager,
        private readonly ConnectionResolverInterface $connectionResolver,
    ) {
    }

    /**
     * @return array{
     *     queues: array{
     *         depth: array<int, array{connection: string, queue: string, size: int}>,
     *         metrics: array{
     *             total_failed: int,
     *             total_processed: int,
     *             queues: array<int, array{
     *                 connection: string,
     *                 queue: string,
     *                 failed: int,
     *                 processed: int,
     *                 last_failed_at: string|null,
     *                 last_failed_job: string|null,
     *                 last_exception_message: string|null,
     *                 last_processed_at: string|null,
     *             }>,
     *             updated_at: string|null,
     *             last_failure: array|null,
     *         },
     *         failed_jobs_table: int,
     *     },
     *     cache: array{
     *         hits: int,
     *         misses: int,
     *         hit_rate: float,
     *         stores: array<int, array{store: string, hits: int, misses: int, hit_rate: float}>,
     *         updated_at: string|null,
     *     },
     * }
     */
    public function snapshot(): array
    {
        $queueDepth = $this->gatherQueueDepth();
        $queueMetrics = $this->queueMetrics->snapshot();
        $failedJobs = $this->countFailedJobs();

        return [
            'queues' => [
                'depth' => $queueDepth,
                'metrics' => $queueMetrics,
                'failed_jobs_table' => $failedJobs,
            ],
            'cache' => $this->cacheMetrics->snapshot(),
        ];
    }

    public function toPrometheus(): string
    {
        $snapshot = $this->snapshot();
        $lines = [];

        $lines[] = '# HELP queue_depth Number of pending jobs for each queue.';
        $lines[] = '# TYPE queue_depth gauge';
        foreach ($snapshot['queues']['depth'] as $metric) {
            $labels = $this->formatLabels([
                'connection' => $metric['connection'],
                'queue' => $metric['queue'],
            ]);
            $lines[] = sprintf('queue_depth%s %d', $labels, $metric['size']);
        }

        $lines[] = '# HELP job_failures_total Total failed jobs recorded in the failed_jobs table.';
        $lines[] = '# TYPE job_failures_total counter';
        $lines[] = sprintf('job_failures_total %d', $snapshot['queues']['failed_jobs_table']);

        $lines[] = '# HELP queue_job_failures_total Runtime queue failure counters grouped by queue.';
        $lines[] = '# TYPE queue_job_failures_total counter';
        foreach ($snapshot['queues']['metrics']['queues'] as $metric) {
            $labels = $this->formatLabels([
                'connection' => $metric['connection'],
                'queue' => $metric['queue'],
            ]);
            $lines[] = sprintf('queue_job_failures_total%s %d', $labels, $metric['failed']);
        }

        $lines[] = '# HELP queue_jobs_processed_total Runtime queue processed counters grouped by queue.';
        $lines[] = '# TYPE queue_jobs_processed_total counter';
        foreach ($snapshot['queues']['metrics']['queues'] as $metric) {
            $labels = $this->formatLabels([
                'connection' => $metric['connection'],
                'queue' => $metric['queue'],
            ]);
            $lines[] = sprintf('queue_jobs_processed_total%s %d', $labels, $metric['processed']);
        }

        $cache = $snapshot['cache'];
        $lines[] = '# HELP cache_hits_total Cache hits recorded by the application.';
        $lines[] = '# TYPE cache_hits_total counter';
        $lines[] = sprintf('cache_hits_total %d', $cache['hits']);

        $lines[] = '# HELP cache_misses_total Cache misses recorded by the application.';
        $lines[] = '# TYPE cache_misses_total counter';
        $lines[] = sprintf('cache_misses_total %d', $cache['misses']);

        $lines[] = '# HELP cache_hit_ratio The overall cache hit ratio (0-1).';
        $lines[] = '# TYPE cache_hit_ratio gauge';
        $lines[] = sprintf('cache_hit_ratio %.5f', $cache['hit_rate']);

        $lines[] = '# HELP cache_store_hit_ratio Cache hit ratio per store (0-1).';
        $lines[] = '# TYPE cache_store_hit_ratio gauge';
        $lines[] = '# HELP cache_store_hits_total Cache hits per store.';
        $lines[] = '# TYPE cache_store_hits_total counter';
        $lines[] = '# HELP cache_store_misses_total Cache misses per store.';
        $lines[] = '# TYPE cache_store_misses_total counter';
        foreach ($cache['stores'] as $storeMetric) {
            $labels = $this->formatLabels(['store' => $storeMetric['store']]);
            $lines[] = sprintf('cache_store_hit_ratio%s %.5f', $labels, $storeMetric['hit_rate']);
            $lines[] = sprintf('cache_store_hits_total%s %d', $labels, $storeMetric['hits']);
            $lines[] = sprintf('cache_store_misses_total%s %d', $labels, $storeMetric['misses']);
        }

        return implode("\n", $lines)."\n";
    }

    /**
     * @return array<int, array{connection: string, queue: string, size: int}>
     */
    private function gatherQueueDepth(): array
    {
        $metrics = [];
        $connections = collect(config('queue.connections', []));

        $connections->each(function (array $config, string $name) use (&$metrics): void {
            $queueName = (string) ($config['queue'] ?? 'default');

            try {
                $size = $this->queueManager->connection($name)->size($queueName);
            } catch (\Throwable $exception) {
                Log::debug('Queue size lookup failed', [
                    'connection' => $name,
                    'queue' => $queueName,
                    'exception' => $exception::class,
                ]);

                return;
            }

            $metrics[] = [
                'connection' => $name,
                'queue' => $queueName,
                'size' => (int) $size,
            ];
        });

        return $metrics;
    }

    private function countFailedJobs(): int
    {
        $connectionName = config('queue.failed.database', config('database.default'));
        $table = config('queue.failed.table', 'failed_jobs');

        try {
            return (int) $this->connectionResolver->connection($connectionName)->table($table)->count();
        } catch (QueryException $exception) {
            Log::debug('Failed jobs table unavailable', [
                'connection' => $connectionName,
                'table' => $table,
                'exception' => $exception::class,
            ]);

            return 0;
        }
    }

    /**
     * @param  array<string, string|int>  $labels
     */
    private function formatLabels(array $labels): string
    {
        if ($labels === []) {
            return '';
        }

        $parts = [];
        foreach ($labels as $key => $value) {
            $parts[] = sprintf('%s="%s"', $this->sanitize((string) $key), $this->sanitize((string) $value));
        }

        return '{'.implode(',', $parts).'}';
    }

    private function sanitize(string $value): string
    {
        return str_replace(['\\', '"', "\n"], ['\\\\', '\"', ''], $value);
    }
}
