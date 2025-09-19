<?php

// Script to fix all navigationGroup type issues in Filament resources
// Convert to correct type: string | UnitEnum | null

$files = glob('app/Filament/Resources/**/*.php');

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $originalContent = $content;

        // Fix the type declaration
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

        // Add UnitEnum import if not already present
        if (strpos($content, 'use UnitEnum;') === false && strpos($content, 'protected static string | UnitEnum | null $navigationGroup') !== false) {
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

echo "All navigation group fixes completed!\n";
