<?php

// Script to fix remaining Filament RelationManager syntax errors

$files = glob('/www/wwwroot/statybaecommerse.prus.dev/app/Filament/**/*RelationManager.php');

foreach ($files as $file) {
    $content = file_get_contents($file);
    $originalContent = $content;
    
    // Fix missing opening braces for form methods
    $content = preg_replace(
        '/public function form\([^)]+\): [^{]*\n\s*return/',
        "public function form(\\0 {\n        return",
        $content
    );
    
    // Fix missing opening braces for table methods
    $content = preg_replace(
        '/public function table\([^)]+\): [^{]*\n\s*return/',
        "public function table(\\0 {\n        return",
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
    
    // Fix extra closing brackets
    $content = preg_replace(
        '/\]\);\s*\n\s*}\s*\n\s*\]\);\s*\n\s*}/',
        "]);\n    }\n}",
        $content
    );
    
    if ($content !== $originalContent) {
        file_put_contents($file, $content);
        echo "Fixed: $file\n";
    }
}

echo "Done fixing remaining syntax errors.\n";


