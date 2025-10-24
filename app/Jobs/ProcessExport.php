<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\Export\ExportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessExport implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Number of job attempts before failing.
     */
    public int $tries = 3;

    /**
     * Define retry backoff windows (in seconds).
     *
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [60, 120, 300];
    }

    public function __construct(public readonly int $exportId) {}

    public function handle(ExportService $service): void
    {
        $service->process($this->exportId);
    }
}
