<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\ReportGenerationService;
use App\Support\Logging\StructuredLogger;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

final class GenerateReportsCommand extends Command
{
    protected $signature = 'reports:generate
                            {--type=all : Report type (all, sales, products, users, system)}
                            {--output=storage/reports : Output directory}
                            {--format=json : Output format (json, csv)}
                            {--date-from= : Start date (Y-m-d)}
                            {--date-to= : End date (Y-m-d)}';

    protected $description = 'Generate various reports with timeout protection using LazyCollections takeUntilTimeout';

    public function __construct(private readonly StructuredLogger $logger)
    {
        parent::__construct();
    }

    public function handle(ReportGenerationService $reportService): int
    {
        $type = $this->option('type');
        $outputDir = $this->option('output');
        $format = $this->option('format');
        $dateFrom = $this->option('date-from');
        $dateTo = $this->option('date-to');

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
    }

    private function generateSalesReport(
        ReportGenerationService $reportService,
        array $filters,
        string $outputDir,
        string $format,
        array &$generatedReports
    ): void {
        $this->info('📈 Generating sales report...');

        $data = $reportService->generateSalesReport($filters);
        $filename = 'sales_report_'.now()->format('Y-m-d_H-i-s').'.'.$format;
        $filepath = $outputDir.'/'.$filename;

        $content = $format === 'json'
            ? json_encode($data, JSON_PRETTY_PRINT)
            : $this->arrayToCsv($data['daily_data']);

        Storage::put($filepath, $content);

        $generatedReports[] = [
            'name' => $filename,
            'size' => strlen($content),
            'type' => 'sales',
        ];

        $this->info("✅ Sales report generated: {$filename}");
    }

    private function generateProductReport(
        ReportGenerationService $reportService,
        array $filters,
        string $outputDir,
        string $format,
        array &$generatedReports
    ): void {
        $this->info('📦 Generating product analytics report...');

        $data = $reportService->generateProductAnalyticsReport($filters);
        $filename = 'product_analytics_'.now()->format('Y-m-d_H-i-s').'.'.$format;
        $filepath = $outputDir.'/'.$filename;

        $content = $format === 'json'
            ? json_encode($data, JSON_PRETTY_PRINT)
            : $this->arrayToCsv($data['products']);

        Storage::put($filepath, $content);

        $generatedReports[] = [
            'name' => $filename,
            'size' => strlen($content),
            'type' => 'products',
        ];

        $this->info("✅ Product analytics report generated: {$filename}");
    }

    private function generateUserReport(
        ReportGenerationService $reportService,
        array $filters,
        string $outputDir,
        string $format,
        array &$generatedReports
    ): void {
        $this->info('👥 Generating user activity report...');

        $data = $reportService->generateUserActivityReport($filters);
        $filename = 'user_activity_'.now()->format('Y-m-d_H-i-s').'.'.$format;
        $filepath = $outputDir.'/'.$filename;

        $content = $format === 'json'
            ? json_encode($data, JSON_PRETTY_PRINT)
            : $this->arrayToCsv($data['user_activity']);

        Storage::put($filepath, $content);

        $generatedReports[] = [
            'name' => $filename,
            'size' => strlen($content),
            'type' => 'users',
        ];

        $this->info("✅ User activity report generated: {$filename}");
    }

    private function generateSystemReport(
        ReportGenerationService $reportService,
        string $outputDir,
        string $format,
        array &$generatedReports
    ): void {
        $this->info('🔧 Generating system report...');

        $data = $reportService->generateSystemReport();
        $filename = 'system_report_'.now()->format('Y-m-d_H-i-s').'.'.$format;
        $filepath = $outputDir.'/'.$filename;

        $content = $format === 'json'
            ? json_encode($data, JSON_PRETTY_PRINT)
            : $this->arrayToCsv($this->flattenSystemReport($data));

        Storage::put($filepath, $content);

        $generatedReports[] = [
            'name' => $filename,
            'size' => strlen($content),
            'type' => 'system',
        ];

        $this->info("✅ System report generated: {$filename}");
    }

    private function generateAllReports(
        ReportGenerationService $reportService,
        array $filters,
        string $outputDir,
        string $format,
        array &$generatedReports
    ): void {
        $this->generateSalesReport($reportService, $filters, $outputDir, $format, $generatedReports);
        $this->generateProductReport($reportService, $filters, $outputDir, $format, $generatedReports);
        $this->generateUserReport($reportService, $filters, $outputDir, $format, $generatedReports);
        $this->generateSystemReport($reportService, $outputDir, $format, $generatedReports);
    }

    private function arrayToCsv(array $data): string
    {
        if (empty($data)) {
            return '';
        }

        $output = fopen('php://temp', 'r+');

        // Add headers
        fputcsv($output, array_keys($data[0]));

        // Add data rows
        foreach ($data as $row) {
            fputcsv($output, $row);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    private function flattenSystemReport(array $data): array
    {
        $flattened = [];

        foreach ($data['sections'] as $sectionName => $sectionData) {
            if (is_array($sectionData)) {
                foreach ($sectionData as $key => $value) {
                    $flattened[] = [
                        'section' => $sectionName,
                        'key' => $key,
                        'value' => is_array($value) ? json_encode($value) : $value,
                    ];
                }
            }
        }

        return $flattened;
    }
}
