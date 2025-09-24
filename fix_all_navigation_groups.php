<?php

/**
 * Comprehensive Navigation Group Fix for Filament v4
 * Fixes all navigation group type issues across all resources
 */
echo "=== COMPREHENSIVE NAVIGATION GROUP FIX ===\n\n";

$filamentResourcesPath = 'app/Filament/Resources/';
$files = glob($filamentResourcesPath.'*.php');

$fixedFiles = [];
$errors = [];

echo 'Scanning '.count($files)." resource files...\n\n";

foreach ($files as $file) {
    $content = file_get_contents($file);
    $originalContent = $content;
    $fileChanged = false;

    echo 'Processing: '.basename($file).'... ';

    // Fix 1: Remove any typed navigation group properties and replace with docblock
    $content = preg_replace(
        '/protected static \?\w+ \$navigationGroup = ([^;]+);/',
        '/** @var UnitEnum|string|null */'."\n    protected static \$navigationGroup = $1;",
        $content
    );

    // Fix 2: Fix navigation group with enum values
    $content = preg_replace(
        '/(\s+)\/\*\* @var UnitEnum\|string\|null \*\/\s*\n(\s+)protected static \$navigationGroup = NavigationGroup::([^;]+);/',
        '$1/** @var UnitEnum|string|null */'."\n$2protected static \$navigationGroup = NavigationGroup::$3;",
        $content
    );

    // Fix 3: Fix navigation group with string values
    $content = preg_replace(
        '/(\s+)\/\*\* @var UnitEnum\|string\|null \*\/\s*\n(\s+)protected static \$navigationGroup = \'([^\']+)\';/',
        '$1/** @var UnitEnum|string|null */'."\n$2protected static \$navigationGroup = '$3';",
        $content
    );

    // Fix 4: Ensure UnitEnum import exists if navigation group is used
    if (strpos($content, 'protected static $navigationGroup') !== false && strpos($content, 'use UnitEnum;') === false) {
        // Find the last use statement and add UnitEnum import
        $content = preg_replace(
            '/(use [^;]+;\s*\n)(class \w+ extends Resource)/',
            '$1use UnitEnum;'."\n\n$2",
            $content
        );
    }

    // Fix 5: Remove duplicate UnitEnum imports
    $content = preg_replace(
        '/(use UnitEnum;\s*\n)+/',
        'use UnitEnum;'."\n",
        $content
    );

    if ($content !== $originalContent) {
        if (file_put_contents($file, $content)) {
            $fixedFiles[] = $file;
            echo "✅ FIXED\n";
            $fileChanged = true;
        } else {
            $errors[] = "❌ Failed to write: $file";
            echo "❌ WRITE ERROR\n";
        }
    } else {
        echo "✅ OK\n";
    }
}

echo "\n=== SUMMARY ===\n";
echo 'Files fixed: '.count($fixedFiles)."\n";
echo 'Errors: '.count($errors)."\n";

if (! empty($fixedFiles)) {
    echo "\nFixed files:\n";
    foreach ($fixedFiles as $file) {
        echo '- '.basename($file)."\n";
    }
}

if (! empty($errors)) {
    echo "\nErrors:\n";
    foreach ($errors as $error) {
        echo "- $error\n";
    }
}

echo "\n=== VALIDATION ===\n";
echo "Running syntax check on all files...\n";

$syntaxErrors = [];
foreach ($files as $file) {
    $output = [];
    $returnCode = 0;
    exec("php -l $file 2>&1", $output, $returnCode);

    if ($returnCode !== 0) {
        $syntaxErrors[] = basename($file).': '.implode(' ', $output);
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

echo "\n=== TESTING FILAMENT COMMANDS ===\n";
$output = [];
$returnCode = 0;
exec('php artisan list 2>&1', $output, $returnCode);

if ($returnCode === 0) {
    echo "✅ Filament commands working\n";
} else {
    echo "❌ Filament commands still have errors:\n";
    echo implode("\n", $output)."\n";
}

echo "\nDone!\n";
