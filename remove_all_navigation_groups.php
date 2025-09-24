<?php

/**
 * Remove ALL NavigationGroup Properties
 * This script removes all navigationGroup properties from all Filament resources
 */
$projectRoot = '/www/wwwroot/statybaecommerse.prus.dev';
$resourcesPath = $projectRoot.'/app/Filament/Resources';

echo "🔧 Removing ALL navigationGroup properties...\n";

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

    // Remove all navigationGroup related lines
    $lines = explode("\n", $content);
    $newLines = [];
    $skipNext = false;

    foreach ($lines as $line) {
        // Skip lines containing navigationGroup
        if (strpos($line, 'navigationGroup') !== false) {
            $skipNext = true;

            continue;
        }

        // Skip docblock lines if we just skipped a navigationGroup line
        if ($skipNext && (strpos($line, '/**') !== false || strpos($line, ' *') !== false || strpos($line, ' */') !== false)) {
            continue;
        }

        // Skip empty lines after navigationGroup
        if ($skipNext && trim($line) === '') {
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

echo "\n✨ ALL navigationGroup properties removed!\n";
