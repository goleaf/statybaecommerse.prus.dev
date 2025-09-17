<?php

// Script to fix common Filament RelationManager syntax errors

$files = glob('/www/wwwroot/statybaecommerse.prus.dev/app/Filament/**/*RelationManager.php');

foreach ($files as $file) {
    $content = file_get_contents($file);
    $originalContent = $content;
    
    // Fix missing opening braces for table methods
    $content = preg_replace(
        '/public function table\(Table \$table\): Table\s*\n\s*return \$table/',
        "public function table(Table \$table): Table\n    {\n        return \$table",
        $content
    );
    
    // Fix missing commas in form components
    $content = preg_replace(
        '/(Forms\\\\Components\\\\[A-Za-z]+::make\([^)]+\)[^,]*)\n\s*(Forms\\\\Components\\\\[A-Za-z]+::make)/',
        "$1,\n                $2",
        $content
    );
    
    // Fix missing commas in table columns
    $content = preg_replace(
        '/(Tables\\\\Columns\\\\[A-Za-z]+::make\([^)]+\)[^,]*)\n\s*(Tables\\\\Columns\\\\[A-Za-z]+::make)/',
        "$1,\n                $2",
        $content
    );
    
    // Fix missing closing brackets for filters
    $content = preg_replace(
        '/(->filters\(\[[^\]]*\])\s*\n\s*(->headerActions)/',
        "$1\n            ])\n            $2",
        $content
    );
    
    // Fix missing closing brackets for headerActions
    $content = preg_replace(
        '/(->headerActions\(\[[^\]]*\])\s*\n\s*(->actions)/',
        "$1\n            ])\n            $2",
        $content
    );
    
    // Fix missing closing brackets for actions
    $content = preg_replace(
        '/(->actions\(\[[^\]]*\])\s*\n\s*(->bulkActions)/',
        "$1\n            ])\n            $2",
        $content
    );
    
    // Fix missing closing brackets for bulkActions
    $content = preg_replace(
        '/(->bulkActions\(\[[^\]]*\])\s*\n\s*(->modifyQueryUsing)/',
        "$1\n            ])\n            $2",
        $content
    );
    
    // Fix missing closing brace for table method
    $content = preg_replace(
        '/(->modifyQueryUsing\([^)]+\));\s*\n\s*}\s*\n\s*}/',
        "$1);\n    }\n}",
        $content
    );
    
    if ($content !== $originalContent) {
        file_put_contents($file, $content);
        echo "Fixed: $file\n";
    }
}

echo "Done fixing Filament syntax errors.\n";

