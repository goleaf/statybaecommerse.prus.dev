<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\ExportStatus;
use App\Models\Export;
use App\Services\Export\ExportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

final class ProcessExportJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public Export $export;

    public function __construct(Export $export)
    {
        $this->export = $export;
        $this->onQueue('exports');
    }

    public function handle(ExportService $exportService): void
    {
        $export = $this->export->fresh();

        if (! $export instanceof Export) {
            return;
        }

        $exportService->process($export);
    }

    public function failed(Throwable $exception): void
    {
        $this->export->forceFill([
            'status' => ExportStatus::FAILED,
        ])->save();
    }
}
