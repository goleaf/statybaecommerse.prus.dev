<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;

class PHPDocUpgradeCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'docs:upgrade 
                            {--directory= : Specific directory to upgrade}
                            {--dry-run : Show what would be changed without making changes}';

    /**
     * The console command description.
     */
    protected $description = 'Upgrade PHPDoc comments to modern standards';

    private array $processedFiles = [];
    private array $errors = [];
    private Standard $printer;
    private int $totalFiles = 0;
    private int $upgradedFiles = 0;
    private bool $dryRun = false;

    public function __construct()
    {
        parent::__construct();
        $this->printer = new Standard();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->dryRun = $this->option('dry-run');
        
        $this->info('⬆️  Starting PHPDoc Upgrade...');
        $this->newLine();

        $directories = $this->getDirectoriesToProcess();

        $progressBar = $this->output->createProgressBar(count($directories));
        $progressBar->start();

        foreach ($directories as $directory => $description) {
            $this->processDirectory($directory, $description);
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->displayUpgradeReport();

        return Command::SUCCESS;
    }

    /**
     * Get directories to process.
     */
    private function getDirectoriesToProcess(): array
    {
        $specificDirectory = $this->option('directory');
        
        if ($specificDirectory) {
            $fullPath = base_path($specificDirectory);
            if (!File::isDirectory($fullPath)) {
                $this->error("❌ Directory not found: {$specificDirectory}");
                return [];
            }
            
            return [$specificDirectory => "Custom Directory: {$specificDirectory}"];
        }

        return [
            'app/Models' => 'Eloquent Models',
            'app/Http/Controllers' => 'HTTP Controllers',
            'app/Services' => 'Service Classes',
            'app/Filament/Resources' => 'Filament Resources',
            'app/Filament/Pages' => 'Filament Pages',
            'app/Filament/Widgets' => 'Filament Widgets',
            'app/Livewire' => 'Livewire Components',
            'app/Enums' => 'Enumerations',
            'app/Traits' => 'Traits',
            'app/Jobs' => 'Queue Jobs',
            'app/Events' => 'Events',
            'app/Listeners' => 'Event Listeners',
            'app/Notifications' => 'Notifications',
            'app/Mail' => 'Mailable Classes',
            'app/Policies' => 'Authorization Policies',
            'app/Observers' => 'Model Observers',
            'app/Actions' => 'Action Classes',
            'app/Data' => 'Data Transfer Objects',
            'app/Collections' => 'Custom Collections',
            'app/Contracts' => 'Interface Contracts',
            'app/Exceptions' => 'Custom Exceptions',
            'app/Validators' => 'Validation Classes',
        ];
    }

    /**
     * Process all PHP files in a directory.
     */
    private function processDirectory(string $directory, string $description): void
    {
        $fullPath = base_path($directory);
        
        if (!File::isDirectory($fullPath)) {
            return;
        }

        $files = $this->getPhpFiles($fullPath);
        $this->totalFiles += count($files);
        
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

    /**
     * Process a single PHP file.
     */
    private function processFile(string $filePath, string $relativeDirectory): void
    {
        $relativePath = str_replace(base_path() . '/', '', $filePath);
        
        try {
            $content = File::get($filePath);

            $parser = (new ParserFactory())->createForNewestSupportedVersion();
            $ast = $parser->parse($content);

            if ($ast === null) {
                throw new \Exception("Could not parse file: {$filePath}");
            }

            $traverser = new NodeTraverser();
            $visitor = new PHPDocUpgradeVisitor($relativeDirectory);
            $traverser->addVisitor($visitor);
            $modifiedAst = $traverser->traverse($ast);

            $newContent = $this->printer->prettyPrintFile($modifiedAst);
            
            // Only write if content changed
            if ($newContent !== $content) {
                if (!$this->dryRun) {
                    File::put($filePath, $newContent);
                }
                
                $this->processedFiles[] = $relativePath;
                $this->upgradedFiles++;
            }

        } catch (\Exception $e) {
            $this->errors[] = "Error processing {$relativePath}: " . $e->getMessage();
        }
    }

    /**
     * Display upgrade report.
     */
    private function displayUpgradeReport(): void
    {
        $this->info('📊 PHPDoc Upgrade Report');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        
        if ($this->dryRun) {
            $this->warn('🔍 DRY RUN MODE - No files were actually modified');
            $this->newLine();
        }
        
        $this->line("📈 Statistics:");
        $this->line("  • Total Files Scanned: {$this->totalFiles}");
        $this->line("  • Files " . ($this->dryRun ? 'Would Be Upgraded' : 'Upgraded') . ": {$this->upgradedFiles}");
        $this->line("  • Files Already Current: " . ($this->totalFiles - $this->upgradedFiles - count($this->errors)));
        $this->line("  • Errors: " . count($this->errors));

        if (!empty($this->processedFiles)) {
            $this->newLine();
            $this->info('✅ ' . ($this->dryRun ? 'Files that would be upgraded' : 'Upgraded Files') . ':');
            foreach ($this->processedFiles as $file) {
                $this->line("  - {$file}");
            }
        }

        if (!empty($this->errors)) {
            $this->newLine();
            $this->error('❌ Errors:');
            foreach ($this->errors as $error) {
                $this->line("  - {$error}");
            }
        }

        $successRate = $this->totalFiles > 0 ? 
            round((($this->totalFiles - count($this->errors)) / $this->totalFiles) * 100, 2) : 100;

        $this->newLine();
        $this->line("🎯 Success Rate: {$successRate}%");
        
        if ($this->dryRun && $this->upgradedFiles > 0) {
            $this->newLine();
            $this->info('💡 To apply these changes, run the command without --dry-run');
        }
    }
}

/**
 * PHPDoc Upgrade Visitor for AST traversal.
 */
class PHPDocUpgradeVisitor extends NodeVisitorAbstract
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
            $this->upgradeClass($node);
        } elseif ($node instanceof Node\Stmt\Interface_) {
            $this->upgradeInterface($node);
        } elseif ($node instanceof Node\Stmt\Trait_) {
            $this->upgradeTrait($node);
        } elseif ($node instanceof Node\Stmt\Enum_) {
            $this->upgradeEnum($node);
        }

        return null;
    }

    private function upgradeClass(Node\Stmt\Class_ $node): void
    {
        $className = $node->name->name ?? 'Unknown';
        
        // Analyze class structure
        $this->classInfo = [
            'name' => $className,
            'type' => 'class',
            'properties' => [],
            'methods' => [],
            'relationships' => [],
            'extends' => $node->extends ? $node->extends->toString() : null,
            'implements' => array_map(fn($impl) => $impl->toString(), $node->implements),
        ];

        foreach ($node->stmts as $stmt) {
            if ($stmt instanceof Node\Stmt\Property) {
                $this->analyzeProperty($stmt);
            } elseif ($stmt instanceof Node\Stmt\ClassMethod) {
                $this->analyzeMethod($stmt);
            }
        }

        // Upgrade class PHPDoc
        $this->upgradeClassPHPDoc($node);

        // Upgrade method PHPDocs
        foreach ($node->stmts as $stmt) {
            if ($stmt instanceof Node\Stmt\ClassMethod) {
                $this->upgradeMethodPHPDoc($stmt);
            }
        }
    }

    private function upgradeInterface(Node\Stmt\Interface_ $node): void
    {
        $this->upgradeInterfacePHPDoc($node);
    }

    private function upgradeTrait(Node\Stmt\Trait_ $node): void
    {
        $this->upgradeTraitPHPDoc($node);
    }

    private function upgradeEnum(Node\Stmt\Enum_ $node): void
    {
        $this->upgradeEnumPHPDoc($node);
    }

    private function analyzeProperty(Node\Stmt\Property $property): void
    {
        foreach ($property->props as $prop) {
            $this->classInfo['properties'][] = [
                'name' => $prop->name->name,
                'type' => $this->getPropertyType($property),
                'visibility' => $this->getVisibility($property),
                'static' => $property->isStatic(),
            ];
        }
    }

    private function analyzeMethod(Node\Stmt\ClassMethod $method): void
    {
        $this->classInfo['methods'][] = [
            'name' => $method->name->name,
            'visibility' => $this->getVisibility($method),
            'static' => $method->isStatic(),
            'parameters' => $this->getMethodParameters($method),
            'returnType' => $this->getReturnType($method),
        ];
    }

    private function upgradeClassPHPDoc(Node\Stmt\Class_ $node): void
    {
        $phpdoc = $this->generateUpgradedClassPHPDoc();
        $node->setDocComment(new \PhpParser\Comment\Doc($phpdoc));
    }

    private function upgradeMethodPHPDoc(Node\Stmt\ClassMethod $method): void
    {
        // Only upgrade if method doesn't have comprehensive PHPDoc
        if (!$this->hasComprehensiveMethodPHPDoc($method)) {
            $phpdoc = $this->generateMethodPHPDoc($method);
            $method->setDocComment(new \PhpParser\Comment\Doc($phpdoc));
        }
    }

    private function upgradeInterfacePHPDoc(Node\Stmt\Interface_ $node): void
    {
        $phpdoc = $this->generateInterfacePHPDoc($node);
        $node->setDocComment(new \PhpParser\Comment\Doc($phpdoc));
    }

    private function upgradeTraitPHPDoc(Node\Stmt\Trait_ $node): void
    {
        $phpdoc = $this->generateTraitPHPDoc($node);
        $node->setDocComment(new \PhpParser\Comment\Doc($phpdoc));
    }

    private function upgradeEnumPHPDoc(Node\Stmt\Enum_ $node): void
    {
        $phpdoc = $this->generateEnumPHPDoc($node);
        $node->setDocComment(new \PhpParser\Comment\Doc($phpdoc));
    }

    private function generateUpgradedClassPHPDoc(): string
    {
        $className = $this->classInfo['name'];
        $description = $this->getUpgradedClassDescription();
        
        $phpdoc = "/**\n";
        $phpdoc .= " * {$className}\n";
        $phpdoc .= " * \n";
        $phpdoc .= " * {$description}\n";
        $phpdoc .= " * \n";

        // Add property documentation
        foreach ($this->classInfo['properties'] as $property) {
            $type = $property['type'] ?? 'mixed';
            $name = $property['name'];
            $phpdoc .= " * @property {$type} \${$name}\n";
        }

        // Add method documentation for Laravel/Filament specific methods
        if (str_contains($this->directory, 'Models')) {
            $phpdoc .= " * @method static \\Illuminate\\Database\\Eloquent\\Builder|{$className} newModelQuery()\n";
            $phpdoc .= " * @method static \\Illuminate\\Database\\Eloquent\\Builder|{$className} newQuery()\n";
            $phpdoc .= " * @method static \\Illuminate\\Database\\Eloquent\\Builder|{$className} query()\n";
            $phpdoc .= " * @mixin \\Eloquent\n";
        }

        if (str_contains($this->directory, 'Filament/Resources')) {
            $phpdoc .= " * @method static \\Filament\\Forms\\Form form(\\Filament\\Forms\\Form \$form)\n";
            $phpdoc .= " * @method static \\Filament\\Tables\\Table table(\\Filament\\Tables\\Table \$table)\n";
        }

        $phpdoc .= " */";

        return $phpdoc;
    }

    private function generateMethodPHPDoc(Node\Stmt\ClassMethod $method): string
    {
        $methodName = $method->name->name;
        $description = $this->getMethodDescription($methodName);
        
        $phpdoc = "/**\n";
        $phpdoc .= " * {$description}\n";
        
        // Add parameter documentation
        foreach ($method->params as $param) {
            $type = $param->type ? $this->getTypeString($param->type) : 'mixed';
            $name = $param->var->name;
            $phpdoc .= " * @param {$type} \${$name}\n";
        }

        // Add return type documentation
        if ($method->returnType) {
            $returnType = $this->getTypeString($method->returnType);
            $phpdoc .= " * @return {$returnType}\n";
        }

        $phpdoc .= " */";

        return $phpdoc;
    }

    private function generateInterfacePHPDoc(Node\Stmt\Interface_ $node): string
    {
        $interfaceName = $node->name->name;
        
        return "/**\n * {$interfaceName}\n * \n * Interface contract defining required methods and behavior.\n */";
    }

    private function generateTraitPHPDoc(Node\Stmt\Trait_ $node): string
    {
        $traitName = $node->name->name;
        
        return "/**\n * {$traitName}\n * \n * Trait providing reusable functionality across multiple classes.\n */";
    }

    private function generateEnumPHPDoc(Node\Stmt\Enum_ $node): string
    {
        $enumName = $node->name->name;
        
        return "/**\n * {$enumName}\n * \n * Enumeration defining a set of named constants with type safety.\n */";
    }

    private function getUpgradedClassDescription(): string
    {
        $directory = $this->directory;
        $className = $this->classInfo['name'];
        
        if (str_contains($directory, 'Models')) {
            return "Eloquent model representing the {$className} entity with comprehensive relationships, scopes, and business logic for the e-commerce system.";
        } elseif (str_contains($directory, 'Controllers')) {
            return "HTTP controller handling {$className} related web requests, responses, and business logic with proper validation and error handling.";
        } elseif (str_contains($directory, 'Services')) {
            return "Service class containing {$className} business logic, external integrations, and complex operations with proper error handling and logging.";
        } elseif (str_contains($directory, 'Filament/Resources')) {
            return "Filament v4 resource for {$className} management in the admin panel with comprehensive CRUD operations, filters, and actions.";
        } elseif (str_contains($directory, 'Filament/Pages')) {
            return "Filament v4 page component for {$className} with reactive functionality and user interface management.";
        } elseif (str_contains($directory, 'Filament/Widgets')) {
            return "Filament v4 widget for {$className} dashboard display with real-time data and interactive features.";
        } elseif (str_contains($directory, 'Livewire')) {
            return "Livewire component for {$className} with reactive frontend functionality, real-time updates, and user interaction handling.";
        } elseif (str_contains($directory, 'Enums')) {
            return "Enumeration defining {$className} constants with type safety and comprehensive value validation for the application.";
        } elseif (str_contains($directory, 'Traits')) {
            return "Trait providing {$className} functionality that can be reused across multiple classes with consistent behavior.";
        } elseif (str_contains($directory, 'Jobs')) {
            return "Queue job for {$className} background processing with proper error handling, retry logic, and progress tracking.";
        } elseif (str_contains($directory, 'Events')) {
            return "Event class for {$className} application events with comprehensive data payload and listener integration.";
        } elseif (str_contains($directory, 'Listeners')) {
            return "Event listener for {$className} handling application events with proper error handling and side effect management.";
        } elseif (str_contains($directory, 'Notifications')) {
            return "Notification class for {$className} user notifications with multi-channel delivery and customizable content.";
        } elseif (str_contains($directory, 'Mail')) {
            return "Mailable class for {$className} email sending with template management and attachment support.";
        } elseif (str_contains($directory, 'Policies')) {
            return "Authorization policy for {$className} access control with comprehensive permission checking and role-based access.";
        } elseif (str_contains($directory, 'Observers')) {
            return "Model observer for {$className} Eloquent model events with automatic side effect handling and data consistency.";
        } elseif (str_contains($directory, 'Actions')) {
            return "Action class for {$className} single-purpose operations with validation, error handling, and result reporting.";
        } elseif (str_contains($directory, 'Data')) {
            return "Data transfer object for {$className} structured data handling with validation and type safety.";
        } elseif (str_contains($directory, 'Collections')) {
            return "Custom collection class for {$className} data manipulation with enhanced methods and type safety.";
        } elseif (str_contains($directory, 'Contracts')) {
            return "Interface contract for {$className} defining required methods and establishing behavioral contracts.";
        } elseif (str_contains($directory, 'Exceptions')) {
            return "Custom exception class for {$className} error handling with detailed error information and context.";
        } elseif (str_contains($directory, 'Validators')) {
            return "Validation class for {$className} data validation with comprehensive rules and custom error messages.";
        }

        return "PHP class providing {$className} functionality for the Laravel e-commerce application.";
    }

    private function getMethodDescription(string $methodName): string
    {
        // Common method descriptions
        $descriptions = [
            '__construct' => 'Initialize the class instance with required dependencies.',
            'index' => 'Display a listing of the resource with pagination and filtering.',
            'create' => 'Show the form for creating a new resource.',
            'store' => 'Store a newly created resource in storage with validation.',
            'show' => 'Display the specified resource with related data.',
            'edit' => 'Show the form for editing the specified resource.',
            'update' => 'Update the specified resource in storage with validation.',
            'destroy' => 'Remove the specified resource from storage.',
            'form' => 'Configure the Filament form schema with fields and validation.',
            'table' => 'Configure the Filament table with columns, filters, and actions.',
            'render' => 'Render the Livewire component view with current state.',
            'mount' => 'Initialize the Livewire component with parameters.',
            'boot' => 'Boot the service provider or trait functionality.',
            'handle' => 'Handle the job, event, or request processing.',
            'toArray' => 'Convert the instance to an array representation.',
            'toJson' => 'Convert the instance to a JSON representation.',
            'validate' => 'Validate the input data against defined rules.',
        ];

        return $descriptions[$methodName] ?? "Handle {$methodName} functionality with proper error handling.";
    }

    private function hasComprehensiveMethodPHPDoc(Node\Stmt\ClassMethod $method): bool
    {
        $comments = $method->getComments();
        foreach ($comments as $comment) {
            if ($comment instanceof Node\Comment\Doc) {
                $text = $comment->getText();
                return strpos($text, '/**') === 0 && 
                       (strpos($text, '@param') !== false || strpos($text, '@return') !== false);
            }
        }
        return false;
    }

    private function getPropertyType(Node\Stmt\Property $property): ?string
    {
        if ($property->type) {
            return $this->getTypeString($property->type);
        }
        return null;
    }

    private function getTypeString($type): string
    {
        if ($type instanceof Node\Name) {
            return $type->toString();
        } elseif ($type instanceof Node\Identifier) {
            return $type->name;
        } elseif ($type instanceof Node\NullableType) {
            return $this->getTypeString($type->type) . '|null';
        } elseif ($type instanceof Node\UnionType) {
            return implode('|', array_map([$this, 'getTypeString'], $type->types));
        }
        
        return 'mixed';
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
                'type' => $param->type ? $this->getTypeString($param->type) : null,
            ];
        }
        return $parameters;
    }

    private function getReturnType(Node\Stmt\ClassMethod $method): ?string
    {
        if ($method->returnType) {
            return $this->getTypeString($method->returnType);
        }
        return null;
    }
}
