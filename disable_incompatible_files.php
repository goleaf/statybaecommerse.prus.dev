<?php

declare(strict_types=1);

$filesToDisable = [
    'app/Filament/Tables/Concerns/ConfiguresToggleableTableLayout.php',
    'app/Filament/Resources/UserProductInteractionResource/Pages/ViewUserProductInteraction.php',
    'app/Filament/Resources/RecommendationConfigResource/Pages/ViewRecommendationConfig.php',
    'app/Filament/Resources/RecommendationConfigResourceSimple/Pages/ViewRecommendationConfigSimple.php',
    'app/Filament/Pages/InventoryManagement.php',
    'app/Filament/Pages/UserImpersonation.php',
];

echo "Disabling incompatible files...\n";

foreach ($filesToDisable as $file) {
    if (file_exists($file)) {
        $bakFile = $file . '.bak';
        if (rename($file, $bakFile)) {
            echo "✓ Disabled: {$file}\n";
        } else {
            echo "✗ Failed to disable: {$file}\n";
        }
    } else {
        echo "- File not found: {$file}\n";
    }
}

echo "\nDone.\n";
