<?php

declare(strict_types=1);

// Normalize $navigationIcon declarations to typed BackedEnum|string|null

$root = dirname(__DIR__);
$targets = [
    $root.'/app/Filament/Resources',
    $root.'/app/Filament/Pages',
    $root.'/app/Filament/Widgets',
];

function navIcon_getPhpFiles(string $dir): array
{
    if (! is_dir($dir)) {
        return [];
    }
    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    $files = [];
    foreach ($rii as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $files[] = $file->getPathname();
        }
    }

    return $files;
}

function navIcon_normalizeFile(string $path): bool
{
    $orig = file_get_contents($path);
    if ($orig === false) {
        return false;
    }
    $content = $orig;

    if (strpos($content, '$navigationIcon') === false) {
        return false;
    }

    // Ensure BackedEnum import present
    if (strpos($content, 'use BackedEnum;') === false) {
        $content = preg_replace('/(use\s+[^;]+;\s*\n)(?!.*use BackedEnum;)/', "\$1use BackedEnum;\n", $content, 1) ?? $content;
    }

    // Replace docblock + untyped property => typed union
    $content = preg_replace(
        '/(?:\s*\/\*\*[\s\S]*?@var\s+(?:string\?\||\?BackedEnum\?|\?BackedEnum\|string)\|?null[\s\S]*?\*\/\s*)?(\s*)protected\s+static\s+\$navigationIcon\s*=\s*([^;]+);/U',
        '$1protected static BackedEnum|string|null $navigationIcon = $2;',
        $content
    ) ?? $content;

    // Normalize variants like \BackedEnum|string|null
    $content = preg_replace(
        '/(\s*)protected\s+static\s+\?BackedEnum\|string\|null\s+\$navigationIcon\s*=\s*([^;]+);/',
        '$1protected static BackedEnum|string|null $navigationIcon = $2;',
        $content
    ) ?? $content;

    // Normalize wrong types like ?string or string
    $content = preg_replace(
        '/(\s*)protected\s+static\s+(?:\?string|string)\s+\$navigationIcon\s*=\s*([^;]+);/',
        '$1protected static BackedEnum|string|null $navigationIcon = $2;',
        $content
    ) ?? $content;

    if ($content !== $orig) {
        file_put_contents($path, $content);
        echo "Normalized navigationIcon: $path\n";

        return true;
    }

    return false;
}

$changed = 0;
foreach ($targets as $dir) {
    foreach (navIcon_getPhpFiles($dir) as $file) {
        if (navIcon_normalizeFile($file)) {
            $changed++;
        }
    }
}

echo "\nFiles changed: $changed\n";
