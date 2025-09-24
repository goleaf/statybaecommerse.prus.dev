<?php

declare(strict_types=1);

/**
 * Fix Filament v4 Navigation Group Type Issues
 *
 * This script fixes the navigationGroup type compatibility issues
 * in Filament v4 by adding proper UnitEnum imports and docblocks.
 */
require_once __DIR__.'/../vendor/autoload.php';

use Illuminate\Support\Facades\File;

class FilamentNavigationGroupFixer
{
    private array $processedFiles = [];

    private array $errors = [];

    public function run(): void
    {
        echo "🔧 Fixing Filament v4 Navigation Group Type Issues...\n\n";

        $directories = [
            'app/Filament/Resources',
            'app/Filament/Pages',
            'app/Filament/Widgets',
        ];

        foreach ($directories as $directory) {
            $this->processDirectory($directory);
        }

        $this->generateReport();
    }

    private function processDirectory(string $directory): void
    {
        $fullPath = __DIR__.'/../'.$directory;

        if (! is_dir($fullPath)) {
            echo "⚠️  Directory not found: {$directory}\n";

            return;
        }

        echo "📁 Processing directory: {$directory}\n";

        $files = $this->getPhpFiles($fullPath);

        foreach ($files as $file) {
            $this->processFile($file, $directory);
        }
    }

    private function getPhpFiles(string $directory): array
    {
        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    private function processFile(string $filePath, string $relativeDirectory): void
    {
        $relativePath = str_replace(__DIR__.'/../', '', $filePath);

        try {
            $content = file_get_contents($filePath);
            if ($content === false) {
                throw new \Exception("Could not read file: {$filePath}");
            }

            $originalContent = $content;
            $modified = false;

            // Check if file has navigationGroup property
            if (strpos($content, '$navigationGroup') !== false) {
                // Add UnitEnum import if not present
                if (strpos($content, 'use UnitEnum;') === false) {
                    $content = $this->addUnitEnumImport($content);
                    $modified = true;
                }

                // Fix navigationGroup type declaration
                $content = $this->fixNavigationGroupType($content);
                if ($content !== $originalContent) {
                    $modified = true;
                }
            }

            // Only write if content changed
            if ($modified) {
                file_put_contents($filePath, $content);
                echo "  ✅ {$relativePath} - Fixed navigation group types\n";
                $this->processedFiles[] = $relativePath;
            } else {
                echo "  ⏭️  {$relativePath} - No changes needed\n";
            }
        } catch (\Exception $e) {
            $this->errors[] = "Error processing {$relativePath}: ".$e->getMessage();
            echo "  ❌ {$relativePath} - Error: ".$e->getMessage()."\n";
        }
    }

    private function addUnitEnumImport(string $content): string
    {
        // Find the last use statement and insert UnitEnum import once
        $lines = explode("\n", $content);
        $lastUseIndex = -1;

        for ($i = 0; $i < count($lines); $i++) {
            if (preg_match('/^use\s+[^;]+;$/', trim($lines[$i]))) {
                $lastUseIndex = $i;
            }
        }

        if ($lastUseIndex !== -1) {
            array_splice($lines, $lastUseIndex + 1, 0, ['use UnitEnum;']);
            $content = implode("\n", $lines);
        }

        // Deduplicate multiple occurrences of the same import
        $content = $this->dedupeUnitEnumImport($content);

        return $content;
    }

    private function fixNavigationGroupType(string $content): string
    {
        $lines = explode("\n", $content);
        $modified = false;

        $removeUnitEnumDocblockAbove = function (array &$lines, int $index): void {
            for ($i = $index - 1; $i >= max(0, $index - 3); $i--) {
                if (preg_match('/@var\s+UnitEnum\|string\|null/', $lines[$i])) {
                    array_splice($lines, $i, 1);

                    return;
                }
                if (preg_match('/^\s*\/\*\*\s*$/', $lines[$i])) {
                    // remove the start of a simple preceding docblock if immediately above
                    array_splice($lines, $i, min(3, $index - $i));

                    return;
                }
            }
        };

        for ($i = 0; $i < count($lines); $i++) {
            $line = $lines[$i];

            // 1) Already typed (possibly with leading backslash) => normalize
            if (preg_match('/^(\s*)protected\s+static\s+\?UnitEnum\|string\|null\s+\$navigationGroup\s*=\s*([^;]+);/u', $line, $m)) {
                $indent = $m[1];
                $value = $m[2];
                $removeUnitEnumDocblockAbove($lines, $i);
                $lines[$i] = $indent.'protected static UnitEnum|string|null $navigationGroup = '.$value.';';
                $modified = true;

                continue;
            }

            // 2) Wrong types (?string or NavigationGroup) => convert to typed union
            if (preg_match('/^(\s*)protected\s+static\s+(\?string|NavigationGroup)\s+\$navigationGroup\s*=\s*([^;]+);/u', $line, $m)) {
                $indent = $m[1];
                $value = $m[3];
                $removeUnitEnumDocblockAbove($lines, $i);
                $lines[$i] = $indent.'protected static UnitEnum|string|null $navigationGroup = '.$value.';';
                $modified = true;

                continue;
            }

            // 3) Untyped property => convert to typed and remove any preceding docblock
            if (preg_match('/^(\s*)protected\s+static\s+\$navigationGroup\s*=\s*([^;]+);/u', $line, $m)) {
                $indent = $m[1];
                $value = $m[2];
                $removeUnitEnumDocblockAbove($lines, $i);
                $lines[$i] = $indent.'protected static UnitEnum|string|null $navigationGroup = '.$value.';';
                $modified = true;

                continue;
            }
        }

        $newContent = implode("\n", $lines);

        // Deduplicate multiple UnitEnum imports just in case
        $newContent = $this->dedupeUnitEnumImport($newContent);

        return $modified ? $newContent : $content;
    }

    private function dedupeUnitEnumImport(string $content): string
    {
        // Keep only the first occurrence of "use UnitEnum;" and remove the rest
        $lines = explode("\n", $content);
        $seen = false;
        $out = [];

        foreach ($lines as $line) {
            if (trim($line) === 'use UnitEnum;') {
                if ($seen) {
                    // Skip duplicates
                    continue;
                }
                $seen = true;
            }
            $out[] = $line;
        }

        return implode("\n", $out);
    }

    private function generateReport(): void
    {
        echo "\n".str_repeat('=', 60)."\n";
        echo "📊 Filament Navigation Group Fix Report\n";
        echo str_repeat('=', 60)."\n\n";

        echo '✅ Files Processed: '.count($this->processedFiles)."\n";
        echo '❌ Errors: '.count($this->errors)."\n\n";

        if (! empty($this->processedFiles)) {
            echo "📝 Modified Files:\n";
            foreach ($this->processedFiles as $file) {
                echo "  - {$file}\n";
            }
            echo "\n";
        }

        if (! empty($this->errors)) {
            echo "❌ Errors:\n";
            foreach ($this->errors as $error) {
                echo "  - {$error}\n";
            }
            echo "\n";
        }

        echo "🎉 Filament navigation group fix completed!\n";
    }
}

// Run the fixer
if (php_sapi_name() === 'cli') {
    $fixer = new FilamentNavigationGroupFixer;
    $fixer->run();
}
