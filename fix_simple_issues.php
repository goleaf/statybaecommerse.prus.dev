<?php

/**
 * Simple Filament v4 Issues Fix
 * Fixes the most common issues one by one
 */
echo "=== SIMPLE FILAMENT V4 ISSUES FIX ===\n\n";

// List of critical files to fix
$criticalFiles = [
    'app/Filament/Resources/NewsImageResource.php',
    'app/Filament/Resources/NewsResource.php',
    'app/Filament/Resources/NewsTagResource.php',
    'app/Filament/Resources/NormalSettingResource.php',
    'app/Filament/Resources/CartItemResource.php',
    'app/Filament/Resources/LocationResource.php',
    'app/Filament/Resources/OrderResource.php',
    'app/Filament/Resources/PartnerResource.php',
    'app/Filament/Resources/PartnerTierResource.php',
    'app/Filament/Resources/ProductComparisonResource.php',
    'app/Filament/Resources/ProductFeatureResource.php',
    'app/Filament/Resources/ProductHistoryResource.php',
    'app/Filament/Resources/ProductImageResource.php',
    'app/Filament/Resources/ProductVariantResource.php',
    'app/Filament/Resources/RecommendationConfigResource.php',
    'app/Filament/Resources/ReportResource.php',
    'app/Filament/Resources/ReviewResource.php',
    'app/Filament/Resources/SeoDataResource.php',
    'app/Filament/Resources/SettingResource.php',
    'app/Filament/Resources/StockMovementResource.php',
    'app/Filament/Resources/SubscriberResource.php',
    'app/Filament/Resources/SystemSettingResource.php',
    'app/Filament/Resources/UserBehaviorResource.php',
    'app/Filament/Resources/UserPreferenceResource.php',
    'app/Filament/Resources/UserWishlistResource.php',
    'app/Filament/Resources/VariantAttributeValueResource.php',
    'app/Filament/Resources/VariantCombinationResource.php',
    'app/Filament/Resources/VariantImageResource.php',
    'app/Filament/Resources/VariantInventoryResource.php',
    'app/Filament/Resources/VariantPriceHistoryResource.php',
    'app/Filament/Resources/VariantPricingRuleResource.php',
];

$fixedFiles = [];
$errors = [];

echo 'Fixing '.count($criticalFiles)." critical files...\n\n";

foreach ($criticalFiles as $file) {
    if (! file_exists($file)) {
        echo "❌ File not found: $file\n";

        continue;
    }

    $content = file_get_contents($file);
    $originalContent = $content;
    $fileChanged = false;

    echo 'Processing: '.basename($file).'... ';

    // Fix 1: Remove duplicate imports
    $lines = explode("\n", $content);
    $uniqueImports = [];
    $newLines = [];

    foreach ($lines as $line) {
        if (strpos($line, 'use ') === 0) {
            if (! in_array($line, $uniqueImports)) {
                $uniqueImports[] = $line;
                $newLines[] = $line;
            }
        } else {
            $newLines[] = $line;
        }
    }

    $content = implode("\n", $newLines);

    // Fix 2: Fix Form → Schema updates
    $content = str_replace('use Filament\Forms\Form;', 'use Filament\Schemas\Schema;', $content);
    $content = str_replace('public static function form(Form $form): Form', 'public static function form(Schema $schema): Schema', $content);
    $content = str_replace('return $form', 'return $schema', $content);

    // Fix 3: Fix navigation group type issues
    $content = preg_replace('/protected static \?\w+ \$navigationGroup = ([^;]+);/', 'protected static $navigationGroup = $1;', $content);

    // Fix 4: Remove problematic docblock type annotations
    $content = preg_replace('/\s*\/\*\* @var UnitEnum\|string\|null \*\/\s*\n\s*protected static \$navigationGroup/', '    protected static $navigationGroup', $content);

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
echo "Running syntax check on fixed files...\n";

$syntaxErrors = [];
$syntaxOk = [];

foreach ($fixedFiles as $file) {
    $output = [];
    $returnCode = 0;
    exec("php -l $file 2>&1", $output, $returnCode);

    if ($returnCode !== 0) {
        $syntaxErrors[] = basename($file).': '.implode(' ', $output);
    } else {
        $syntaxOk[] = basename($file);
    }
}

echo '✅ Files with valid syntax: '.count($syntaxOk)."\n";
echo '❌ Files with syntax errors: '.count($syntaxErrors)."\n";

if (! empty($syntaxErrors)) {
    echo "\nSyntax errors found:\n";
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
    echo implode("\n", array_slice($output, 0, 10))."\n";
}

echo "\nDone!\n";
