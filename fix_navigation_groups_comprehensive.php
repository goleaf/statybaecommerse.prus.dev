<?php

/**
 * Comprehensive Navigation Group Type Fix for Filament v4
 * Fixes all navigation group type declarations to be compatible with Filament v4
 */
$filamentResourcesPath = 'app/Filament/Resources/';
$files = glob($filamentResourcesPath.'*.php');

$fixedFiles = [];
$errors = [];

foreach ($files as $file) {
    $content = file_get_contents($file);
    $originalContent = $content;

    // Fix navigation group type declarations
    $patterns = [
        // Fix typed navigation group properties
        '/protected static \?\w+ \$navigationGroup = ([^;]+);/' => '/** @var UnitEnum|string|null */'."\n    protected static \$navigationGroup = \$1;",
        // Fix navigation group with enum values
        '/(\s+)\/\*\* @var UnitEnum\|string\|null \*\/\s*\n(\s+)protected static \$navigationGroup = NavigationGroup::([^;]+);/' => '$1/** @var UnitEnum|string|null */'."\n\$2protected static \$navigationGroup = NavigationGroup::\$3;",
        // Ensure UnitEnum import exists
        '/(use [^;]+;\s*\n)(class \w+ extends Resource)/' => '$1use UnitEnum;'."\n\n\$2",
    ];

    foreach ($patterns as $pattern => $replacement) {
        $content = preg_replace($pattern, $replacement, $content);
    }

    // Add UnitEnum import if not present and navigation group is used
    if (strpos($content, 'protected static $navigationGroup') !== false && strpos($content, 'use UnitEnum;') === false) {
        $content = preg_replace('/(use [^;]+;\s*\n)(class \w+ extends Resource)/', '$1use UnitEnum;'."\n\n\$2", $content);
    }

    if ($content !== $originalContent) {
        if (file_put_contents($file, $content)) {
            $fixedFiles[] = $file;
            echo "✅ Fixed: $file\n";
        } else {
            $errors[] = "❌ Failed to write: $file";
        }
    }
}

echo "\n=== SUMMARY ===\n";
echo 'Files fixed: '.count($fixedFiles)."\n";
echo 'Errors: '.count($errors)."\n";

if (! empty($fixedFiles)) {
    echo "\nFixed files:\n";
    foreach ($fixedFiles as $file) {
        echo "- $file\n";
    }
}

if (! empty($errors)) {
    echo "\nErrors:\n";
    foreach ($errors as $error) {
        echo "- $error\n";
    }
}

echo "\n=== VALIDATION ===\n";
echo "Running syntax check...\n";

$syntaxErrors = [];
foreach ($fixedFiles as $file) {
    $output = [];
    $returnCode = 0;
    exec("php -l $file 2>&1", $output, $returnCode);

    if ($returnCode !== 0) {
        $syntaxErrors[] = $file.': '.implode("\n", $output);
    }
}

if (empty($syntaxErrors)) {
    echo "✅ All files have valid syntax\n";
} else {
    echo "❌ Syntax errors found:\n";
    foreach ($syntaxErrors as $error) {
        echo "- $error\n";
    }
}

echo "\nDone!\n";
