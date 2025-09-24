<?php

/**
 * Comprehensive Analysis Script for Laravel + Filament v4 E-commerce System
 * Analyzes all models, resources, and identifies missing components
 */
echo "=== COMPREHENSIVE LARAVEL + FILAMENT V4 ANALYSIS ===\n\n";

// Get all models (excluding translations and scopes)
$modelFiles = glob('app/Models/*.php');
$models = [];
foreach ($modelFiles as $file) {
    $modelName = basename($file, '.php');
    if (! str_contains($modelName, 'Translation') && ! str_contains($modelName, 'Scope')) {
        $models[] = $modelName;
    }
}

// Get all resources
$resourceFiles = glob('app/Filament/Resources/*Resource.php');
$resources = [];
foreach ($resourceFiles as $file) {
    $resourceName = basename($file, 'Resource.php');
    $resources[] = $resourceName;
}

echo "📊 MODELS ANALYSIS\n";
echo 'Total models found: '.count($models)."\n\n";

$modelsWithResources = [];
$modelsWithoutResources = [];
$emptyResources = [];
$resourcesWithIssues = [];

foreach ($models as $model) {
    $resourceFile = "app/Filament/Resources/{$model}Resource.php";

    echo "🔍 Analyzing: $model\n";

    if (file_exists($resourceFile)) {
        $resourceSize = filesize($resourceFile);
        if ($resourceSize < 100) {
            $emptyResources[] = $model;
            echo "  ⚠️  Empty resource ($resourceSize bytes)\n";
        } else {
            $modelsWithResources[] = $model;
            echo "  ✅ Resource exists ($resourceSize bytes)\n";

            // Check for syntax errors
            $output = [];
            $returnCode = 0;
            exec("php -l $resourceFile 2>&1", $output, $returnCode);

            if ($returnCode !== 0) {
                $resourcesWithIssues[] = $model;
                echo '  ❌ Syntax error: '.implode(' ', $output)."\n";
            }
        }
    } else {
        $modelsWithoutResources[] = $model;
        echo "  ❌ No resource found\n";
    }
}

echo "\n=== RESOURCE COMPATIBILITY ANALYSIS ===\n";

$resourcesUsingOldForm = [];
$resourcesUsingNewSchema = [];
$resourcesWithNavigationIssues = [];

foreach ($resourceFiles as $resourceFile) {
    $resourceName = basename($resourceFile, '.php');
    $content = file_get_contents($resourceFile);

    echo "🔍 Analyzing resource: $resourceName\n";

    // Check if using old Form class
    if (strpos($content, 'use Filament\Forms\Form;') !== false ||
        strpos($content, 'public static function form(Form $form): Form') !== false) {
        $resourcesUsingOldForm[] = $resourceName;
        echo "  ⚠️  Using old Form class\n";
    } elseif (strpos($content, 'use Filament\Schemas\Schema;') !== false ||
               strpos($content, 'public static function form(Schema $schema): Schema') !== false) {
        $resourcesUsingNewSchema[] = $resourceName;
        echo "  ✅ Using new Schema class\n";
    }

    // Check for navigation group issues
    if (strpos($content, 'protected static $navigationGroup') !== false) {
        if (strpos($content, '/** @var UnitEnum|string|null */') === false) {
            $resourcesWithNavigationIssues[] = $resourceName;
            echo "  ⚠️  Navigation group type issue\n";
        }
    }
}

echo "\n=== SUMMARY ===\n";
echo '✅ Models with resources: '.count($modelsWithResources)."\n";
echo '❌ Models without resources: '.count($modelsWithoutResources)."\n";
echo '⚠️  Empty resources: '.count($emptyResources)."\n";
echo '🔧 Resources with issues: '.count($resourcesWithIssues)."\n";

echo "\n=== RESOURCE COMPATIBILITY ===\n";
echo '✅ Resources using new Schema: '.count($resourcesUsingNewSchema)."\n";
echo '⚠️  Resources using old Form: '.count($resourcesUsingOldForm)."\n";
echo '🔧 Resources with navigation issues: '.count($resourcesWithNavigationIssues)."\n";

if (! empty($modelsWithoutResources)) {
    echo "\n❌ MODELS WITHOUT RESOURCES:\n";
    foreach ($modelsWithoutResources as $model) {
        echo "- $model\n";
    }
}

if (! empty($emptyResources)) {
    echo "\n⚠️  EMPTY RESOURCES:\n";
    foreach ($emptyResources as $model) {
        echo "- {$model}Resource.php\n";
    }
}

if (! empty($resourcesWithIssues)) {
    echo "\n🔧 RESOURCES WITH ISSUES:\n";
    foreach ($resourcesWithIssues as $model) {
        echo "- {$model}Resource.php\n";
    }
}

if (! empty($resourcesUsingOldForm)) {
    echo "\n⚠️  RESOURCES USING OLD FORM CLASS:\n";
    foreach ($resourcesUsingOldForm as $resource) {
        echo "- $resource\n";
    }
}

if (! empty($resourcesWithNavigationIssues)) {
    echo "\n🔧 RESOURCES WITH NAVIGATION GROUP ISSUES:\n";
    foreach ($resourcesWithNavigationIssues as $resource) {
        echo "- $resource\n";
    }
}

echo "\n=== RECOMMENDATIONS ===\n";
echo '1. Fix navigation group type issues in '.count($resourcesWithNavigationIssues)." resources\n";
echo '2. Update '.count($resourcesUsingOldForm)." resources from Form to Schema\n";
echo '3. Implement '.count($emptyResources)." empty resources\n";
echo '4. Create '.count($modelsWithoutResources)." missing resources\n";
echo '5. Fix syntax errors in '.count($resourcesWithIssues)." resources\n";

echo "\nDone!\n";
