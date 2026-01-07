<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

final class ComprehensiveAnalysisCommand extends Command
{
    protected $signature = 'filament:analyze';

    protected $description = 'Analyze models and Filament resources for coverage and common compatibility issues.';

    public function handle(): int
    {
        $this->components->info('=== COMPREHENSIVE LARAVEL + FILAMENT V4 ANALYSIS ===');

        $models = $this->collectModels();
        $resources = $this->collectResources();

        $this->line('📊 MODELS ANALYSIS');
        $this->line(sprintf('Total models found: %d', count($models)));

        [$modelsWithResources, $modelsWithoutResources, $emptyResources, $resourcesWithIssues] = $this->analyzeModels($models);

        $this->newline();
        $this->line('=== RESOURCE COMPATIBILITY ANALYSIS ===');

        [$resourcesUsingOldForm, $resourcesUsingNewSchema, $resourcesWithNavigationIssues] = $this->analyzeResources($resources);

        $this->newline();
        $this->line('=== SUMMARY ===');
        $this->line(sprintf('✅ Models with resources: %d', count($modelsWithResources)));
        $this->line(sprintf('❌ Models without resources: %d', count($modelsWithoutResources)));
        $this->line(sprintf('⚠️  Empty resources: %d', count($emptyResources)));
        $this->line(sprintf('🔧 Resources with issues: %d', count($resourcesWithIssues)));

        $this->newline();
        $this->line('=== RESOURCE COMPATIBILITY ===');
        $this->line(sprintf('✅ Resources using new Schema: %d', count($resourcesUsingNewSchema)));
        $this->line(sprintf('⚠️  Resources using old Form: %d', count($resourcesUsingOldForm)));
        $this->line(sprintf('🔧 Resources with navigation issues: %d', count($resourcesWithNavigationIssues)));

        $this->reportList('❌ MODELS WITHOUT RESOURCES', $modelsWithoutResources);
        $this->reportList('⚠️  EMPTY RESOURCES', array_map(static fn (string $model): string => $model . 'Resource.php', $emptyResources));
        $this->reportList('🔧 RESOURCES WITH ISSUES', array_map(static fn (string $model): string => $model . 'Resource.php', $resourcesWithIssues));
        $this->reportList('⚠️  RESOURCES USING OLD FORM CLASS', $resourcesUsingOldForm);
        $this->reportList('🔧 RESOURCES WITH NAVIGATION GROUP ISSUES', $resourcesWithNavigationIssues);

        $this->newline();
        $this->line('=== RECOMMENDATIONS ===');
        $this->line(sprintf('1. Fix navigation group type issues in %d resources', count($resourcesWithNavigationIssues)));
        $this->line(sprintf('2. Update %d resources from Form to Schema', count($resourcesUsingOldForm)));
        $this->line(sprintf('3. Implement %d empty resources', count($emptyResources)));
        $this->line(sprintf('4. Create %d missing resources', count($modelsWithoutResources)));
        $this->line(sprintf('5. Fix syntax errors in %d resources', count($resourcesWithIssues)));

        $this->newline();
        $this->info('Analysis complete.');

        return self::SUCCESS;
    }

    /**
     * @return array<int, string>
     */
    private function collectModels(): array
    {
        $files = glob(base_path('app/Models') . '/*.php');
        $models = [];

        foreach ($files as $file) {
            $modelName = basename($file, '.php');

            if (! str_contains($modelName, 'Translation') && ! str_contains($modelName, 'Scope')) {
                $models[] = $modelName;
            }
        }

        return $models;
    }

    /**
     * @return array<int, string>
     */
    private function collectResources(): array
    {
        $files = glob(base_path('app/Filament/Resources') . '/*Resource.php');
        $resources = [];

        foreach ($files as $file) {
            $resources[] = basename($file);
        }

        return $resources;
    }

    /**
     * @param  array<int, string>                                                                                $models
     * @return array{0: array<int, string>, 1: array<int, string>, 2: array<int, string>, 3: array<int, string>}
     */
    private function analyzeModels(array $models): array
    {
        $modelsWithResources = [];
        $modelsWithoutResources = [];
        $emptyResources = [];
        $resourcesWithIssues = [];

        foreach ($models as $model) {
            $resourceFile = base_path(sprintf('app/Filament/Resources/%sResource.php', $model));
            $this->line(sprintf('🔍 Analyzing: %s', $model));

            if (! file_exists($resourceFile)) {
                $modelsWithoutResources[] = $model;
                $this->error('  ❌ No resource found');

                continue;
            }

            $resourceSize = filesize($resourceFile) ?: 0;

            if ($resourceSize < 100) {
                $emptyResources[] = $model;
                $this->warn(sprintf('  ⚠️  Empty resource (%d bytes)', $resourceSize));
            } else {
                $modelsWithResources[] = $model;
                $this->info(sprintf('  ✅ Resource exists (%d bytes)', $resourceSize));

                $output = [];
                $returnCode = 0;
                exec(sprintf('php -l %s 2>&1', escapeshellarg($resourceFile)), $output, $returnCode);

                if ($returnCode !== 0) {
                    $resourcesWithIssues[] = $model;
                    $this->error('  ❌ Syntax error: ' . implode(' ', $output));
                }
            }
        }

        return [$modelsWithResources, $modelsWithoutResources, $emptyResources, $resourcesWithIssues];
    }

    /**
     * @param  array<int, string>                                                         $resources
     * @return array{0: array<int, string>, 1: array<int, string>, 2: array<int, string>}
     */
    private function analyzeResources(array $resources): array
    {
        $resourcesUsingOldForm = [];
        $resourcesUsingNewSchema = [];
        $resourcesWithNavigationIssues = [];

        foreach ($resources as $resourceFile) {
            $fullPath = base_path('app/Filament/Resources/' . $resourceFile);
            $content = file_get_contents($fullPath) ?: '';

            $this->line(sprintf('🔍 Analyzing resource: %s', $resourceFile));

            if (Str::contains($content, ['use Filament\\Forms\\Form;', 'public static function form(Form $form): Form'])) {
                $resourcesUsingOldForm[] = $resourceFile;
                $this->warn('  ⚠️  Using old Form class');
            } elseif (Str::contains($content, ['use Filament\\Schemas\\Schema;', 'public static function form(Schema $schema): Schema'])) {
                $resourcesUsingNewSchema[] = $resourceFile;
                $this->info('  ✅ Using new Schema class');
            }

            if (Str::contains($content, 'protected static $navigationGroup') && ! Str::contains($content, '/** @var \UnitEnum|string|null */')) {
                $resourcesWithNavigationIssues[] = $resourceFile;
                $this->warn('  ⚠️  Navigation group type issue');
            }
        }

        return [$resourcesUsingOldForm, $resourcesUsingNewSchema, $resourcesWithNavigationIssues];
    }

    /**
     * @param array<int, string> $items
     */
    private function reportList(string $title, array $items): void
    {
        if ($items === []) {
            return;
        }

        $this->newline();
        $this->line($title . ':');

        foreach ($items as $item) {
            $this->line('- ' . $item);
        }
    }
}
