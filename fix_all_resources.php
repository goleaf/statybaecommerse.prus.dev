<?php

// Script to fix all navigationGroup issues in Filament resources
// Fix the messed up sed replacements

$files = glob('app/Filament/Resources/**/*.php');

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $originalContent = $content;

        // Fix the messed up navigationGroup declarations
        $content = preg_replace(
            '/protected static string \| UnitEnum \| null \$navigationGroup \| UnitEnum \| protected static string \| UnitEnum \| null \$navigationGroup = /',
            'protected static string | UnitEnum | null $navigationGroup = ',
            $content
        );

        // Fix other variations
        $content = preg_replace(
            '/protected static string\|null \$navigationGroup/',
            'protected static string | UnitEnum | null $navigationGroup',
            $content
        );

        $content = preg_replace(
            '/protected static string|null \$navigationGroup/',
            'protected static string | UnitEnum | null $navigationGroup',
            $content
        );

        $content = preg_replace(
            '/protected static \$navigationGroup/',
            'protected static string | UnitEnum | null $navigationGroup',
            $content
        );

        // Add UnitEnum import if not already present and needed
        if (strpos($content, 'protected static string | UnitEnum | null $navigationGroup') !== false && strpos($content, 'use UnitEnum;') === false) {
            // Find the last use statement
            $lines = explode("\n", $content);
            $lastUseIndex = -1;
            for ($i = 0; $i < count($lines); $i++) {
                if (strpos($lines[$i], 'use ') === 0) {
                    $lastUseIndex = $i;
                }
            }

            if ($lastUseIndex !== -1) {
                $lines[$lastUseIndex + 1] = 'use UnitEnum;';
                $content = implode("\n", $lines);
            }
        }

        if ($content !== $originalContent) {
            file_put_contents($file, $content);
            echo "Fixed: $file\n";
        }
    }
}

echo "All resource fixes completed!\n";

