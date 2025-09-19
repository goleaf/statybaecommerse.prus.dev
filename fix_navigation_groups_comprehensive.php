<?php

/**
 * Comprehensive Navigation Group Fix for Filament v4
 * Fixes all malformed $navigationGroup declarations in Filament resources
 */
$resourceFiles = glob('app/Filament/Resources/*.php');

echo "Starting comprehensive navigation group fixes...\n";

foreach ($resourceFiles as $file) {
    $content = file_get_contents($file);
    $originalContent = $content;

    // Fix malformed navigation group declarations
    $content = preg_replace(
        '/(protected static \?\w+ \$model = [^;]+;)\s*\*\* @var UnitEnum\|string\|null \*\/\s*protected static \$navigationGroup/',
        '$1

    /** @var UnitEnum|string|null */
    protected static $navigationGroup',
        $content
    );

    // Fix any remaining malformed navigation group declarations
    $content = preg_replace(
        '/protected static \?\w+ \$navigationGroup/',
        'protected static $navigationGroup',
        $content
    );

    // Ensure proper spacing and formatting
    $content = preg_replace(
        '/(protected static \?\w+ \$model = [^;]+;)\s*\*\* @var UnitEnum\|string\|null \*\/\s*protected static \$navigationGroup/',
        '$1

    /** @var UnitEnum|string|null */
    protected static $navigationGroup',
        $content
    );

    if ($content !== $originalContent) {
        file_put_contents($file, $content);
        echo 'Fixed: ' . basename($file) . "\n";
    }
}

echo "Navigation group fixes completed.\n";
