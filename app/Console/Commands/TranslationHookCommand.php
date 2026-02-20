<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\TranslationHookService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

final class TranslationHookCommand extends Command
{
    protected $signature = 'translation:hook {action : scan, report, sync, process} {--path= : Path to scan} {--fix : Auto-fix missing translations} {--exclude= : Files to exclude}';

    protected $description = '';

    public function __construct()
    {
        parent::__construct();
        $this->setDescription(__('messages.translation_hook_command_description'));
    }

    public function handle(TranslationHookService $service): int
    {
        $action = $this->argument('action');
        $path = $this->option('path') ?? resource_path('views');
        $exclude = $this->option('exclude');

        return match ($action) {
            'scan'    => $this->scanAction($service, $path, $exclude),
            'report'  => $this->reportAction($service),
            'sync'    => $this->syncAction($service),
            'process' => $this->processAction($service, $path),
            default   => $this->invalidAction($action),
        };
    }

    private function scanAction(TranslationHookService $service, string $path, ?string $exclude): int
    {
        $this->info("Scanning Blade files in: {$path}");

        $files = File::allFiles($path);
        $excludeList = $exclude ? explode(',', $exclude) : [];

        foreach ($files as $file) {
            if (in_array($file->getFilename(), $excludeList)) {
                continue;
            }

            if ($file->getExtension() === 'php') {
                $missing = $service->processBladeFile($file->getPathname());
                if (! empty($missing)) {
                    $this->warn("Found missing keys in {$file->getRelativePathname()}: " . implode(', ', $missing));
                }
            }
        }

        return 0;
    }

    private function reportAction(TranslationHookService $service): int
    {
        $this->info('Translation Report');
        $report = $service->generateTranslationReport();

        $this->info("Total Keys: {$report['total_keys']}");

        foreach ($report['locales'] as $locale => $stats) {
            $this->info("Locale: {$locale}");
            $this->line("  Translated: {$stats['translated']}");
            $this->line("  Missing: {$stats['missing']}");
            $this->line("  Completion: {$stats['completion_percentage']}%");
        }

        return 0;
    }

    private function syncAction(TranslationHookService $service): int
    {
        $this->info('Syncing translations between locales...');

        // Simple sync: ensure all keys from default locale exist in others
        $report = $service->generateTranslationReport();
        $defaultLocale = config('app.locale', 'lt');

        foreach ($report['locales'] as $locale => $stats) {
            if ($locale === $defaultLocale) {
                continue;
            }

            foreach ($stats['missing_keys'] as $key) {
                $service->addTranslation($key, []);
            }
        }

        $this->info('Sync completed.');

        return 0;
    }

    private function processAction(TranslationHookService $service, string $path): int
    {
        $this->info("Processing Blade files in: {$path}");

        return $this->scanAction($service, $path, null);
    }

    private function invalidAction(string $action): int
    {
        $this->error("Invalid action: {$action}");

        return 1;
    }
}
