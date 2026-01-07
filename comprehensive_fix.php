<?php

declare(strict_types=1);

// Find and fix all files with LaraZeus\SpatieTranslatable usage

function findFilesWithPattern($directory, $pattern)
{
    $files = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));

    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $content = file_get_contents($file->getPathname());
            if (strpos($content, $pattern) !== false) {
                $files[] = $file->getPathname();
            }
        }
    }

    return $files;
}

function fixFile($filePath)
{
    $content = file_get_contents($filePath);
    $originalContent = $content;

    // Remove LaraZeus imports
    $content = preg_replace('/use LaraZeus\\\\SpatieTranslatable[^;]+;[\r\n]*/', '', $content);

    // Remove trait usage
    $content = preg_replace('/use SpatieTranslatableResource;?\s*/', '', $content);
    $content = preg_replace('/use SpatieTranslatableCreateRecord;?\s*/', '', $content);
    $content = preg_replace('/use SpatieTranslatableEditRecord;?\s*/', '', $content);
    $content = preg_replace('/use SpatieTranslatableViewRecord;?\s*/', '', $content);
    $content = preg_replace('/use SpatieTranslatableListRecords;?\s*/', '', $content);

    // Clean up extra blank lines
    $content = preg_replace('/\n\s*\n\s*\n/', "\n\n", $content);

    if ($content !== $originalContent) {
        file_put_contents($filePath, $content);

        return true;
    }

    return false;
}

// Find all files with LaraZeus usage
$files = findFilesWithPattern('app/Filament', 'LaraZeus\\SpatieTranslatable');

echo 'Found ' . count($files) . " files with LaraZeus\\SpatieTranslatable usage:\n";

$fixed = 0;
foreach ($files as $file) {
    if (fixFile($file)) {
        echo "Fixed: $file\n";
        $fixed++;
    } else {
        echo "No changes needed: $file\n";
    }
}

echo "\nFixed $fixed files\n";
