<?php

// Final script to fix all remaining navigationGroup issues
// Fix all broken files with comprehensive patterns

$files = glob('app/Filament/Resources/**/*.php');

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $originalContent = $content;

        // Fix all remaining broken patterns
        $patterns = [
            '/protected static string \| UnitEnum \| null \$navigationGroup = "Products";\s*;\s*/' => 'protected static string | UnitEnum | null $navigationGroup = "Products";',
            '/protected static string \| UnitEnum \| null \$navigationGroup = "Products";\s*;\s*/' => 'protected static string | UnitEnum | null $navigationGroup = "Products";',
            '/protected static string \| UnitEnum \| null \$navigationGroup = \s*;\s*/' => 'protected static string | UnitEnum | null $navigationGroup = "Products";',
            '/protected static string \| UnitEnum \| null \$navigationGroup = \s*\n\s*;\s*/' => 'protected static string | UnitEnum | null $navigationGroup = "Products";',
            '/protected static string \| UnitEnum \| null \$navigationGroup = \s*$/m' => 'protected static string | UnitEnum | null $navigationGroup = "Products";',
            '/protected static string \| UnitEnum \| null \$navigationGroup = \s*\n\s*;\s*/' => 'protected static string | UnitEnum | null $navigationGroup = "Products";',
        ];

        foreach ($patterns as $pattern => $replacement) {
            $content = preg_replace($pattern, $replacement, $content);
        }

        // Fix any remaining broken lines
        $content = preg_replace(
            '/protected static string \| UnitEnum \| null \$navigationGroup = \s*\n\s*;\s*/',
            'protected static string | UnitEnum | null $navigationGroup = "Products";',
            $content
        );

        // Fix any remaining broken lines with different patterns
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

echo "All final resource fixes completed!\n";
