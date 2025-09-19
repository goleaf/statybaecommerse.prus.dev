<?php

// Script to fix all remaining navigationGroup issues in Filament resources
// Fix the remaining broken files

$files = glob('app/Filament/Resources/**/*.php');

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $originalContent = $content;

        // Fix the remaining broken navigationGroup declarations
        $content = preg_replace(
            '/protected static string \| UnitEnum \| null \$navigationGroup = "Products";\s*;\s*/',
            'protected static string | UnitEnum | null $navigationGroup = "Products";',
            $content
        );

        $content = preg_replace(
            '/protected static string \| UnitEnum \| null \$navigationGroup = "Products";\s*;\s*/',
            'protected static string | UnitEnum | null $navigationGroup = "Products";',
            $content
        );

        // Fix other variations
        $content = preg_replace(
            '/protected static string \| UnitEnum \| null \$navigationGroup = \s*;\s*/',
            'protected static string | UnitEnum | null $navigationGroup = "Products";',
            $content
        );

        // Fix incomplete declarations
        $content = preg_replace(
            '/protected static string \| UnitEnum \| null \$navigationGroup = \s*$/',
            'protected static string | UnitEnum | null $navigationGroup = "Products";',
            $content
        );

        // Fix broken lines
        $content = preg_replace(
            '/protected static string \| UnitEnum \| null \$navigationGroup = \s*\n\s*;\s*/',
            'protected static string | UnitEnum | null $navigationGroup = "Products";',
            $content
        );

        if ($content !== $originalContent) {
            file_put_contents($file, $content);
            echo "Fixed: $file\n";
        }
    }
}

echo "All remaining resource fixes completed!\n";

