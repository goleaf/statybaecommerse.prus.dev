<?php

/**
 * Final Comprehensive NavigationGroup Fix Script
 * Fixes all Filament v4 navigationGroup type compatibility issues
 */
$projectRoot = '/www/wwwroot/statybaecommerse.prus.dev';
$resourcesPath = $projectRoot.'/app/Filament/Resources';

echo "🔧 Starting final comprehensive NavigationGroup fix...\n";

// Get all PHP files in Resources directory recursively
$files = glob($resourcesPath.'/**/*.php');
$fixedCount = 0;
$errorCount = 0;

foreach ($files as $file) {
    $relativePath = str_replace($projectRoot.'/', '', $file);
    echo "Processing: $relativePath\n";

    $content = file_get_contents($file);
    $originalContent = $content;

    // Skip if file doesn't contain navigationGroup
    if (strpos($content, 'navigationGroup') === false) {
        continue;
    }

    // Add NavigationGroup import if not present and file uses NavigationGroup::
    if (strpos($content, 'use App\Enums\NavigationGroup;') === false && strpos($content, 'NavigationGroup::') !== false) {
        // Find the namespace line and add import after it
        $lines = explode("\n", $content);
        $newLines = [];
        $importAdded = false;

        foreach ($lines as $line) {
            $newLines[] = $line;

            // Add import after namespace
            if (strpos($line, 'namespace App\Filament\Resources') !== false && ! $importAdded) {
                $newLines[] = '';
                $newLines[] = 'use App\Enums\NavigationGroup;';
                $importAdded = true;
            }
        }

        $content = implode("\n", $newLines);
    }

    // Fix all navigationGroup patterns
    $patterns = [
        // Fix direct enum usage without ->value
        '/(protected static \$navigationGroup = NavigationGroup::[^;]+;)/' => function ($matches) {
            $line = $matches[1];
            // If it doesn't end with ->value, add it
            if (strpos($line, '->value') === false) {
                return str_replace(';', '->value;', $line);
            }

            return $line;
        },
        // Fix string literals to use enum
        "/(protected static \\\$navigationGroup = '[^']+';)/" => function ($matches) {
            $line = $matches[1];
            $value = trim($line, 'protected static $navigationGroup = \';');

            // Map common string values to enum cases
            $mapping = [
                'Products' => 'NavigationGroup::Products->value',
                'Orders' => 'NavigationGroup::Orders->value',
                'Users' => 'NavigationGroup::Users->value',
                'Settings' => 'NavigationGroup::Settings->value',
                'Analytics' => 'NavigationGroup::Analytics->value',
                'Content' => 'NavigationGroup::Content->value',
                'Content Management' => 'NavigationGroup::ContentManagement->value',
                'System' => 'NavigationGroup::System->value',
                'Marketing' => 'NavigationGroup::Marketing->value',
                'Inventory' => 'NavigationGroup::Inventory->value',
                'Reports' => 'NavigationGroup::Reports->value',
                'Locations' => 'NavigationGroup::Locations->value',
                'Discounts' => 'NavigationGroup::Discounts->value',
                'Campaigns' => 'NavigationGroup::Campaigns->value',
                'News' => 'NavigationGroup::News->value',
                'Referral System' => 'NavigationGroup::Referral->value',
            ];

            if (isset($mapping[$value])) {
                return "protected static \$navigationGroup = {$mapping[$value]};";
            }

            return $line;
        },
        // Fix type declarations - ensure proper docblock
        '/(\*\* @var UnitEnum\|string\|null \*\/\s*)?protected static \$navigationGroup = ([^;]+);/' => function ($matches) {
            $docblock = $matches[1] ?? '';
            $value = $matches[2];

            // Ensure proper docblock
            if (empty($docblock)) {
                $docblock = "/** @var UnitEnum|string|null */\n    ";
            }

            return $docblock."protected static \$navigationGroup = $value;";
        },
    ];

    // Apply patterns
    foreach ($patterns as $pattern => $replacement) {
        if (is_callable($replacement)) {
            $content = preg_replace_callback($pattern, $replacement, $content);
        } else {
            $content = preg_replace($pattern, $replacement, $content);
        }
    }

    // Only write if content changed
    if ($content !== $originalContent) {
        if (file_put_contents($file, $content)) {
            echo "✅ Fixed: $relativePath\n";
            $fixedCount++;
        } else {
            echo "❌ Error writing: $relativePath\n";
            $errorCount++;
        }
    } else {
        echo "⏭️  No changes needed: $relativePath\n";
    }
}

echo "\n🎯 NavigationGroup Fix Complete!\n";
echo "✅ Files fixed: $fixedCount\n";
echo "❌ Errors: $errorCount\n";

// Clear all caches
echo "\n🧹 Clearing caches...\n";
$cacheCommands = [
    "cd $projectRoot && php artisan config:clear",
    "cd $projectRoot && php artisan cache:clear",
    "cd $projectRoot && php artisan route:clear",
    "cd $projectRoot && php artisan view:clear",
    "cd $projectRoot && php artisan optimize:clear",
];

foreach ($cacheCommands as $command) {
    echo "Running: $command\n";
    $output = shell_exec($command.' 2>&1');
    if ($output) {
        echo $output."\n";
    }
}

// Test the fixes
echo "\n🧪 Testing fixes...\n";
$testCommand = "cd $projectRoot && php artisan test --stop-on-failure 2>&1 | head -30";
$output = shell_exec($testCommand);
echo $output;

echo "\n✨ Final comprehensive NavigationGroup fix completed!\n";
