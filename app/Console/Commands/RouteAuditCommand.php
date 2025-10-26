<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Support\RouteAudit\ReportWriter;
use App\Support\RouteAudit\StaticAnalyzer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

final class RouteAuditCommand extends Command
{
    protected $signature = 'route:audit {--skip-tests : Skip running the dynamic route health tests.}';

    protected $description = 'Perform static and dynamic health checks across all application routes.';

    public function handle(): int
    {
        $this->info('Starting route audit…');

        $analyzer = App::make(StaticAnalyzer::class);
        $staticReport = $analyzer->analyze();

        $storagePath = storage_path('app/route_audit');
        File::ensureDirectoryExists($storagePath);

        File::put(
            $storagePath . '/static_report.json',
            json_encode($staticReport, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: '{}'
        );

        $this->line(sprintf(
            'Static analysis complete: %d routes, %d errors, %d warnings.',
            $staticReport['routeCount'] ?? 0,
            $staticReport['errors'] ?? 0,
            $staticReport['warnings'] ?? 0
        ));

        $dynamicReport = [
            'generatedAt' => now()->toIso8601String(),
            'routes'      => [],
        ];

        $dynamicPath = $storagePath . '/dynamic_results.json';
        File::delete($dynamicPath);

        if (! $this->option('skip-tests')) {
            $this->line('Running dynamic route health tests with Pest…');

            $process = new Process(
                [
                    PHP_BINARY,
                    'vendor/bin/pest',
                    '--testsuite=Feature',
                    '--filter=RouteHealthTest',
                ],
                base_path(),
                [
                    'APP_ENV'          => 'testing',
                    'CACHE_DRIVER'     => 'array',
                    'SESSION_DRIVER'   => 'array',
                    'QUEUE_CONNECTION' => 'sync',
                ]
            );

            $process->setTimeout(900);
            $process->run(function ($type, $buffer): void {
                $this->output->write($buffer);
            });

            if (File::exists($dynamicPath)) {
                $contents = File::get($dynamicPath);
                $decoded = json_decode($contents, true);
                if (is_array($decoded)) {
                    $dynamicReport = $decoded;
                }
            } else {
                $this->warn('Dynamic results file was not produced; marking dynamic status as unavailable.');
            }
        } else {
            $this->warn('Skipping dynamic tests as requested.');
        }

        $reportWriter = App::make(ReportWriter::class);
        $reportWriter->write(
            $staticReport,
            $dynamicReport,
            $storagePath . '/route_audit.json',
            base_path('route_audit.md')
        );

        $this->info('Route audit artefacts:');
        $this->line(' - ' . $storagePath . '/route_audit.json');
        $this->line(' - ' . base_path('route_audit.md'));

        if (isset($process) && ! $process->isSuccessful() && ! $this->option('skip-tests')) {
            $this->error('Dynamic route tests reported failures. See output above for details.');

            return self::FAILURE;
        }

        $this->info('Route audit complete.');

        return self::SUCCESS;
    }
}
