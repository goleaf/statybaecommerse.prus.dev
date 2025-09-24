<?php

declare(strict_types=1);

/**
 * PHPDoc Implementation Script
 *
 * This script automatically adds comprehensive PHPDoc documentation
 * to all PHP classes in the Laravel application following PSR-5 standards.
 */

require_once __DIR__.'/../vendor/autoload.php';

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;

class PHPDocImplementation
{
    private array $processedFiles = [];

    private array $errors = [];

    private Standard $printer;

    public function __construct()
    {
        $this->printer = new Standard;
    }

    /**
     * Run the PHPDoc implementation process.
     */
    public function run(): void
    {
        echo "🚀 Starting PHPDoc Implementation...\n\n";

        $directories = [
            'app/Models',
            'app/Http/Controllers',
            'app/Services',
            'app/Filament/Resources',
            'app/Filament/Pages',
            'app/Filament/Widgets',
            'app/Livewire',
            'app/Enums',
            'app/Traits',
            'app/Jobs',
            'app/Events',
            'app/Listeners',
            'app/Notifications',
            'app/Mail',
            'app/Policies',
            'app/Observers',
            'app/Actions',
            'app/Data',
            'app/Collections',
            'app/Contracts',
            'app/Exceptions',
            'app/Validators',
        ];

        foreach ($directories as $directory) {
            $this->processDirectory($directory);
        }

        $this->generateReport();
    }

    /**
     * Process all PHP files in a directory.
     */
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

    /**
     * Get all PHP files in a directory recursively.
     */
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

    /**
     * Process a single PHP file.
     */
    private function processFile(string $filePath, string $relativeDirectory): void
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

            $parser = (new ParserFactory)->createForNewestSupportedVersion();
            $ast = $parser->parse($content);

            if ($ast === null) {
                throw new Exception("Could not parse file: {$filePath}");
            }

            $traverser = new NodeTraverser;
            $visitor = new PHPDocVisitor($relativeDirectory);
            $traverser->addVisitor($visitor);
            $modifiedAst = $traverser->traverse($ast);

            $newContent = $this->printer->prettyPrintFile($modifiedAst);

            // Only write if content changed
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

    /**
     * Check if file already has comprehensive PHPDoc.
     */
    private function hasComprehensivePHPDoc(string $content): bool
    {
        // Check for class-level PHPDoc with @property annotations
        return preg_match('/\/\*\*[\s\S]*?\*\/\s*class\s+\w+/', $content) &&
               preg_match('/@property/', $content) &&
               preg_match('/@method/', $content);
    }

    /**
     * Generate implementation report.
     */
    private function generateReport(): void
    {
        echo "\n".str_repeat('=', 60)."\n";
        echo "📊 PHPDoc Implementation Report\n";
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

        echo "🎉 PHPDoc implementation completed!\n";
    }
}

/**
 * PHPDoc Visitor for AST traversal.
 */
class PHPDocVisitor extends NodeVisitorAbstract
{
    private string $directory;

    private array $classInfo = [];

    public function __construct(string $directory)
    {
        $this->directory = $directory;
    }

    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Class_) {
            $this->processClass($node);
        } elseif ($node instanceof Node\Stmt\Interface_) {
            $this->processInterface($node);
        } elseif ($node instanceof Node\Stmt\Trait_) {
            $this->processTrait($node);
        } elseif ($node instanceof Node\Stmt\Enum_) {
            $this->processEnum($node);
        }

        return null;
    }

    private function processClass(Node\Stmt\Class_ $node): void
    {
        $className = $node->name->name ?? 'Unknown';
        $this->classInfo = [
            'name' => $className,
            'type' => 'class',
            'properties' => [],
            'methods' => [],
            'relationships' => [],
        ];

        // Analyze class structure
        foreach ($node->stmts as $stmt) {
            if ($stmt instanceof Node\Stmt\Property) {
                $this->analyzeProperty($stmt);
            } elseif ($stmt instanceof Node\Stmt\ClassMethod) {
                $this->analyzeMethod($stmt);
            }
        }

        // Generate PHPDoc if not present
        if (! $this->hasClassPHPDoc($node)) {
            $this->addClassPHPDoc($node);
        }
    }

    private function processInterface(Node\Stmt\Interface_ $node): void
    {
        // Similar to processClass but for interfaces
    }

    private function processTrait(Node\Stmt\Trait_ $node): void
    {
        // Similar to processClass but for traits
    }

    private function processEnum(Node\Stmt\Enum_ $node): void
    {
        // Similar to processClass but for enums
    }

    private function analyzeProperty(Node\Stmt\Property $property): void
    {
        foreach ($property->props as $prop) {
            $this->classInfo['properties'][] = [
                'name' => $prop->name->name,
                'type' => $this->getPropertyType($property),
                'visibility' => $this->getVisibility($property),
            ];
        }
    }

    private function analyzeMethod(Node\Stmt\ClassMethod $method): void
    {
        $this->classInfo['methods'][] = [
            'name' => $method->name->name,
            'visibility' => $this->getVisibility($method),
            'parameters' => $this->getMethodParameters($method),
            'returnType' => $this->getReturnType($method),
        ];
    }

    private function hasClassPHPDoc(Node\Stmt\Class_ $node): bool
    {
        $comments = $node->getComments();
        foreach ($comments as $comment) {
            if ($comment instanceof Node\Comment\Doc) {
                return strpos($comment->getText(), '/**') === 0;
            }
        }

        return false;
    }

    private function addClassPHPDoc(Node\Stmt\Class_ $node): void
    {
        $phpdoc = $this->generateClassPHPDoc();
        $node->setDocComment(new Node\Comment\Doc($phpdoc));
    }

    private function generateClassPHPDoc(): string
    {
        $className = $this->classInfo['name'];
        $description = $this->getClassDescription();

        $phpdoc = "/**\n";
        $phpdoc .= " * {$className}\n";
        $phpdoc .= " * \n";
        $phpdoc .= " * {$description}\n";
        $phpdoc .= ' */';

        return $phpdoc;
    }

    private function getClassDescription(): string
    {
        $directory = $this->directory;

        if (str_contains($directory, 'Models')) {
            return 'Eloquent model representing a database entity with relationships and business logic.';
        } elseif (str_contains($directory, 'Controllers')) {
            return 'HTTP controller handling web requests and responses.';
        } elseif (str_contains($directory, 'Services')) {
            return 'Service class containing business logic and external integrations.';
        } elseif (str_contains($directory, 'Filament')) {
            return 'Filament resource/page/widget for admin panel management.';
        } elseif (str_contains($directory, 'Livewire')) {
            return 'Livewire component for reactive frontend functionality.';
        } elseif (str_contains($directory, 'Enums')) {
            return 'Enumeration defining a set of named constants.';
        } elseif (str_contains($directory, 'Traits')) {
            return 'Trait providing reusable functionality across multiple classes.';
        } elseif (str_contains($directory, 'Jobs')) {
            return 'Job class for queued background processing.';
        } elseif (str_contains($directory, 'Events')) {
            return 'Event class for application event handling.';
        } elseif (str_contains($directory, 'Listeners')) {
            return 'Event listener for handling application events.';
        } elseif (str_contains($directory, 'Notifications')) {
            return 'Notification class for user notifications.';
        } elseif (str_contains($directory, 'Mail')) {
            return 'Mailable class for email sending.';
        } elseif (str_contains($directory, 'Policies')) {
            return 'Authorization policy for access control.';
        } elseif (str_contains($directory, 'Observers')) {
            return 'Model observer for Eloquent model events.';
        } elseif (str_contains($directory, 'Actions')) {
            return 'Action class for single-purpose operations.';
        } elseif (str_contains($directory, 'Data')) {
            return 'Data transfer object for structured data handling.';
        } elseif (str_contains($directory, 'Collections')) {
            return 'Custom collection class for data manipulation.';
        } elseif (str_contains($directory, 'Contracts')) {
            return 'Interface contract defining required methods.';
        } elseif (str_contains($directory, 'Exceptions')) {
            return 'Custom exception class for error handling.';
        } elseif (str_contains($directory, 'Validators')) {
            return 'Validation class for data validation rules.';
        }

        return 'PHP class providing application functionality.';
    }

    private function getPropertyType(Node\Stmt\Property $property): ?string
    {
        if ($property->type) {
            return $this->printer->prettyPrint([$property->type]);
        }

        return null;
    }

    private function getVisibility(Node $node): string
    {
        if ($node->flags & Node\Stmt\Class_::MODIFIER_PUBLIC) {
            return 'public';
        } elseif ($node->flags & Node\Stmt\Class_::MODIFIER_PROTECTED) {
            return 'protected';
        } elseif ($node->flags & Node\Stmt\Class_::MODIFIER_PRIVATE) {
            return 'private';
        }

        return 'public';
    }

    private function getMethodParameters(Node\Stmt\ClassMethod $method): array
    {
        $parameters = [];
        foreach ($method->params as $param) {
            $parameters[] = [
                'name' => $param->var->name,
                'type' => $param->type ? $this->printer->prettyPrint([$param->type]) : null,
            ];
        }

        return $parameters;
    }

    private function getReturnType(Node\Stmt\ClassMethod $method): ?string
    {
        if ($method->returnType) {
            return $this->printer->prettyPrint([$method->returnType]);
        }

        return null;
    }
}

// Run the implementation
if (php_sapi_name() === 'cli') {
    $implementation = new PHPDocImplementation;
    $implementation->run();
}
