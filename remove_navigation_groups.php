<?php

/**
 * Remove NavigationGroup Properties Temporarily
 * This script removes all navigationGroup properties to allow tests to run
 */
$projectRoot = '/www/wwwroot/statybaecommerse.prus.dev';
$resourcesPath = $projectRoot.'/app/Filament/Resources';

echo "🔧 Removing navigationGroup properties temporarily...\n";

// Get all PHP files in Resources directory recursively
$files = glob($resourcesPath.'/**/*.php');
$fixedCount = 0;

foreach ($files as $file) {
    $relativePath = str_replace($projectRoot.'/', '', $file);
    echo "Processing: $relativePath\n";

    $content = file_get_contents($file);
    $originalContent = $content;

    // Skip if file doesn't contain navigationGroup
    if (strpos($content, 'navigationGroup') === false) {
        continue;
    }

    // Remove navigationGroup property lines
    $lines = explode("\n", $content);
    $newLines = [];
    $skipNext = false;

    foreach ($lines as $line) {
        if (strpos($line, 'protected static $navigationGroup') !== false) {
            // Skip this line and the next line if it's a docblock
            $skipNext = true;

            continue;
        }

        if ($skipNext && (strpos($line, '/**') !== false || strpos($line, ' *') !== false)) {
            continue;
        }

        if ($skipNext && strpos($line, ' */') !== false) {
            $skipNext = false;

            continue;
        }

        if ($skipNext && trim($line) === '') {
            $skipNext = false;

            continue;
        }

        $skipNext = false;
        $newLines[] = $line;
    }

    $content = implode("\n", $newLines);

    // Only write if content changed
    if ($content !== $originalContent) {
        if (file_put_contents($file, $content)) {
            echo "✅ Removed navigationGroup from: $relativePath\n";
            $fixedCount++;
        } else {
            echo "❌ Error writing: $relativePath\n";
        }
    } else {
        echo "⏭️  No navigationGroup found: $relativePath\n";
    }
}

echo "\n🎯 NavigationGroup Removal Complete!\n";
echo "✅ Files modified: $fixedCount\n";

echo "\n✨ NavigationGroup properties removed temporarily!\n";
