<?php

/** Script to generate missing Filament Resource Page classes */
$basePath = __DIR__ . '/app/Filament/Resources';

// Get all empty PHP files in Pages directories
$emptyFiles = [];
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($basePath));
foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php' && $file->getSize() === 0) {
        $path = $file->getPathname();
        if (strpos($path, '/Pages/') !== false) {
            $emptyFiles[] = $path;
        }
    }
}

echo 'Found ' . count($emptyFiles) . " empty page files\n";

foreach ($emptyFiles as $filePath) {
    $relativePath = str_replace($basePath . '/', '', $filePath);
    $pathParts = explode('/', $relativePath);

    if (count($pathParts) < 3)
        continue;

    $resourceName = $pathParts[0];
    $pageName = basename($filePath, '.php');

    // Determine the page type and generate appropriate content
    $pageType = 'ListRecords';
    $actions = ['Actions\CreateAction::make()'];

    if (strpos($pageName, 'Create') === 0) {
        $pageType = 'CreateRecord';
        $actions = [];
    } elseif (strpos($pageName, 'Edit') === 0) {
        $pageType = 'EditRecord';
        $actions = ['Actions\ViewAction::make()', 'Actions\DeleteAction::make()'];
    } elseif (strpos($pageName, 'View') === 0) {
        $pageType = 'ViewRecord';
        $actions = ['Actions\EditAction::make()'];
    }

    // Generate the class content
    $className = $pageName;
    $namespace = "App\\Filament\\Resources\\{$resourceName}\Pages";
    $resourceClass = "App\\Filament\\Resources\\{$resourceName}";

    $content = "<?php\n\n";
    $content .= "declare(strict_types=1);\n\n";
    $content .= "namespace {$namespace};\n\n";
    $content .= "use {$resourceClass};\n";
    $content .= "use Filament\Actions;\n";
    $content .= "use Filament\\Resources\\Pages\\{$pageType};\n\n";
    $content .= "final class {$className} extends {$pageType}\n";
    $content .= "{\n";
    $content .= "    protected static string \$resource = {$resourceName}::class;\n\n";

    if (!empty($actions)) {
        $content .= "    protected function getHeaderActions(): array\n";
        $content .= "    {\n";
        $content .= "        return [\n";
        foreach ($actions as $action) {
            $content .= "            {$action},\n";
        }
        $content .= "        ];\n";
        $content .= "    }\n";
    }

    $content .= "}\n";

    // Write the file
    file_put_contents($filePath, $content);
    echo "Generated: {$relativePath}\n";
}

echo "Done!\n";

