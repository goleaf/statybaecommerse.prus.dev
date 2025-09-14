<?php

// Get all Filament resource files
$resourceDir = __DIR__ . '/app/Filament/Resources';
$resourceFiles = [];

function getAllPhpFiles($dir) {
    $files = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $files[] = $file->getPathname();
        }
    }
    return $files;
}

$resourceFiles = getAllPhpFiles($resourceDir);

foreach ($resourceFiles as $file) {
    $content = file_get_contents($file);
    $originalContent = $content;
    
    // Fix multiple duplicate docblock lines for navigationGroup
    $content = preg_replace(
        '/\/\*\*\s*\n\s*\*\s*@var\s+(UnitEnum|BackedEnum)\|string\|null\s*\n\s*\*\/\s*\n\s*\/\*\*\s*\n\s*\*\s*@var\s+(UnitEnum|BackedEnum)\|string\|null\s*\n\s*\*\/\s*\n\s*\/\*\*\s*\n\s*\*\s*@var\s+(UnitEnum|BackedEnum)\|string\|null\s*\n\s*\*\/\s*\n\s*\/\*\*\s*\n\s*\*\s*@var\s+(UnitEnum|BackedEnum)\|string\|null\s*\n\s*\*\/\s*/',
        '/** @var UnitEnum|string|null */',
        $content
    );
    
    // Fix single duplicate docblock lines
    $content = preg_replace(
        '/\/\*\*\s*\n\s*\*\s*@var\s+(UnitEnum|BackedEnum)\|string\|null\s*\n\s*\*\/\s*\n\s*\/\*\*\s*\n\s*\*\s*@var\s+(UnitEnum|BackedEnum)\|string\|null\s*\n\s*\*\/\s*/',
        '/** @var UnitEnum|string|null */',
        $content
    );
    
    // Add UnitEnum import if missing and navigationGroup is used
    if (strpos($content, 'protected static $navigationGroup') !== false && 
        strpos($content, 'use UnitEnum;') === false &&
        strpos($content, 'namespace ') !== false) {
        
        // Find the namespace line and add the import after it
        $content = preg_replace(
            '/(namespace\s+[^;]+;)/',
            "$1\n\nuse UnitEnum;",
            $content
        );
    }
    
    // Only write if content changed
    if ($content !== $originalContent) {
        file_put_contents($file, $content);
        echo "Fixed: " . basename($file) . "\n";
    }
}

echo "Navigation group fixes completed!\n";