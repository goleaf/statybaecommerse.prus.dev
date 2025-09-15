<?php

declare(strict_types=1);

/**
 * Fix Filament v4 Navigation Group Type Issues
 * 
 * This script fixes the navigationGroup type compatibility issues
 * in Filament v4 by adding proper UnitEnum imports and docblocks.
 */

require_once __DIR__ . '/../vendor/autoload.php';

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
        $fullPath = __DIR__ . '/../' . $directory;
        
        if (!is_dir($fullPath)) {
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
        $relativePath = str_replace(__DIR__ . '/../', '', $filePath);
        
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
            $this->errors[] = "Error processing {$relativePath}: " . $e->getMessage();
            echo "  ❌ {$relativePath} - Error: " . $e->getMessage() . "\n";
        }
    }

    private function addUnitEnumImport(string $content): string
    {
        // Find the last use statement
        $lines = explode("\n", $content);
        $lastUseIndex = -1;
        
        for ($i = 0; $i < count($lines); $i++) {
            if (preg_match('/^use\s+[^;]+;$/', trim($lines[$i]))) {
                $lastUseIndex = $i;
            }
        }

        if ($lastUseIndex !== -1) {
            // Insert UnitEnum import after the last use statement
            array_splice($lines, $lastUseIndex + 1, 0, ['use UnitEnum;']);
            return implode("\n", $lines);
        }

        return $content;
    }

    private function fixNavigationGroupType(string $content): string
    {
        // Fix various patterns of navigationGroup declarations
        $patterns = [
            // Pattern 1: protected static NavigationGroup $navigationGroup = NavigationGroup::Something;
            '/protected\s+static\s+NavigationGroup\s+\$navigationGroup\s*=\s*([^;]+);/' => '/** @var UnitEnum|string|null */' . "\n    protected static \$navigationGroup = $1;",
            
            // Pattern 2: protected static ?string $navigationGroup = NavigationGroup::Something;
            '/protected\s+static\s+\?string\s+\$navigationGroup\s*=\s*([^;]+);/' => '/** @var UnitEnum|string|null */' . "\n    protected static \$navigationGroup = $1;",
            
            // Pattern 3: protected static $navigationGroup = NavigationGroup::Something; (without type)
            '/protected\s+static\s+\$navigationGroup\s*=\s*([^;]+);/' => '/** @var UnitEnum|string|null */' . "\n    protected static \$navigationGroup = $1;",
        ];

        foreach ($patterns as $pattern => $replacement) {
            $content = preg_replace($pattern, $replacement, $content);
        }

        return $content;
    }

    private function generateReport(): void
    {
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "📊 Filament Navigation Group Fix Report\n";
        echo str_repeat("=", 60) . "\n\n";

        echo "✅ Files Processed: " . count($this->processedFiles) . "\n";
        echo "❌ Errors: " . count($this->errors) . "\n\n";

        if (!empty($this->processedFiles)) {
            echo "📝 Modified Files:\n";
            foreach ($this->processedFiles as $file) {
                echo "  - {$file}\n";
            }
            echo "\n";
        }

        if (!empty($this->errors)) {
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
    $fixer = new FilamentNavigationGroupFixer();
    $fixer->run();
}
