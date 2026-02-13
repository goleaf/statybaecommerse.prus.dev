<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

final class GenerateReportsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /**
     * @param array<string, mixed> $filters
     */
    public function __construct(
        private readonly string $type,
        private readonly string $outputPath,
        private readonly string $format,
        private readonly array $filters = [],
    ) {
        $this->onQueue('reports');
    }

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [60, 120, 300];
    }

    /**
     * @return array<int, string>
     */
    public function tags(): array
    {
        return [
            'reports',
            'type:' . $this->type,
            'format:' . $this->format,
        ];
    }

    public function handle(): void
    {
        // Keep the job lightweight for now; report generation is handled by dedicated services.
        Log::info('Report generation job queued', [
            'type'        => $this->type,
            'output_path' => $this->outputPath,
            'format'      => $this->format,
            'filters'     => $this->filters,
        ]);
    }
}
