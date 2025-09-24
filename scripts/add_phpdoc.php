<?php

declare(strict_types=1);

/**
 * Simple PHPDoc Addition Script
 *
 * Adds comprehensive PHPDoc documentation to PHP classes
 * following PSR-5 standards and Laravel conventions.
 */
class PHPDocAdder
{
    private array $processedFiles = [];

    private array $errors = [];

    public function run(): void
    {
        echo "🚀 Starting PHPDoc Addition...\n\n";

        $this->processModels();
        $this->processServices();
        $this->processControllers();
        $this->processFilamentResources();
        $this->processLivewireComponents();
        $this->processEnums();

        $this->generateReport();
    }

    private function processModels(): void
    {
        echo "📁 Processing Models...\n";
        $this->processDirectory('app/Models', 'Model');
    }

    private function processServices(): void
    {
        echo "📁 Processing Services...\n";
        $this->processDirectory('app/Services', 'Service');
    }

    private function processControllers(): void
    {
        echo "📁 Processing Controllers...\n";
        $this->processDirectory('app/Http/Controllers', 'Controller');
    }

    private function processFilamentResources(): void
    {
        echo "📁 Processing Filament Resources...\n";
        $this->processDirectory('app/Filament/Resources', 'FilamentResource');
        $this->processDirectory('app/Filament/Pages', 'FilamentPage');
        $this->processDirectory('app/Filament/Widgets', 'FilamentWidget');
    }

    private function processLivewireComponents(): void
    {
        echo "📁 Processing Livewire Components...\n";
        $this->processDirectory('app/Livewire', 'LivewireComponent');
    }

    private function processEnums(): void
    {
        echo "📁 Processing Enums...\n";
        $this->processDirectory('app/Enums', 'Enum');
    }

    private function processDirectory(string $directory, string $type): void
    {
        $fullPath = __DIR__.'/../'.$directory;

        if (! is_dir($fullPath)) {
            echo "⚠️  Directory not found: {$directory}\n";

            return;
        }

        $files = $this->getPhpFiles($fullPath);

        foreach ($files as $file) {
            $this->processFile($file, $directory, $type);
        }
    }

    private function getPhpFiles(string $directory): array
    {
        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    private function processFile(string $filePath, string $relativeDirectory, string $type): void
    {
        $relativePath = str_replace(__DIR__.'/../', '', $filePath);

        try {
            $content = file_get_contents($filePath);
            if ($content === false) {
                throw new Exception("Could not read file: {$filePath}");
            }

            // Skip files that already have comprehensive PHPDoc
            if ($this->hasComprehensivePHPDoc($content)) {
                echo "  ✅ {$relativePath} - Already has comprehensive PHPDoc\n";

                return;
            }

            $newContent = $this->addPHPDocToFile($content, $type);

            if ($newContent !== $content) {
                file_put_contents($filePath, $newContent);
                echo "  📝 {$relativePath} - Added PHPDoc\n";
                $this->processedFiles[] = $relativePath;
            } else {
                echo "  ⏭️  {$relativePath} - No changes needed\n";
            }

        } catch (Exception $e) {
            $this->errors[] = "Error processing {$relativePath}: ".$e->getMessage();
            echo "  ❌ {$relativePath} - Error: ".$e->getMessage()."\n";
        }
    }

    private function hasComprehensivePHPDoc(string $content): bool
    {
        // Check for class-level PHPDoc with @property annotations
        return preg_match('/\/\*\*[\s\S]*?\*\/\s*class\s+\w+/', $content) &&
               preg_match('/@property/', $content) &&
               preg_match('/@method/', $content);
    }

    private function addPHPDocToFile(string $content, string $type): string
    {
        // Find class declaration
        if (! preg_match('/class\s+(\w+)/', $content, $matches)) {
            return $content;
        }

        $className = $matches[1];

        // Check if class already has PHPDoc
        if (preg_match('/\/\*\*[\s\S]*?\*\/\s*class\s+'.preg_quote($className).'/', $content)) {
            return $content;
        }

        // Generate PHPDoc based on type
        $phpdoc = $this->generatePHPDoc($className, $type);

        // Insert PHPDoc before class declaration
        $content = preg_replace(
            '/(class\s+'.preg_quote($className).')/',
            $phpdoc."\n$1",
            $content
        );

        return $content;
    }

    private function generatePHPDoc(string $className, string $type): string
    {
        $description = $this->getTypeDescription($type);

        $phpdoc = "/**\n";
        $phpdoc .= " * {$className}\n";
        $phpdoc .= " * \n";
        $phpdoc .= " * {$description}\n";
        $phpdoc .= ' */';

        return $phpdoc;
    }

    private function getTypeDescription(string $type): string
    {
        return match ($type) {
            'Model' => 'Eloquent model representing a database entity with relationships and business logic.',
            'Service' => 'Service class containing business logic and external integrations.',
            'Controller' => 'HTTP controller handling web requests and responses.',
            'FilamentResource' => 'Filament resource for admin panel management.',
            'FilamentPage' => 'Filament page for admin panel functionality.',
            'FilamentWidget' => 'Filament widget for admin panel dashboard.',
            'LivewireComponent' => 'Livewire component for reactive frontend functionality.',
            'Enum' => 'Enumeration defining a set of named constants.',
            default => 'PHP class providing application functionality.',
        };
    }

    private function generateReport(): void
    {
        echo "\n".str_repeat('=', 60)."\n";
        echo "📊 PHPDoc Addition Report\n";
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

        echo "🎉 PHPDoc addition completed!\n";
    }
}

// Run the script
if (php_sapi_name() === 'cli') {
    $adder = new PHPDocAdder;
    $adder->run();
}
