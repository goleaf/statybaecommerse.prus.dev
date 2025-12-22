<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Support\Exceptions\BootErrorMetrics;
use Illuminate\Console\Command;

/**
 * Monitor boot error patterns and rate limiting effectiveness.
 */
class MonitorBootErrorsCommand extends Command
{
    protected $signature = 'monitor:boot-errors 
                           {--format=table : Output format (table, json)}
                           {--hours=24 : Number of hours to analyze}';

    protected $description = 'Monitor boot error patterns and rate limiting metrics';

    public function handle(): int
    {
        if (! config('exception-handling.performance.track_boot_errors', false)) {
            $this->error(__('exceptions_monitoring_disabled') . '. Enable it in config/exception-handling.php');

            return self::FAILURE;
        }

        $metrics = BootErrorMetrics::getMetrics();

        if (empty($metrics['error_patterns']) && $metrics['rate_limit_hits'] === 0) {
            $this->info(__('No boot errors detected in the current hour.'));

            return self::SUCCESS;
        }

        $format = $this->option('format');

        if ($format === 'json') {
            $this->line(json_encode($metrics, JSON_PRETTY_PRINT));

            return self::SUCCESS;
        }

        // Table format
        $this->info('Boot Error Monitoring Report');
        $this->info('Generated: ' . $metrics['timestamp']);
        $this->newLine();

        if ($metrics['rate_limit_hits'] > 0) {
            $this->warn("Rate limit hits: {$metrics['rate_limit_hits']}");
            $this->newLine();
        }

        if (! empty($metrics['error_patterns'])) {
            $this->info('Error Patterns:');

            $tableData = [];
            foreach ($metrics['error_patterns'] as $pattern => $count) {
                $tableData[] = [$pattern, $count];
            }

            $this->table(['Pattern', 'Count'], $tableData);
        }

        // Provide recommendations
        if ($metrics['rate_limit_hits'] > 10) {
            $this->warn('⚠️  High rate limit activity detected. Consider investigating the root cause.');
        }

        return self::SUCCESS;
    }
}
