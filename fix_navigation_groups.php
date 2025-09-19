<?php

// Fix navigation group type issues in all Filament resources
$resourceFiles = glob('app/Filament/Resources/*.php');

foreach ($resourceFiles as $file) {
    $content = file_get_contents($file);
    $originalContent = $content;

    // Fix the navigation group type declaration
    $content = preg_replace(
        '/\s+\/\*\* @var UnitEnum\|string\|null \*\/\s+protected static \$navigationGroup/',
        '    /** @var UnitEnum|string|null */' . "\n" . '    protected static $navigationGroup',
        $content
    );

    // Also fix any malformed navigation group declarations
    $content = preg_replace(
        '/protected static \?\w+ \$navigationGroup/',
        'protected static $navigationGroup',
        $content
    );

    if ($content !== $originalContent) {
        file_put_contents($file, $content);
        echo 'Fixed: ' . basename($file) . "\n";
    }
}

echo "Navigation group fixes completed.\n";
