<?php

/** Comprehensive script to find and create all missing Filament Resource Page classes */
$basePath = __DIR__ . '/app/Filament/Resources';

// Find all resource files
$resourceFiles = glob($basePath . '/*Resource.php');

echo 'Found ' . count($resourceFiles) . " resource files\n";

foreach ($resourceFiles as $resourceFile) {
    $resourceName = basename($resourceFile, 'Resource.php');
    $resourceClass = basename($resourceFile, '.php');

    // Read the resource file to find getPages() method
    $content = file_get_contents($resourceFile);

    if (preg_match('/public static function getPages\(\): array\s*\{([^}]+)\}/s', $content, $matches)) {
        $pagesContent = $matches[1];

        // Extract page classes from the getPages method
        if (preg_match_all('/Pages\(\w+)::route/', $pagesContent, $pageMatches)) {
            $pageClasses = $pageMatches[1];

            // Create Pages directory if it doesn't exist
            $pagesDir = dirname($resourceFile) . '/' . $resourceClass . '/Pages';
            if (!is_dir($pagesDir)) {
                mkdir($pagesDir, 0755, true);
                echo "Created directory: {$pagesDir}\n";
            }

            // Create each page class
            foreach ($pageClasses as $pageClass) {
                $pageFile = $pagesDir . '/' . $pageClass . '.php';

                // Skip if file already exists and is not empty
                if (file_exists($pageFile) && filesize($pageFile) > 0) {
                    continue;
                }

                // Determine page type and actions
                $pageType = 'ListRecords';
                $actions = ['Actions\CreateAction::make()'];

                if (strpos($pageClass, 'Create') === 0) {
                    $pageType = 'CreateRecord';
                    $actions = [];
                } elseif (strpos($pageClass, 'Edit') === 0) {
                    $pageType = 'EditRecord';
                    $actions = ['Actions\ViewAction::make()', 'Actions\DeleteAction::make()'];
                } elseif (strpos($pageClass, 'View') === 0) {
                    $pageType = 'ViewRecord';
                    $actions = ['Actions\EditAction::make()'];
                }

                // Generate the class content
                $namespace = "App\\Filament\\Resources\\{$resourceClass}\Pages";
                $resourceClassFull = "App\\Filament\\Resources\\{$resourceClass}";

                $content = "<?php\n\n";
                $content .= "declare(strict_types=1);\n\n";
                $content .= "namespace {$namespace};\n\n";
                $content .= "use {$resourceClassFull};\n";
                $content .= "use Filament\Actions;\n";
                $content .= "use Filament\\Resources\\Pages\\{$pageType};\n\n";
                $content .= "final class {$pageClass} extends {$pageType}\n";
                $content .= "{\n";
                $content .= "    protected static string \$resource = {$resourceClass}::class;\n\n";

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
                file_put_contents($pageFile, $content);
                echo "Generated: {$resourceClass}/Pages/{$pageClass}.php\n";
            }
        }
    }
}

echo "Done!\n";

