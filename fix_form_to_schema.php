<?php

/**
 * Fix Form → Schema compatibility issues for Filament v4
 */
echo "=== FIXING FORM → SCHEMA COMPATIBILITY ===\n\n";

$resourcesToFix = [
    'News',
    'NewsTag',
    'NormalSetting',
    'PriceList',
    'RecommendationBlock',
];

$fixedCount = 0;
$errorCount = 0;

$filesToFix = [
    'app/Filament/Resources/NewsResource.php',
    'app/Filament/Resources/NewsTagResource.php',
    'app/Filament/Resources/NormalSettingResource.php',
    'app/Filament/Resources/PriceListResource.php',
    'app/Filament/Resources/RecommendationBlockResource.php',
];

foreach ($filesToFix as $file) {

    if (! file_exists($file)) {
        echo "❌ File not found: $file\n";
        $errorCount++;

        continue;
    }

    echo '🔧 Fixing: '.basename($file)."\n";

    $content = file_get_contents($file);
    $originalContent = $content;

    // Fix form method signature
    $content = preg_replace(
        '/public static function form\(Form \$form\): Form/',
        'public static function form(Schema $schema): Schema',
        $content
    );

    // Fix return statement
    $content = preg_replace(
        '/return \$form\s*->schema\(/',
        'return $schema->schema(',
        $content
    );

    // Add Form import if not present
    if (strpos($content, 'use Filament\Forms\Form;') === false) {
        $content = preg_replace(
            '/(use Filament\\Forms;)/',
            '$1'."\nuse Filament\Forms\Form;",
            $content
        );
    }

    if ($content !== $originalContent) {
        if (file_put_contents($file, $content)) {
            echo '  ✅ Fixed: '.basename($file)."\n";
            $fixedCount++;
        } else {
            echo '  ❌ Failed to write: '.basename($file)."\n";
            $errorCount++;
        }
    } else {
        echo '  ⏭️  No changes needed: '.basename($file)."\n";
    }
}

echo "\n=== SUMMARY ===\n";
echo "✅ Resources fixed: $fixedCount\n";
echo "❌ Errors: $errorCount\n";

echo "\nDone!\n";
