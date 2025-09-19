<?php

/**
 * Fix All Filament Component Imports
 * 
 * This script fixes all incorrect Filament component imports
 * for Filament v4 compatibility
 */

echo "🔧 Fixing All Filament Component Imports for v4...\n\n";

// Define the mapping of old imports to new imports
$importMappings = [
    // Section moved to Schemas
    'use Filament\\Forms\\Components\\Section;' => 'use Filament\\Schemas\\Components\\Section;',
    'Filament\\Forms\\Components\\Section' => 'Filament\\Schemas\\Components\\Section',
    
    // Grid moved to Schemas
    'use Filament\\Forms\\Components\\Grid;' => 'use Filament\\Schemas\\Components\\Grid;',
    'Filament\\Forms\\Components\\Grid' => 'Filament\\Schemas\\Components\\Grid',
    
    // Tabs moved to Schemas
    'use Filament\\Forms\\Components\\Tabs;' => 'use Filament\\Schemas\\Components\\Tabs;',
    'Filament\\Forms\\Components\\Tabs' => 'Filament\\Schemas\\Components\\Tabs',
    
    // Tab moved to Schemas
    'use Filament\\Forms\\Components\\Tabs\\Tab;' => 'use Filament\\Schemas\\Components\\Tabs\\Tab;',
    'Filament\\Forms\\Components\\Tabs\\Tab' => 'Filament\\Schemas\\Components\\Tabs\\Tab',
    
    // Group moved to Schemas
    'use Filament\\Forms\\Components\\Group;' => 'use Filament\\Schemas\\Components\\Group;',
    'Filament\\Forms\\Components\\Group' => 'Filament\\Schemas\\Components\\Group',
    
    // Fieldset moved to Schemas
    'use Filament\\Forms\\Components\\Fieldset;' => 'use Filament\\Schemas\\Components\\Fieldset;',
    'Filament\\Forms\\Components\\Fieldset' => 'Filament\\Schemas\\Components\\Fieldset',
    
    // Placeholder moved to Schemas
    'use Filament\\Forms\\Components\\Placeholder;' => 'use Filament\\Schemas\\Components\\Placeholder;',
    'Filament\\Forms\\Components\\Placeholder' => 'Filament\\Schemas\\Components\\Placeholder',
    
    // Html moved to Schemas
    'use Filament\\Forms\\Components\\Html;' => 'use Filament\\Schemas\\Components\\Html;',
    'Filament\\Forms\\Components\\Html' => 'Filament\\Schemas\\Components\\Html',
    
    // Actions moved to Schemas
    'use Filament\\Forms\\Components\\Actions;' => 'use Filament\\Schemas\\Components\\Actions;',
    'Filament\\Forms\\Components\\Actions' => 'Filament\\Schemas\\Components\\Actions',
    
    // Actions\Action moved to Schemas
    'use Filament\\Forms\\Components\\Actions\\Action;' => 'use Filament\\Schemas\\Components\\Actions\\Action;',
    'Filament\\Forms\\Components\\Actions\\Action' => 'Filament\\Schemas\\Components\\Actions\\Action',
    
    // Actions\ActionGroup moved to Schemas
    'use Filament\\Forms\\Components\\Actions\\ActionGroup;' => 'use Filament\\Schemas\\Components\\Actions\\ActionGroup;',
    'Filament\\Forms\\Components\\Actions\\ActionGroup' => 'Filament\\Schemas\\Components\\Actions\\ActionGroup',
    
    // Actions\CreateAction moved to Schemas
    'use Filament\\Forms\\Components\\Actions\\CreateAction;' => 'use Filament\\Schemas\\Components\\Actions\\CreateAction;',
    'Filament\\Forms\\Components\\Actions\\CreateAction' => 'Filament\\Schemas\\Components\\Actions\\CreateAction',
    
    // Actions\DeleteAction moved to Schemas
    'use Filament\\Forms\\Components\\Actions\\DeleteAction;' => 'use Filament\\Schemas\\Components\\Actions\\DeleteAction;',
    'Filament\\Forms\\Components\\Actions\\DeleteAction' => 'Filament\\Schemas\\Components\\Actions\\DeleteAction',
    
    // Actions\EditAction moved to Schemas
    'use Filament\\Forms\\Components\\Actions\\EditAction;' => 'use Filament\\Schemas\\Components\\Actions\\EditAction;',
    'Filament\\Forms\\Components\\Actions\\EditAction' => 'Filament\\Schemas\\Components\\Actions\\EditAction',
    
    // Actions\ViewAction moved to Schemas
    'use Filament\\Forms\\Components\\Actions\\ViewAction;' => 'use Filament\\Schemas\\Components\\Actions\\ViewAction;',
    'Filament\\Forms\\Components\\Actions\\ViewAction' => 'Filament\\Schemas\\Components\\Actions\\ViewAction',
    
    // Actions\ReplicateAction moved to Schemas
    'use Filament\\Forms\\Components\\Actions\\ReplicateAction;' => 'use Filament\\Schemas\\Components\\Actions\\ReplicateAction;',
    'Filament\\Forms\\Components\\Actions\\ReplicateAction' => 'Filament\\Schemas\\Components\\Actions\\ReplicateAction',
    
    // Actions\AssociateAction moved to Schemas
    'use Filament\\Forms\\Components\\Actions\\AssociateAction;' => 'use Filament\\Schemas\\Components\\Actions\\AssociateAction;',
    'Filament\\Forms\\Components\\Actions\\AssociateAction' => 'Filament\\Schemas\\Components\\Actions\\AssociateAction',
    
    // Actions\DissociateAction moved to Schemas
    'use Filament\\Forms\\Components\\Actions\\DissociateAction;' => 'use Filament\\Schemas\\Components\\Actions\\DissociateAction;',
    'Filament\\Forms\\Components\\Actions\\DissociateAction' => 'Filament\\Schemas\\Components\\Actions\\DissociateAction',
    
    // Actions\DissociateBulkAction moved to Schemas
    'use Filament\\Forms\\Components\\Actions\\DissociateBulkAction;' => 'use Filament\\Schemas\\Components\\Actions\\DissociateBulkAction;',
    'Filament\\Forms\\Components\\Actions\\DissociateBulkAction' => 'Filament\\Schemas\\Components\\Actions\\DissociateBulkAction',
    
    // Actions\DissociateAllBulkAction moved to Schemas
    'use Filament\\Forms\\Components\\Actions\\DissociateAllBulkAction;' => 'use Filament\\Schemas\\Components\\Actions\\DissociateAllBulkAction;',
    'Filament\\Forms\\Components\\Actions\\DissociateAllBulkAction' => 'Filament\\Schemas\\Components\\Actions\\DissociateAllBulkAction',
    
    // Actions\AttachAction moved to Schemas
    'use Filament\\Forms\\Components\\Actions\\AttachAction;' => 'use Filament\\Schemas\\Components\\Actions\\AttachAction;',
    'Filament\\Forms\\Components\\Actions\\AttachAction' => 'Filament\\Schemas\\Components\\Actions\\AttachAction',
    
    // Actions\DetachAction moved to Schemas
    'use Filament\\Forms\\Components\\Actions\\DetachAction;' => 'use Filament\\Schemas\\Components\\Actions\\DetachAction;',
    'Filament\\Forms\\Components\\Actions\\DetachAction' => 'Filament\\Schemas\\Components\\Actions\\DetachAction',
    
    // Actions\DetachBulkAction moved to Schemas
    'use Filament\\Forms\\Components\\Actions\\DetachBulkAction;' => 'use Filament\\Schemas\\Components\\Actions\\DetachBulkAction;',
    'Filament\\Forms\\Components\\Actions\\DetachBulkAction' => 'Filament\\Schemas\\Components\\Actions\\DetachBulkAction',
    
    // Actions\DetachAllBulkAction moved to Schemas
    'use Filament\\Forms\\Components\\Actions\\DetachAllBulkAction;' => 'use Filament\\Schemas\\Components\\Actions\\DetachAllBulkAction;',
    'Filament\\Forms\\Components\\Actions\\DetachAllBulkAction' => 'Filament\\Schemas\\Components\\Actions\\DetachAllBulkAction',
    
    // Actions\AttachBulkAction moved to Schemas
    'use Filament\\Forms\\Components\\Actions\\AttachBulkAction;' => 'use Filament\\Schemas\\Components\\Actions\\AttachBulkAction;',
    'Filament\\Forms\\Components\\Actions\\AttachBulkAction' => 'Filament\\Schemas\\Components\\Actions\\AttachBulkAction',
    
    // Actions\AttachAllBulkAction moved to Schemas
    'use Filament\\Forms\\Components\\Actions\\AttachAllBulkAction;' => 'use Filament\\Schemas\\Components\\Actions\\AttachAllBulkAction;',
    'Filament\\Forms\\Components\\Actions\\AttachAllBulkAction' => 'Filament\\Schemas\\Components\\Actions\\AttachAllBulkAction',
];

// Get all PHP files in the app directory
$files = [];
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('app'));
foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $files[] = $file->getPathname();
    }
}

$fixedCount = 0;
$errorCount = 0;
$totalFiles = count($files);

echo "📁 Found $totalFiles PHP files to process...\n\n";

foreach ($files as $file) {
    $content = file_get_contents($file);
    $originalContent = $content;
    
    // Apply all import mappings
    foreach ($importMappings as $oldImport => $newImport) {
        $content = str_replace($oldImport, $newImport, $content);
    }
    
    if ($content !== $originalContent) {
        if (file_put_contents($file, $content)) {
            echo "✅ Fixed: $file\n";
            $fixedCount++;
        } else {
            echo "❌ Failed to write: $file\n";
            $errorCount++;
        }
    }
}

echo "\n📊 Summary:\n";
echo "✅ Files fixed: $fixedCount\n";
echo "❌ Errors: $errorCount\n";
echo "📁 Total files processed: $totalFiles\n";

echo "\n🎯 Next steps:\n";
echo "1. Run tests to verify the fixes\n";
echo "2. Check for any remaining import issues\n";
echo "3. Update any tests that might be affected\n";
echo "4. Test the application to ensure everything works\n";
