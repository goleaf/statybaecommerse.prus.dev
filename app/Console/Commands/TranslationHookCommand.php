<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\TranslationHookService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class TranslationHookCommand extends Command
{
    protected $signature = 'translation:hook 
                           {action : The action to perform (scan|sync|report|missing)}
                           {--path= : Specific path to scan for Blade files}
                           {--locale= : Specific locale for missing translations}
                           {--fix : Automatically fix missing translations}';

    protected $description = 'Translation hook management commands';

    public function __construct(
        private readonly TranslationHookService $translationService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $action = $this->argument('action');

        return match ($action) {
            'scan'    => $this->scanBladeFiles(),
            'sync'    => $this->syncTranslations(),
            'report'  => $this->generateReport(),
            'missing' => $this->showMissingTranslations(),
            default   => $this->showHelp(),
        };
    }

    private function scanBladeFiles(): int
    {
        $path = $this->option('path') ?? resource_path('views');
        $fix = $this->option('fix');

        $this->info("Scanning Blade files in: {$path}");

        $bladeFiles = $this->getBladeFiles($path);
        $totalMissing = 0;

        foreach ($bladeFiles as $file) {
            $relativePath = str_replace(base_path() . '/', '', $file);
            $missingKeys = $this->translationService->processBladeFile($file);

            if (! empty($missingKeys)) {
                $totalMissing += count($missingKeys);
                $this->warn('Found ' . count($missingKeys) . " missing translations in: {$relativePath}");

                foreach ($missingKeys as $key) {
                    $this->line("  - {$key}");
                }

                if ($fix) {
                    $this->info('  ✓ Auto-created missing translations');
                }
            }
        }

        if ($totalMissing === 0) {
            $this->info('✓ No missing translations found!');
        } else {
            $this->info("Found {$totalMissing} missing translation keys across " . count($bladeFiles) . ' files.');

            if (! $fix) {
                $this->comment('Run with --fix to automatically create missing translations.');
            }
        }

        return 0;
    }

    private function syncTranslations(): int
    {
        $this->info('Syncing translation formats...');

        $this->translationService->syncTranslationFormats();

        $this->info('✓ Translation formats synchronized!');

        return 0;
    }

    private function generateReport(): int
    {
        $this->info('Generating translation report...');

        $report = $this->translationService->generateTranslationReport();

        $this->table(
            ['Locale', 'Translated', 'Missing', 'Completion %'],
            collect($report['locales'])->map(function ($data, $locale) {
                return [
                    $locale,
                    $data['translated'],
                    $data['missing'],
                    $data['completion_percentage'] . '%',
                ];
            })->toArray()
        );

        $this->info("Total translation keys: {$report['total_keys']}");

        return 0;
    }

    private function showMissingTranslations(): int
    {
        $locale = $this->option('locale');

        if (! $locale) {
            $this->error('Please specify a locale with --locale option');

            return 1;
        }

        $missing = $this->translationService->getMissingTranslations($locale);

        if (empty($missing)) {
            $this->info("✓ No missing translations for locale: {$locale}");

            return 0;
        }

        $this->warn("Missing translations for locale: {$locale}");

        foreach ($missing as $key => $defaultValue) {
            $this->line("  {$key}: \"{$defaultValue}\"");
        }

        $this->info('Total missing: ' . count($missing));

        return 0;
    }

    private function showHelp(): int
    {
        $this->error('Invalid action. Available actions:');
        $this->line('  scan   - Scan Blade files for missing translations');
        $this->line('  sync   - Sync translation formats (JSON ↔ PHP)');
        $this->line('  report - Generate translation completion report');
        $this->line('  missing - Show missing translations for a locale');

        $this->line("\nExamples:");
        $this->line('  php artisan translation:hook scan --fix');
        $this->line('  php artisan translation:hook missing --locale=en');
        $this->line('  php artisan translation:hook scan --path=resources/views/admin');

        return 1;
    }

    private function getBladeFiles(string $path): array
    {
        if (! File::isDirectory($path)) {
            return File::exists($path) && str_ends_with($path, '.blade.php') ? [$path] : [];
        }

        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && str_ends_with($file->getFilename(), '.blade.php')) {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }
}
