<?php

// Get all models
$models = [];
$modelFiles = glob('app/Models/*.php');
foreach ($modelFiles as $file) {
    $basename = basename($file, '.php');
    // Skip scope classes and other non-model files
    if (str_ends_with($basename, 'Scope') ||
            str_ends_with($basename, 'Translation') ||
            in_array($basename, ['AdminUser', 'Setting'])) {
        continue;
    }
    $models[] = $basename;
}

// Get all resources
$resources = [];
$resourceFiles = glob('app/Filament/Resources/*Resource.php');
foreach ($resourceFiles as $file) {
    $basename = basename($file, 'Resource.php');
    $resources[] = $basename;
}

// Find missing resources
$missingResources = array_diff($models, $resources);

echo "Models without resources:\n";
foreach ($missingResources as $model) {
    echo "- $model\n";
}

echo "\nTotal models: " . count($models) . "\n";
echo 'Total resources: ' . count($resources) . "\n";
echo 'Missing resources: ' . count($missingResources) . "\n";

// Check for models that should have seeders
$modelsNeedingSeeders = [];
foreach ($missingResources as $model) {
    $seederFile = "database/seeders/{$model}Seeder.php";
    if (!file_exists($seederFile)) {
        $modelsNeedingSeeders[] = $model;
    }
}

echo "\nModels needing seeders:\n";
foreach ($modelsNeedingSeeders as $model) {
    echo "- $model\n";
}

echo "\nModels needing seeders: " . count($modelsNeedingSeeders) . "\n";
