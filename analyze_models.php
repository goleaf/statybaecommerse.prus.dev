<?php

// Script to analyze models and their corresponding resources
$models = [];
$resources = [];

// Get all models
$modelFiles = glob('app/Models/*.php');
foreach ($modelFiles as $file) {
    $className = basename($file, '.php');
    if ($className !== 'User' && !str_contains($className, 'Translation') && !str_contains($className, 'Scope')) {
        $models[] = $className;
    }
}

// Get all resources
$resourceFiles = glob('app/Filament/Resources/*Resource.php');
foreach ($resourceFiles as $file) {
    $className = basename($file, 'Resource.php');
    $resources[] = $className;
}

echo "Models without Resources:\n";
$missingResources = array_diff($models, $resources);
foreach ($missingResources as $model) {
    echo "- $model\n";
}

echo "\nResources without Models:\n";
$missingModels = array_diff($resources, $models);
foreach ($missingModels as $resource) {
    echo "- $resource\n";
}

echo "\nTotal Models: " . count($models) . "\n";
echo "Total Resources: " . count($resources) . "\n";
echo "Missing Resources: " . count($missingResources) . "\n";
