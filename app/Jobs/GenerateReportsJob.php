<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\ReportGenerationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use RuntimeException;

/**
 * GenerateReportsJob
 *
 * Queue job that encapsulates report generation so CLI commands remain responsive.
 */
final class GenerateReportsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Number of job attempts before failing.
     */
    public int $tries = 3;

    /**
     * @param array<string, mixed> $filters
     */
    public function __construct(
        private readonly string $type,
        private readonly string $outputDirectory,
        private readonly string $format,
        private readonly array $filters
    ) {
        $this->onQueue('reports');
    }

    /**
     * Define the backoff (delay) for retry attempts.
     *
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
        return ['reports', 'type:' . $this->type];
    }

    /**
     * Handle the job, event, or request processing.
     */
    public function handle(ReportGenerationService $reportService): void
    {
        if (! Storage::exists($this->outputDirectory)) {
            Storage::makeDirectory($this->outputDirectory);
        }

        $generatedReports = match ($this->type) {
            'sales'    => [$this->generateSalesReport($reportService)],
            'products' => [$this->generateProductReport($reportService)],
            'users'    => [$this->generateUserReport($reportService)],
            'system'   => [$this->generateSystemReport($reportService)],
            'all'      => $this->generateAllReports($reportService),
            default    => throw new InvalidArgumentException('Unknown report type: ' . $this->type),
        };

        Log::info('Reports generated', [
            'type'    => $this->type,
            'format'  => $this->format,
            'output'  => $this->outputDirectory,
            'reports' => $generatedReports,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function generateSalesReport(ReportGenerationService $reportService): array
    {
        $data = $reportService->generateSalesReport($this->filters);
        $filename = 'sales_report_' . now()->format('Y-m-d_H-i-s') . '.' . $this->format;

        return $this->writeReport($filename, $data, fn (array $payload): string => $this->formatPayload($payload['daily_data']));
    }

    /**
     * @return array<string, mixed>
     */
    private function generateProductReport(ReportGenerationService $reportService): array
    {
        $data = $reportService->generateProductAnalyticsReport($this->filters);
        $filename = 'product_analytics_' . now()->format('Y-m-d_H-i-s') . '.' . $this->format;

        return $this->writeReport($filename, $data, fn (array $payload): string => $this->formatPayload($payload['products']));
    }

    /**
     * @return array<string, mixed>
     */
    private function generateUserReport(ReportGenerationService $reportService): array
    {
        $data = $reportService->generateUserActivityReport($this->filters);
        $filename = 'user_activity_' . now()->format('Y-m-d_H-i-s') . '.' . $this->format;

        return $this->writeReport($filename, $data, fn (array $payload): string => $this->formatPayload($payload['user_activity']));
    }

    /**
     * @return array<string, mixed>
     */
    private function generateSystemReport(ReportGenerationService $reportService): array
    {
        $data = $reportService->generateSystemReport();
        $filename = 'system_report_' . now()->format('Y-m-d_H-i-s') . '.' . $this->format;

        return $this->writeReport($filename, $data, function (array $payload): string {
            if ($this->format === 'json') {
                return json_encode($payload, JSON_PRETTY_PRINT) ?: '{}';
            }

            return $this->formatPayload($this->flattenSystemReport($payload));
        });
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function generateAllReports(ReportGenerationService $reportService): array
    {
        return [
            $this->generateSalesReport($reportService),
            $this->generateProductReport($reportService),
            $this->generateUserReport($reportService),
            $this->generateSystemReport($reportService),
        ];
    }

    /**
     * @param  callable(array<string, mixed>):string $formatter
     * @return array<string, mixed>
     */
    private function writeReport(string $filename, array $data, callable $formatter): array
    {
        $filepath = $this->outputDirectory . '/' . $filename;
        $content = $this->format === 'json'
            ? json_encode($data, JSON_PRETTY_PRINT) ?: '{}'
            : $formatter($data);

        Storage::put($filepath, $content);

        return [
            'name' => $filename,
            'path' => $filepath,
            'size' => strlen($content),
            'type' => $this->type,
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     */
    private function formatPayload(array $rows): string
    {
        if ($this->format === 'json') {
            return json_encode($rows, JSON_PRETTY_PRINT) ?: '[]';
        }

        $stream = fopen('php://temp', 'w+');
        if ($stream === false) {
            throw new RuntimeException('Unable to create temporary stream for report export.');
        }

        if ($rows !== []) {
            fputcsv($stream, array_keys($rows[0]));
            foreach ($rows as $row) {
                fputcsv($stream, $row);
            }
        }

        rewind($stream);
        $csv = stream_get_contents($stream) ?: '';
        fclose($stream);

        return $csv;
    }

    /**
     * @param  array<string, mixed>             $report
     * @return array<int, array<string, mixed>>
     */
    private function flattenSystemReport(array $report): array
    {
        $rows = [];
        foreach ($report['sections'] ?? [] as $section => $data) {
            $rows[] = ['section' => $section, 'data' => json_encode($data, JSON_PRETTY_PRINT) ?: '{}'];
        }

        return $rows;
    }
}
