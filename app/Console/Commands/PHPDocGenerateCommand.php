<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;

class PHPDocGenerateCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'docs:generate 
                            {--output=docs/html : Output directory for documentation}
                            {--template=default : Template to use for documentation}';

    /**
     * The console command description.
     */
    protected $description = 'Generate HTML documentation from PHPDoc comments';

    private array $classes = [];
    private array $interfaces = [];
    private array $traits = [];
    private array $enums = [];
    private int $totalFiles = 0;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('📖 Generating PHPDoc HTML Documentation...');
        $this->newLine();

        $outputDir = $this->option('output');
        $template = $this->option('template');

        // Create output directory
        if (!File::isDirectory($outputDir)) {
            File::makeDirectory($outputDir, 0755, true);
            $this->line("📁 Created output directory: {$outputDir}");
        }

        // Process all PHP files
        $this->processPhpFiles();

        // Generate HTML documentation
        $this->generateHtmlDocumentation($outputDir, $template);

        $this->newLine();
        $this->info('🎉 Documentation generated successfully!');
        $this->line("📂 Location: {$outputDir}");
        $this->line("📊 Processed {$this->totalFiles} files");
        $this->line("📋 Generated documentation for:");
        $this->line("  • " . count($this->classes) . " classes");
        $this->line("  • " . count($this->interfaces) . " interfaces");
        $this->line("  • " . count($this->traits) . " traits");
        $this->line("  • " . count($this->enums) . " enums");

        return Command::SUCCESS;
    }

    /**
     * Process all PHP files in the application.
     */
    private function processPhpFiles(): void
    {
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

        $progressBar = $this->output->createProgressBar(count($directories));
        $progressBar->start();

        foreach ($directories as $directory) {
            $this->processDirectory($directory);
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);
    }

    /**
     * Process all PHP files in a directory.
     */
    private function processDirectory(string $directory): void
    {
        $fullPath = base_path($directory);
        
        if (!File::isDirectory($fullPath)) {
            return;
        }

        $files = $this->getPhpFiles($fullPath);
        $this->totalFiles += count($files);
        
        foreach ($files as $file) {
            $this->processFile($file);
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
    private function processFile(string $filePath): void
    {
        try {
            $content = File::get($filePath);
            $relativePath = str_replace(base_path() . '/', '', $filePath);

            $parser = (new ParserFactory())->createForNewestSupportedVersion();
            $ast = $parser->parse($content);

            if ($ast === null) {
                return;
            }

            $traverser = new NodeTraverser();
            $visitor = new DocumentationVisitor($relativePath);
            $traverser->addVisitor($visitor);
            $traverser->traverse($ast);

            // Collect the parsed information
            $this->classes = array_merge($this->classes, $visitor->getClasses());
            $this->interfaces = array_merge($this->interfaces, $visitor->getInterfaces());
            $this->traits = array_merge($this->traits, $visitor->getTraits());
            $this->enums = array_merge($this->enums, $visitor->getEnums());

        } catch (\Exception $e) {
            // Skip files that can't be parsed
        }
    }

    /**
     * Generate HTML documentation.
     */
    private function generateHtmlDocumentation(string $outputDir, string $template): void
    {
        $this->info('📝 Generating HTML documentation...');

        // Generate index page
        $this->generateIndexPage($outputDir);

        // Generate class pages
        foreach ($this->classes as $class) {
            $this->generateClassPage($outputDir, $class);
        }

        // Generate interface pages
        foreach ($this->interfaces as $interface) {
            $this->generateInterfacePage($outputDir, $interface);
        }

        // Generate trait pages
        foreach ($this->traits as $trait) {
            $this->generateTraitPage($outputDir, $trait);
        }

        // Generate enum pages
        foreach ($this->enums as $enum) {
            $this->generateEnumPage($outputDir, $enum);
        }

        // Generate CSS
        $this->generateCss($outputDir);

        // Generate JavaScript
        $this->generateJs($outputDir);
    }

    /**
     * Generate the main index page.
     */
    private function generateIndexPage(string $outputDir): void
    {
        $html = $this->getHtmlTemplate('index', [
            'title' => 'Statyba E-commerce API Documentation',
            'classes' => $this->classes,
            'interfaces' => $this->interfaces,
            'traits' => $this->traits,
            'enums' => $this->enums,
            'totalFiles' => $this->totalFiles,
        ]);

        File::put($outputDir . '/index.html', $html);
    }

    /**
     * Generate a class documentation page.
     */
    private function generateClassPage(string $outputDir, array $class): void
    {
        $filename = str_replace('\\', '_', $class['name']) . '.html';
        $html = $this->getHtmlTemplate('class', [
            'class' => $class,
            'title' => $class['name'] . ' - Class Documentation',
        ]);

        File::put($outputDir . '/' . $filename, $html);
    }

    /**
     * Generate an interface documentation page.
     */
    private function generateInterfacePage(string $outputDir, array $interface): void
    {
        $filename = str_replace('\\', '_', $interface['name']) . '.html';
        $html = $this->getHtmlTemplate('interface', [
            'interface' => $interface,
            'title' => $interface['name'] . ' - Interface Documentation',
        ]);

        File::put($outputDir . '/' . $filename, $html);
    }

    /**
     * Generate a trait documentation page.
     */
    private function generateTraitPage(string $outputDir, array $trait): void
    {
        $filename = str_replace('\\', '_', $trait['name']) . '.html';
        $html = $this->getHtmlTemplate('trait', [
            'trait' => $trait,
            'title' => $trait['name'] . ' - Trait Documentation',
        ]);

        File::put($outputDir . '/' . $filename, $html);
    }

    /**
     * Generate an enum documentation page.
     */
    private function generateEnumPage(string $outputDir, array $enum): void
    {
        $filename = str_replace('\\', '_', $enum['name']) . '.html';
        $html = $this->getHtmlTemplate('enum', [
            'enum' => $enum,
            'title' => $enum['name'] . ' - Enum Documentation',
        ]);

        File::put($outputDir . '/' . $filename, $html);
    }

    /**
     * Generate CSS file.
     */
    private function generateCss(string $outputDir): void
    {
        $css = $this->getCssTemplate();
        File::put($outputDir . '/style.css', $css);
    }

    /**
     * Generate JavaScript file.
     */
    private function generateJs(string $outputDir): void
    {
        $js = $this->getJsTemplate();
        File::put($outputDir . '/script.js', $js);
    }

    /**
     * Get HTML template.
     */
    private function getHtmlTemplate(string $template, array $data): string
    {
        $title = $data['title'] ?? 'Documentation';
        
        switch ($template) {
            case 'index':
                return $this->getIndexTemplate($data);
            case 'class':
                return $this->getClassTemplate($data);
            case 'interface':
                return $this->getInterfaceTemplate($data);
            case 'trait':
                return $this->getTraitTemplate($data);
            case 'enum':
                return $this->getEnumTemplate($data);
            default:
                return $this->getBaseTemplate($title, '');
        }
    }

    /**
     * Get index page template.
     */
    private function getIndexTemplate(array $data): string
    {
        $classesHtml = '';
        foreach ($data['classes'] as $class) {
            $classesHtml .= "<li><a href='" . str_replace('\\', '_', $class['name']) . ".html'>{$class['name']}</a> - {$class['description']}</li>";
        }

        $interfacesHtml = '';
        foreach ($data['interfaces'] as $interface) {
            $interfacesHtml .= "<li><a href='" . str_replace('\\', '_', $interface['name']) . ".html'>{$interface['name']}</a> - {$interface['description']}</li>";
        }

        $traitsHtml = '';
        foreach ($data['traits'] as $trait) {
            $traitsHtml .= "<li><a href='" . str_replace('\\', '_', $trait['name']) . ".html'>{$trait['name']}</a> - {$trait['description']}</li>";
        }

        $enumsHtml = '';
        foreach ($data['enums'] as $enum) {
            $enumsHtml .= "<li><a href='" . str_replace('\\', '_', $enum['name']) . ".html'>{$enum['name']}</a> - {$enum['description']}</li>";
        }

        $content = "
        <div class='container'>
            <h1>Statyba E-commerce API Documentation</h1>
            <p class='lead'>Complete documentation for all PHP classes in the Laravel application.</p>
            
            <div class='stats'>
                <div class='stat'>
                    <h3>{$data['totalFiles']}</h3>
                    <p>Files Processed</p>
                </div>
                <div class='stat'>
                    <h3>" . count($data['classes']) . "</h3>
                    <p>Classes</p>
                </div>
                <div class='stat'>
                    <h3>" . count($data['interfaces']) . "</h3>
                    <p>Interfaces</p>
                </div>
                <div class='stat'>
                    <h3>" . count($data['traits']) . "</h3>
                    <p>Traits</p>
                </div>
                <div class='stat'>
                    <h3>" . count($data['enums']) . "</h3>
                    <p>Enums</p>
                </div>
            </div>

            <div class='sections'>
                <section>
                    <h2>Classes</h2>
                    <ul>{$classesHtml}</ul>
                </section>
                
                <section>
                    <h2>Interfaces</h2>
                    <ul>{$interfacesHtml}</ul>
                </section>
                
                <section>
                    <h2>Traits</h2>
                    <ul>{$traitsHtml}</ul>
                </section>
                
                <section>
                    <h2>Enums</h2>
                    <ul>{$enumsHtml}</ul>
                </section>
            </div>
        </div>";

        return $this->getBaseTemplate($data['title'], $content);
    }

    /**
     * Get class page template.
     */
    private function getClassTemplate(array $data): string
    {
        $class = $data['class'];
        
        $propertiesHtml = '';
        foreach ($class['properties'] as $property) {
            $propertiesHtml .= "<tr><td>\${$property['name']}</td><td>{$property['type']}</td><td>{$property['description']}</td></tr>";
        }

        $methodsHtml = '';
        foreach ($class['methods'] as $method) {
            $methodsHtml .= "<tr><td>{$method['name']}()</td><td>{$method['returnType']}</td><td>{$method['description']}</td></tr>";
        }

        $content = "
        <div class='container'>
            <h1>{$class['name']}</h1>
            <p class='lead'>{$class['description']}</p>
            
            <div class='info'>
                <p><strong>File:</strong> {$class['file']}</p>
                <p><strong>Namespace:</strong> {$class['namespace']}</p>
            </div>

            <section>
                <h2>Properties</h2>
                <table>
                    <thead>
                        <tr><th>Name</th><th>Type</th><th>Description</th></tr>
                    </thead>
                    <tbody>{$propertiesHtml}</tbody>
                </table>
            </section>

            <section>
                <h2>Methods</h2>
                <table>
                    <thead>
                        <tr><th>Name</th><th>Return Type</th><th>Description</th></tr>
                    </thead>
                    <tbody>{$methodsHtml}</tbody>
                </table>
            </section>
        </div>";

        return $this->getBaseTemplate($data['title'], $content);
    }

    /**
     * Get interface page template.
     */
    private function getInterfaceTemplate(array $data): string
    {
        $interface = $data['interface'];
        
        $content = "
        <div class='container'>
            <h1>{$interface['name']}</h1>
            <p class='lead'>{$interface['description']}</p>
            
            <div class='info'>
                <p><strong>File:</strong> {$interface['file']}</p>
                <p><strong>Namespace:</strong> {$interface['namespace']}</p>
            </div>
        </div>";

        return $this->getBaseTemplate($data['title'], $content);
    }

    /**
     * Get trait page template.
     */
    private function getTraitTemplate(array $data): string
    {
        $trait = $data['trait'];
        
        $content = "
        <div class='container'>
            <h1>{$trait['name']}</h1>
            <p class='lead'>{$trait['description']}</p>
            
            <div class='info'>
                <p><strong>File:</strong> {$trait['file']}</p>
                <p><strong>Namespace:</strong> {$trait['namespace']}</p>
            </div>
        </div>";

        return $this->getBaseTemplate($data['title'], $content);
    }

    /**
     * Get enum page template.
     */
    private function getEnumTemplate(array $data): string
    {
        $enum = $data['enum'];
        
        $content = "
        <div class='container'>
            <h1>{$enum['name']}</h1>
            <p class='lead'>{$enum['description']}</p>
            
            <div class='info'>
                <p><strong>File:</strong> {$enum['file']}</p>
                <p><strong>Namespace:</strong> {$enum['namespace']}</p>
            </div>
        </div>";

        return $this->getBaseTemplate($data['title'], $content);
    }

    /**
     * Get base HTML template.
     */
    private function getBaseTemplate(string $title, string $content): string
    {
        return "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>{$title}</title>
    <link rel='stylesheet' href='style.css'>
</head>
<body>
    <nav class='navbar'>
        <div class='nav-container'>
            <a href='index.html' class='nav-brand'>📚 API Docs</a>
            <div class='nav-links'>
                <a href='index.html'>Home</a>
            </div>
        </div>
    </nav>
    
    <main>
        {$content}
    </main>
    
    <footer>
        <p>Generated by Laravel PHPDoc Generator</p>
    </footer>
    
    <script src='script.js'></script>
</body>
</html>";
    }

    /**
     * Get CSS template.
     */
    private function getCssTemplate(): string
    {
        return "/* PHPDoc Documentation Styles */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    line-height: 1.6;
    color: #333;
    background-color: #f8f9fa;
}

.navbar {
    background: #2c3e50;
    color: white;
    padding: 1rem 0;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.nav-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.nav-brand {
    font-size: 1.5rem;
    font-weight: bold;
    color: white;
    text-decoration: none;
}

.nav-links a {
    color: white;
    text-decoration: none;
    margin-left: 1rem;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    transition: background-color 0.3s;
}

.nav-links a:hover {
    background-color: rgba(255,255,255,0.1);
}

.container {
    max-width: 1200px;
    margin: 2rem auto;
    padding: 0 1rem;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    padding: 2rem;
}

h1 {
    color: #2c3e50;
    margin-bottom: 1rem;
    border-bottom: 3px solid #3498db;
    padding-bottom: 0.5rem;
}

h2 {
    color: #34495e;
    margin: 2rem 0 1rem 0;
    border-bottom: 2px solid #ecf0f1;
    padding-bottom: 0.5rem;
}

.lead {
    font-size: 1.1rem;
    color: #7f8c8d;
    margin-bottom: 2rem;
}

.stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1rem;
    margin: 2rem 0;
}

.stat {
    text-align: center;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.stat h3 {
    font-size: 2rem;
    color: #3498db;
    margin-bottom: 0.5rem;
}

.stat p {
    color: #6c757d;
    font-weight: 500;
}

.sections {
    display: grid;
    gap: 2rem;
}

.sections section {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.sections ul {
    list-style: none;
}

.sections li {
    padding: 0.5rem 0;
    border-bottom: 1px solid #e9ecef;
}

.sections li:last-child {
    border-bottom: none;
}

.sections a {
    color: #3498db;
    text-decoration: none;
    font-weight: 500;
}

.sections a:hover {
    text-decoration: underline;
}

.info {
    background: #e8f4f8;
    padding: 1rem;
    border-radius: 6px;
    margin: 1rem 0;
    border-left: 4px solid #3498db;
}

.info p {
    margin: 0.5rem 0;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin: 1rem 0;
}

table th,
table td {
    padding: 0.75rem;
    text-align: left;
    border-bottom: 1px solid #e9ecef;
}

table th {
    background-color: #f8f9fa;
    font-weight: 600;
    color: #495057;
}

table tr:hover {
    background-color: #f8f9fa;
}

footer {
    background: #2c3e50;
    color: white;
    text-align: center;
    padding: 2rem 0;
    margin-top: 3rem;
}

footer p {
    margin: 0;
}

@media (max-width: 768px) {
    .nav-container {
        flex-direction: column;
        gap: 1rem;
    }
    
    .nav-links {
        display: flex;
        gap: 0.5rem;
    }
    
    .container {
        margin: 1rem;
        padding: 1rem;
    }
    
    .stats {
        grid-template-columns: repeat(2, 1fr);
    }
    
    table {
        font-size: 0.9rem;
    }
    
    table th,
    table td {
        padding: 0.5rem;
    }
}";
    }

    /**
     * Get JavaScript template.
     */
    private function getJsTemplate(): string
    {
        return "// PHPDoc Documentation JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Add smooth scrolling to anchor links
    document.querySelectorAll('a[href^=\"#\"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Add search functionality
    const searchInput = document.createElement('input');
    searchInput.type = 'text';
    searchInput.placeholder = 'Search documentation...';
    searchInput.className = 'search-input';
    
    const navLinks = document.querySelector('.nav-links');
    if (navLinks) {
        navLinks.appendChild(searchInput);
    }

    // Add search styles
    const style = document.createElement('style');
    style.textContent = `
        .search-input {
            padding: 0.5rem 1rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-left: 1rem;
            background: white;
            color: #333;
        }
        
        .search-input:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
        }
    `;
    document.head.appendChild(style);

    // Simple search functionality
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const sections = document.querySelectorAll('section');
        
        sections.forEach(section => {
            const text = section.textContent.toLowerCase();
            if (text.includes(searchTerm) || searchTerm === '') {
                section.style.display = 'block';
            } else {
                section.style.display = 'none';
            }
        });
    });

    // Add copy to clipboard functionality for code blocks
    document.querySelectorAll('code').forEach(code => {
        code.addEventListener('click', function() {
            navigator.clipboard.writeText(this.textContent).then(() => {
                // Show a temporary tooltip
                const tooltip = document.createElement('div');
                tooltip.textContent = 'Copied!';
                tooltip.style.cssText = `
                    position: absolute;
                    background: #2c3e50;
                    color: white;
                    padding: 0.5rem;
                    border-radius: 4px;
                    font-size: 0.8rem;
                    z-index: 1000;
                    pointer-events: none;
                `;
                
                document.body.appendChild(tooltip);
                
                const rect = this.getBoundingClientRect();
                tooltip.style.left = rect.left + 'px';
                tooltip.style.top = (rect.top - 30) + 'px';
                
                setTimeout(() => {
                    document.body.removeChild(tooltip);
                }, 1000);
            });
        });
    });
});";
    }
}

/**
 * Documentation Visitor for AST traversal.
 */
class DocumentationVisitor extends NodeVisitorAbstract
{
    private string $file;
    private array $classes = [];
    private array $interfaces = [];
    private array $traits = [];
    private array $enums = [];

    public function __construct(string $file)
    {
        $this->file = $file;
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
        $namespace = $this->getNamespace($node);
        $description = $this->getDescription($node);
        
        $properties = [];
        $methods = [];

        foreach ($node->stmts as $stmt) {
            if ($stmt instanceof Node\Stmt\Property) {
                foreach ($stmt->props as $prop) {
                    $properties[] = [
                        'name' => $prop->name->name,
                        'type' => $this->getPropertyType($stmt),
                        'description' => 'Property description',
                    ];
                }
            } elseif ($stmt instanceof Node\Stmt\ClassMethod) {
                $methods[] = [
                    'name' => $stmt->name->name,
                    'returnType' => $this->getReturnType($stmt),
                    'description' => $this->getMethodDescription($stmt->name->name),
                ];
            }
        }

        $this->classes[] = [
            'name' => $namespace ? $namespace . '\\' . $className : $className,
            'namespace' => $namespace,
            'file' => $this->file,
            'description' => $description,
            'properties' => $properties,
            'methods' => $methods,
        ];
    }

    private function processInterface(Node\Stmt\Interface_ $node): void
    {
        $interfaceName = $node->name->name ?? 'Unknown';
        $namespace = $this->getNamespace($node);
        $description = $this->getDescription($node);

        $this->interfaces[] = [
            'name' => $namespace ? $namespace . '\\' . $interfaceName : $interfaceName,
            'namespace' => $namespace,
            'file' => $this->file,
            'description' => $description,
        ];
    }

    private function processTrait(Node\Stmt\Trait_ $node): void
    {
        $traitName = $node->name->name ?? 'Unknown';
        $namespace = $this->getNamespace($node);
        $description = $this->getDescription($node);

        $this->traits[] = [
            'name' => $namespace ? $namespace . '\\' . $traitName : $traitName,
            'namespace' => $namespace,
            'file' => $this->file,
            'description' => $description,
        ];
    }

    private function processEnum(Node\Stmt\Enum_ $node): void
    {
        $enumName = $node->name->name ?? 'Unknown';
        $namespace = $this->getNamespace($node);
        $description = $this->getDescription($node);

        $this->enums[] = [
            'name' => $namespace ? $namespace . '\\' . $enumName : $enumName,
            'namespace' => $namespace,
            'file' => $this->file,
            'description' => $description,
        ];
    }

    private function getNamespace(Node $node): string
    {
        // Find the namespace node in the AST
        $current = $node;
        while ($current = $current->getAttribute('parent')) {
            if ($current instanceof Node\Stmt\Namespace_) {
                return $current->name ? $current->name->toString() : '';
            }
        }
        return '';
    }

    private function getDescription(Node $node): string
    {
        $comments = $node->getComments();
        foreach ($comments as $comment) {
            if ($comment instanceof Node\Comment\Doc) {
                $text = $comment->getText();
                // Extract description from PHPDoc
                if (preg_match('/\/\*\*\s*\*\s*(.+?)(?:\s*\*\/|\s*\*\s*@)/s', $text, $matches)) {
                    return trim($matches[1]);
                }
            }
        }
        return 'No description available';
    }

    private function getPropertyType(Node\Stmt\Property $property): string
    {
        if ($property->type) {
            return $this->getTypeString($property->type);
        }
        return 'mixed';
    }

    private function getReturnType(Node\Stmt\ClassMethod $method): string
    {
        if ($method->returnType) {
            return $this->getTypeString($method->returnType);
        }
        return 'void';
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

    private function getMethodDescription(string $methodName): string
    {
        $descriptions = [
            '__construct' => 'Initialize the class instance',
            'index' => 'Display a listing of the resource',
            'create' => 'Show the form for creating a new resource',
            'store' => 'Store a newly created resource',
            'show' => 'Display the specified resource',
            'edit' => 'Show the form for editing the resource',
            'update' => 'Update the specified resource',
            'destroy' => 'Remove the specified resource',
        ];

        return $descriptions[$methodName] ?? 'Method description';
    }

    public function getClasses(): array
    {
        return $this->classes;
    }

    public function getInterfaces(): array
    {
        return $this->interfaces;
    }

    public function getTraits(): array
    {
        return $this->traits;
    }

    public function getEnums(): array
    {
        return $this->enums;
    }
}
