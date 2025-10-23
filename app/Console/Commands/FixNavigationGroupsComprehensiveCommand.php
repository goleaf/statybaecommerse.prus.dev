<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;

final class FixNavigationGroupsComprehensiveCommand extends Command
{
    /**
     * Centralised import string so duplicate "use UnitEnum;" statements are never emitted.
     */
    private const UNIT_ENUM_IMPORT = 'use UnitEnum;';

    protected $signature = 'filament:navigation-groups:fix-comprehensive';

    protected $description = 'Apply comprehensive navigation group type fixes for Filament resources.';

    public function handle(): int
    {
        $filamentResourcesPath = base_path('app/Filament/Resources/');
        $files = glob($filamentResourcesPath.'*.php') ?: [];
        $fixedFiles = [];
        $errors = [];

        foreach ($files as $file) {
            $content = file_get_contents($file) ?: '';
            $originalContent = $content;

            $patterns = [
                '/protected static \?\w+ \$navigationGroup = ([^;]+);/' => '/** @var UnitEnum|string|null */'."\n    protected static \$navigationGroup = $1;",
                '/(\s+)\/\*\* @var UnitEnum\|string\|null \*\/\s*\n(\s+)protected static \$navigationGroup = NavigationGroup::([^;]+);/' => '$1/** @var UnitEnum|string|null */'."\n$2protected static \$navigationGroup = NavigationGroup::$3;",
                '/(\s+)\/\*\* @var UnitEnum\|string\|null \*\/\s*\n(\s+)protected static \$navigationGroup = \'([^\']+)\';/' => '$1/** @var UnitEnum|string|null */'."\n$2protected static \$navigationGroup = '$3';",
            ];

            foreach ($patterns as $pattern => $replacement) {
                $content = preg_replace($pattern, $replacement, $content);
            }

            if (str_contains($content, 'protected static $navigationGroup') && ! str_contains($content, self::UNIT_ENUM_IMPORT)) {
                $content = preg_replace(
                    '/(use [^;]+;\s*\n)(class \w+ extends Resource)/',
                    '$1'.self::UNIT_ENUM_IMPORT."\n\n$2",
                    $content,
                );
            }

            if ($content !== $originalContent) {
                if (file_put_contents($file, $content) !== false) {
                    $fixedFiles[] = $file;
                    $this->info('✅ Fixed: '.$file);
                } else {
                    $errors[] = sprintf('❌ Failed to write: %s', $file);
                }
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
                $this->line('- '.$file);
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
        $this->line('Running syntax check...');

        $syntaxErrors = [];

        foreach ($fixedFiles as $file) {
            $output = [];
            $returnCode = 0;
            exec(sprintf('php -l %s 2>&1', escapeshellarg($file)), $output, $returnCode);

            if ($returnCode !== 0) {
                $syntaxErrors[] = $file.': '.implode("\n", $output);
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
        $this->info('Done!');

        return $errors === [] && $syntaxErrors === [] ? self::SUCCESS : self::FAILURE;
    }
}
