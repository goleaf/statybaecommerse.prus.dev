<?php

// Script to fix all Filament v4 compatibility issues

$resourcesDir = __DIR__ . '/app/Filament/Resources';
$files = glob($resourcesDir . '/*.php');

foreach ($files as $file) {
    $content = file_get_contents($file);
    
    // Skip if not a resource file
    if (!str_contains($content, 'extends Resource')) {
        continue;
    }
    
    $originalContent = $content;
    
    // Fix form method signature
    $content = preg_replace(
        '/public static function form\(Form \$form\): Form/',
        'public static function form(Schema $schema): Schema',
        $content
    );
    
    // Fix variable references inside form method
    $content = preg_replace(
        '/return \$form->schema\(/',
        'return $schema->schema(',
        $content
    );
    
    $content = preg_replace(
        '/return \$form->components\(/',
        'return $schema->components(',
        $content
    );
    
    // Add Schema import if not present
    if (str_contains($content, 'Schema $schema') && !str_contains($content, 'use Filament\\Schemas\\Schema')) {
        $content = preg_replace(
            '/(use Filament\\\\Tables\\\\Table;)/',
            '$1' . "\nuse Filament\\Schemas\\Schema;",
            $content
        );
    }
    
    // Fix navigationGroup type issues
    $content = preg_replace(
        '/protected static \?string \$navigationGroup/',
        '/** @var UnitEnum|string|null */' . "\n    protected static \$navigationGroup",
        $content
    );
    
    // Add UnitEnum import if needed
    if (str_contains($content, 'UnitEnum') && !str_contains($content, 'use UnitEnum;')) {
        $content = preg_replace(
            '/(use Filament\\\\Schemas\\\\Schema;)/',
            '$1' . "\nuse UnitEnum;",
            $content
        );
    }
    
    // Only write if content changed
    if ($content !== $originalContent) {
        file_put_contents($file, $content);
        echo "Fixed: " . basename($file) . "\n";
    }
}

echo "Done fixing Filament resources.\n";
