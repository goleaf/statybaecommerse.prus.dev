<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\ReportGenerationService;
use App\Support\Logging\StructuredLogger;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Queue;

final class GenerateReportsCommand extends Command
{
    protected $signature = 'reports:generate
                            {--type=all : Report type (all, sales, products, users, system)}
                            {--output=storage/reports : Output directory}
                            {--format=json : Output format (json, csv)}
                            {--date-from= : Start date (Y-m-d)}
                            {--date-to= : End date (Y-m-d)}';

    protected $description = 'Queue report generation so the CLI command stays responsive.';

    public function __construct(private readonly StructuredLogger $logger)
    {
        parent::__construct();
    }

    public function handle(ReportGenerationService $reportService): int
    {
        $type = (string) $this->option('type');
        $outputDir = (string) $this->option('output');
        $format = (string) $this->option('format');
        $filters = Arr::whereNotNull([
            'date_from' => $this->option('date-from') ?: null,
            'date_to' => $this->option('date-to') ?: null,
        ]);

        $operation = $this->logger->operation('reports_generate_command', [
            'type' => $type,
            'format' => $format,
            'output_directory' => $outputDir,
        ]);

        $this->info('🚀 Starting report generation with timeout protection...');

        // Ensure output directory exists
        if (! Storage::exists($outputDir)) {
            Storage::makeDirectory($outputDir);
        }

        $filters = [];
        if ($dateFrom) {
            $filters['date_from'] = $dateFrom;
        }
        if ($dateTo) {
            $filters['date_to'] = $dateTo;
        }

        if (! in_array($type, ['sales', 'products', 'users', 'system', 'all'], true)) {
            $this->error("Unknown report type: {$type}");
            $operation->fail(new \InvalidArgumentException("Unknown report type: {$type}"), [
                'requested_type' => $type,
            ]);

            return self::FAILURE;
        }

        $startTime = microtime(true);
        $generatedReports = [];

        try {
            match ($type) {
                'sales' => $this->generateSalesReport($reportService, $filters, $outputDir, $format, $generatedReports),
                'products' => $this->generateProductReport($reportService, $filters, $outputDir, $format, $generatedReports),
                'users' => $this->generateUserReport($reportService, $filters, $outputDir, $format, $generatedReports),
                'system' => $this->generateSystemReport($reportService, $outputDir, $format, $generatedReports),
                'all' => $this->generateAllReports($reportService, $filters, $outputDir, $format, $generatedReports),
            };

            $duration = microtime(true) - $startTime;

            $this->info('✅ Report generation completed!');
            $this->info('⏱️  Total time: '.round($duration, 2).' seconds');
            $this->info('📊 Generated reports: '.count($generatedReports));

            foreach ($generatedReports as $report) {
                $this->line("  📄 {$report['name']} - {$report['size']} bytes");
            }

            $operation->finish([
                'duration_seconds' => round($duration, 2),
                'reports_generated' => count($generatedReports),
                'report_names' => array_column($generatedReports, 'name'),
            ]);

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Report generation failed: '.$e->getMessage());

            $operation->fail($e);

            return self::FAILURE;
        }

        if (! in_array($format, ['json', 'csv'], true)) {
            $this->error("Unsupported report format: {$format}");

            return self::FAILURE;
        }

        Queue::push(new GenerateReportsJob($type, $outputDir, $format, $filters));

        $this->info('✅ Report generation has been queued.');
        $this->info('📬 Monitor queue workers to track progress.');

        return self::SUCCESS;
    }
}
