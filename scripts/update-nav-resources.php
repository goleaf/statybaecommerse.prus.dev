<?php

declare(strict_types=1);
$directory = __DIR__ . '/../app/Filament/Resources';
$files = glob($directory . '/*Resource.php');
foreach ($files as $file) {
    $contents = file_get_contents($file);
    $original = $contents;

    if (strpos($contents, 'App\\Support\\Concerns\\HasNav') === false) {
        $contents = preg_replace('/(namespace App\\Filament\\Resources;\n)([\s\S]*?use )/m', "\\1use App\\Support\\Concerns\\HasNav;\n\\2", $contents, 1, $count);
        if ($count === 0) {
            // If there are no use statements yet, insert after namespace line.
            $contents = preg_replace('/(namespace App\\Filament\\Resources;\n)/', "\\1use App\\Support\\Concerns\\HasNav;\n\n", $contents, 1);
        }
    }

    // Remove existing navigation methods.
    $patterns = [
        '/\n\s*public static function getNavigationGroup[^\{]*\{[\s\S]*?\n\s*\}\n/m',
        '/\n\s*public static function getNavigationIcon[^\{]*\{[\s\S]*?\n\s*\}\n/m',
        '/\n\s*public static function getNavigationSort[^\{]*\{[\s\S]*?\n\s*\}\n/m',
    ];
    foreach ($patterns as $pattern) {
        $contents = preg_replace($pattern, "\n", $contents);
    }

    // Ensure class uses HasNav trait.
    if (strpos($contents, 'use HasNav;') === false) {
        $contents = preg_replace('/(class [^{]+\{\n)/', "\\1    use HasNav;\n\n", $contents, 1);
    }

    if ($contents !== $original) {
        file_put_contents($file, $contents);
    }
}
