<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\PerformanceMetrics;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class StorePerformanceMetricsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The maximum number of seconds the job can run.
     */
    public int $timeout = 30;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private readonly string $routeName,
        private readonly float $ttfb,
        private readonly int $queryCount,
        private readonly int $peakMemoryMb,
        private readonly string $environment,
        private readonly ?string $userAgent = null
    ) {
        $this->onQueue('metrics');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            PerformanceMetrics::create([
                'page_route'         => $this->routeName,
                'ttfb_p50'           => $this->ttfb,
                'ttfb_p95'           => $this->ttfb,
                'query_count'        => $this->queryCount,
                'peak_memory_mb'     => $this->peakMemoryMb,
                'environment'        => $this->environment,
                'additional_metrics' => [
                    'timestamp'  => now()->toISOString(),
                    'user_agent' => $this->userAgent ?? request()->userAgent(),
                ],
            ]);
        } catch (Throwable $e) {
            Log::warning('Failed to store performance metrics in job', [
                'route' => $this->routeName,
                'error' => $e->getMessage(),
            ]);

            // Re-throw to trigger job retry mechanism
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $exception): void
    {
        Log::error('Performance metrics job failed permanently', [
            'route'     => $this->routeName,
            'ttfb'      => $this->ttfb,
            'queries'   => $this->queryCount,
            'memory_mb' => $this->peakMemoryMb,
            'error'     => $exception->getMessage(),
        ]);
    }
}
