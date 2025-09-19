<?php

/** Comprehensive fix for all Filament v4 navigation group issues */
$resourceFiles = glob('app/Filament/Resources/*.php');

echo "Fixing all navigation group issues...\n";

foreach ($resourceFiles as $file) {
    $content = file_get_contents($file);
    $originalContent = $content;

    // Fix malformed navigation group declarations
    $content = preg_replace(
        '/(protected static \?\w+ \$model = [^;]+;)\s*\*\* @var UnitEnum\|string\|null \*\/\s*protected static \$navigationGroup/',
        '$1

    protected static string|UnitEnum|null $navigationGroup',
        $content
    );

    // Fix any remaining malformed navigation group declarations
    $content = preg_replace(
        '/protected static \?\w+ \$navigationGroup/',
        'protected static string|UnitEnum|null $navigationGroup',
        $content
    );

    // Fix navigation group without proper typing
    $content = preg_replace(
        '/protected static \$navigationGroup/',
        'protected static string|UnitEnum|null $navigationGroup',
        $content
    );

    if ($content !== $originalContent) {
        file_put_contents($file, $content);
        echo 'Fixed: ' . basename($file) . "\n";
    }
}

echo "All navigation group fixes completed.\n";
