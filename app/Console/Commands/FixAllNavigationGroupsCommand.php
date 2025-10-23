<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Support\Filament\Constants\NavigationGroupConstants;
use Illuminate\Console\Command;

final class FixAllNavigationGroupsCommand extends Command
{
    /**
     * Shared "use UnitEnum;" literal to avoid emitting duplicate imports during rewrites.
     */
    private const UNIT_ENUM_IMPORT = 'use UnitEnum;';

    protected $signature = 'filament:navigation-groups:fix-all';

    protected $description = 'Apply comprehensive navigation group fixes to Filament resources in the root directory.';

    public function handle(): int
    {
        $this->components->info('=== COMPREHENSIVE NAVIGATION GROUP FIX ===');

        $filamentResourcesPath = base_path('app/Filament/Resources/');
        $files = glob($filamentResourcesPath.'*.php') ?: [];
        $fixedFiles = [];
        $errors = [];

        $this->line(sprintf('Scanning %d resource files...', count($files)));
        $this->newline();

        foreach ($files as $file) {
            $content = file_get_contents($file) ?: '';
            $originalContent = $content;

            $this->output->write('Processing: '.basename($file).'... ');

            $content = preg_replace(
                '/protected static \?\w+ \$navigationGroup = ([^;]+);/',
                '/** @var UnitEnum|string|null */'."\n    protected static \$navigationGroup = $1;",
                $content,
            );

            $content = preg_replace(
                '/(\s+)\/\*\* @var UnitEnum\|string\|null \*\/\s*\n(\s+)protected static \$navigationGroup = NavigationGroup::([^;]+);/',
                '$1/** @var UnitEnum|string|null */'."\n$2protected static \$navigationGroup = NavigationGroup::$3;",
                $content,
            );

            $content = preg_replace(
                '/(\s+)\/\*\* @var UnitEnum\|string\|null \*\/\s*\n(\s+)protected static \$navigationGroup = \'([^\']+)\';/',
                '$1/** @var UnitEnum|string|null */'."\n$2protected static \$navigationGroup = '$3';",
                $content,
            );

            // Guarantee the shared UnitEnum import is present whenever we normalize navigation groups.
            if (str_contains($content, 'protected static $navigationGroup') && ! str_contains($content, NavigationGroupConstants::UNIT_ENUM_USE)) {
                $content = preg_replace(
                    '/(use [^;]+;\s*\n)(class \w+ extends Resource)/',
                    '$1'.NavigationGroupConstants::UNIT_ENUM_USE."\n\n$2",
                    $content,
                );
            }

            $content = preg_replace(
                sprintf('/(%s\s*\n)+/', NavigationGroupConstants::unitEnumImportPattern()),
                NavigationGroupConstants::UNIT_ENUM_USE."\n",
                $content,
            );

            if ($content !== $originalContent) {
                if (file_put_contents($file, $content) !== false) {
                    $fixedFiles[] = $file;
                    $this->info('✅ FIXED');
                } else {
                    $errors[] = sprintf('❌ Failed to write: %s', $file);
                    $this->error('❌ WRITE ERROR');
                }
            } else {
                $this->info('✅ OK');
            }
        }

        $this->newline();
        $this->line('=== SUMMARY ===');
        $this->line('Files fixed: '.count($fixedFiles));
        $this->line('Errors: '.count($errors));

        if ($fixedFiles !== []) {
            $this->newline();
            $this->line('Fixed files:');

            foreach ($fixedFiles as $file) {
                $this->line('- '.basename($file));
            }
        }

        if ($errors !== []) {
            $this->newline();
            $this->line('Errors:');

            foreach ($errors as $error) {
                $this->error($error);
            }
        }

        $this->newline();
        $this->line('=== VALIDATION ===');
        $this->line('Running syntax check on all files...');

        $syntaxErrors = [];

        foreach ($files as $file) {
            $output = [];
            $returnCode = 0;
            exec(sprintf('php -l %s 2>&1', escapeshellarg($file)), $output, $returnCode);

            if ($returnCode !== 0) {
                $syntaxErrors[] = basename($file).': '.implode(' ', $output);
            }
        }

        if ($syntaxErrors === []) {
            $this->info('✅ All files have valid syntax');
        } else {
            $this->error('❌ Syntax errors found:');

            foreach ($syntaxErrors as $error) {
                $this->error('- '.$error);
            }
        }

        $this->newline();
        $this->line('=== TESTING FILAMENT COMMANDS ===');
        $output = [];
        $returnCode = 0;
        exec('php artisan list 2>&1', $output, $returnCode);

        if ($returnCode === 0) {
            $this->info('✅ Filament commands working');
        } else {
            $this->error('❌ Filament commands still have errors:');
            $this->line(implode("\n", $output));
        }

        $this->newline();
        $this->info('Done!');

        return $errors === [] && $syntaxErrors === [] && $returnCode === 0 ? self::SUCCESS : self::FAILURE;
    }
}
