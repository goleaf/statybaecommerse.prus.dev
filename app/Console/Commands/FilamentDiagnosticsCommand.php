<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Filament\Facades\Filament;
use Filament\Resources\Pages\Page;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use ReflectionException;
use ReflectionProperty;
use Symfony\Component\Process\Process;
use Throwable;

final class FilamentDiagnosticsCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'filament:diagnostics';

    /**
     * @var string
     */
    protected $description = 'Inspect Filament resources and pages for common issues.';

    public function handle(): int
    {
        $filesystem = new Filesystem;

        $modelNames = $this->gatherModelNames($filesystem);
        $resourceFiles = $this->gatherResourceFiles($filesystem);

        $this->line('');
        $this->components->twoColumnDetail('📊 Models discovered', (string) $modelNames->count());
        $this->components->twoColumnDetail('📁 Resources discovered', (string) $resourceFiles->count());
        $this->line('');

        $modelsWithResources = [];
        $modelsWithoutResources = [];
        $emptyResources = [];
        $resourcesWithSyntaxIssues = [];
        $resourcesUsingOldForm = [];
        $resourcesUsingNewSchema = [];
        $resourcesWithNavigationIssues = [];

        foreach ($modelNames as $model) {
            $resourceFile = $resourceFiles->first(fn ($file) => $file->getFilename() === $model.'Resource.php');

            $this->components->task("Analyzing model {$model}", function () use (
                $resourceFile,
                $model,
                &$modelsWithResources,
                &$modelsWithoutResources,
                &$emptyResources,
                &$resourcesWithSyntaxIssues,
                &$resourcesUsingOldForm,
                &$resourcesUsingNewSchema,
                &$resourcesWithNavigationIssues,
            ): void {
                if ($resourceFile === null) {
                    $modelsWithoutResources[] = $model;

                    return;
                }

                $modelsWithResources[] = $model;

                $content = file_get_contents($resourceFile->getPathname()) ?: '';

                if ($resourceFile->getSize() < 100) {
                    $emptyResources[] = $resourceFile->getFilename();
                }

                $this->recordFormUsage($resourceFile->getFilename(), $content, $resourcesUsingOldForm, $resourcesUsingNewSchema);
                $this->recordNavigationIssues($resourceFile->getFilename(), $content, $resourcesWithNavigationIssues);

                $process = Process::fromShellCommandline('php -l '.escapeshellarg($resourceFile->getPathname()))
                    ->setTimeout(null);
                $process->run();

                if (! $process->isSuccessful()) {
                    $resourcesWithSyntaxIssues[] = $resourceFile->getFilename().' :: '.$process->getErrorOutput().$process->getOutput();
                }
            });
        }

        $this->renderSummary(
            $modelsWithResources,
            $modelsWithoutResources,
            $emptyResources,
            $resourcesWithSyntaxIssues,
            $resourcesUsingOldForm,
            $resourcesUsingNewSchema,
            $resourcesWithNavigationIssues,
        );

        $this->line('');
        $this->info('Scanning Filament pages for resource initialisation issues...');
        $pageIssues = $this->scanFilamentPages();

        if ($pageIssues->isEmpty()) {
            $this->components->info('All Filament resource pages correctly resolve their resource classes.');
        } else {
            $this->components->error('Detected issues while resolving resource pages:');
            $pageIssues->each(fn (string $issue) => $this->line(' - '.$issue));
        }

        return self::SUCCESS;
    }

    /**
     * @return Collection<int, string>
     */
    private function gatherModelNames(Filesystem $filesystem): Collection
    {
        if (! $filesystem->isDirectory(app_path('Models'))) {
            return collect();
        }

        return collect($filesystem->files(app_path('Models')))
            ->filter(fn ($file) => $file->getExtension() === 'php')
            ->map(fn ($file) => $file->getFilenameWithoutExtension())
            ->reject(fn (string $name) => Str::contains($name, ['Translation', 'Scope']))
            ->values();
    }

    /**
     * @return Collection<int, \SplFileInfo>
     */
    private function gatherResourceFiles(Filesystem $filesystem): Collection
    {
        if (! $filesystem->isDirectory(app_path('Filament/Resources'))) {
            return collect();
        }

        return collect($filesystem->files(app_path('Filament/Resources')))
            ->filter(fn ($file) => Str::endsWith($file->getFilename(), 'Resource.php'))
            ->values();
    }

    /**
     * @param  array<int, string>  $resourcesUsingOldForm
     * @param  array<int, string>  $resourcesUsingNewSchema
     */
    private function recordFormUsage(string $fileName, string $content, array &$resourcesUsingOldForm, array &$resourcesUsingNewSchema): void
    {
        if (Str::contains($content, ['use Filament\\Forms\\Form;', 'public static function form(Form $form): Form'])) {
            $resourcesUsingOldForm[] = $fileName;

            return;
        }

        if (Str::contains($content, ['use Filament\\Schemas\\Schema;', 'public static function form(Schema $schema): Schema'])) {
            $resourcesUsingNewSchema[] = $fileName;
        }
    }

    /**
     * @param  array<int, string>  $resourcesWithNavigationIssues
     */
    private function recordNavigationIssues(string $fileName, string $content, array &$resourcesWithNavigationIssues): void
    {
        if (! Str::contains($content, 'protected static $navigationGroup')) {
            return;
        }

        if (! Str::contains($content, '/** @var \UnitEnum|string|null */')) {
            $resourcesWithNavigationIssues[] = $fileName;
        }
    }

    /**
     * @param  array<int, string>  $modelsWithResources
     * @param  array<int, string>  $modelsWithoutResources
     * @param  array<int, string>  $emptyResources
     * @param  array<int, string>  $resourcesWithSyntaxIssues
     * @param  array<int, string>  $resourcesUsingOldForm
     * @param  array<int, string>  $resourcesUsingNewSchema
     * @param  array<int, string>  $resourcesWithNavigationIssues
     */
    private function renderSummary(
        array $modelsWithResources,
        array $modelsWithoutResources,
        array $emptyResources,
        array $resourcesWithSyntaxIssues,
        array $resourcesUsingOldForm,
        array $resourcesUsingNewSchema,
        array $resourcesWithNavigationIssues,
    ): void {
        $this->line('');
        $this->info('=== Resource Summary ===');
        $this->components->twoColumnDetail('✅ Models with resources', (string) count($modelsWithResources));
        $this->components->twoColumnDetail('❌ Models without resources', (string) count($modelsWithoutResources));
        $this->components->twoColumnDetail('⚠️ Empty resources', (string) count($emptyResources));
        $this->components->twoColumnDetail('🔧 Resources with syntax issues', (string) count($resourcesWithSyntaxIssues));
        $this->components->twoColumnDetail('✅ Resources using Schema API', (string) count($resourcesUsingNewSchema));
        $this->components->twoColumnDetail('⚠️ Resources using legacy Form API', (string) count($resourcesUsingOldForm));
        $this->components->twoColumnDetail('🔧 Navigation group docblock issues', (string) count($resourcesWithNavigationIssues));

        $this->listIssues('Models missing resources', $modelsWithoutResources);
        $this->listIssues('Empty resource files', $emptyResources);
        $this->listIssues('Resources with syntax issues', $resourcesWithSyntaxIssues);
        $this->listIssues('Resources using legacy Form API', $resourcesUsingOldForm);
        $this->listIssues('Resources missing navigation docblock', $resourcesWithNavigationIssues);
    }

    /**
     * @param  array<int, string>  $items
     */
    private function listIssues(string $label, array $items): void
    {
        if ($items === []) {
            return;
        }

        $this->line('');
        $this->warn($label.':');
        foreach ($items as $item) {
            $this->line(' - '.$item);
        }
    }

    /**
     * @return Collection<int, string>
     */
    private function scanFilamentPages(): Collection
    {
        $filesystem = new Filesystem;
        $issues = collect();

        if (! $filesystem->isDirectory(app_path('Filament/Resources'))) {
            return $issues;
        }

        foreach (Filament::getPanels() as $panel) {
            foreach ($panel->getResources() as $resourceClass) {
                foreach ($resourceClass::getPages() as $pageClass) {
                    if (! is_subclass_of($pageClass, Page::class)) {
                        continue;
                    }

                    try {
                        $reflection = new ReflectionProperty($pageClass, 'resource');
                        if ($reflection->isStatic() && ! $reflection->isInitialized()) {
                            $issues->push($pageClass.' has an uninitialised static $resource property.');

                            continue;
                        }
                    } catch (ReflectionException $e) {
                        $issues->push($pageClass.' does not define a static $resource property: '.$e->getMessage());

                        continue;
                    }

                    try {
                        $pageClass::getResource();
                    } catch (Throwable $e) {
                        $issues->push($pageClass.'::getResource() failed: '.$e->getMessage());
                    }
                }
            }
        }

        return $issues;
    }
}
