<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\GenerateReportsJob;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

final class GenerateReportsCommand extends Command
{
    protected $signature = 'reports:generate'
        . ' {--type=all : Report type (all, sales, products, users, system)}'
        . ' {--output=storage/reports : Output directory}'
        . ' {--format=json : Output format (json, csv)}'
        . ' {--date-from= : Start date (Y-m-d)}'
        . ' {--date-to= : End date (Y-m-d)}';

    protected $description = 'Queue report generation so the CLI command stays responsive.';

    public function handle(): int
    {
        $type = (string) $this->option('type');
        $outputDir = (string) $this->option('output');
        $format = (string) $this->option('format');
        /** @var array<string, mixed> $filters */
        $filters = Arr::whereNotNull([
            'date_from' => $this->option('date-from') ?: null,
            'date_to'   => $this->option('date-to') ?: null,
        ]);

        if (! in_array($type, ['all', 'sales', 'products', 'users', 'system'], true)) {
            $this->error("Unknown report type: {$type}");

            $operation->fail($e);

            return self::FAILURE;
        }

        if (! in_array($format, ['json', 'csv'], true)) {
            $this->error("Unsupported report format: {$format}");

            return self::FAILURE;
        }

        // Dispatch the job through the dispatcher so the job's preferred queue is honoured.
        GenerateReportsJob::dispatch($type, $outputDir, $format, $filters);

        $this->info('✅ Report generation has been queued.');
        $this->info('📬 Monitor queue workers to track progress.');

        return self::SUCCESS;
    }
}
