<?php

declare(strict_types=1);

// Normalizes all $navigationGroup declarations across app/Filament/** to typed UnitEnum|string|null

$root = dirname(__DIR__);
$targets = [
    $root.'/app/Filament/Resources',
    $root.'/app/Filament/Pages',
    $root.'/app/Filament/Widgets',
];

function getPhpFiles(string $dir): array
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

function normalizeFile(string $path): bool
{
    $orig = file_get_contents($path);
    if ($orig === false) {
        return false;
    }
    $content = $orig;

    // Ensure import present
    if (strpos($content, 'use UnitEnum;') === false) {
        $content = preg_replace('/(use\s+[^;]+;\s*\n)(?!.*use UnitEnum;)/', "\$1use UnitEnum;\n", $content, 1) ?? $content;
    }

    // Remove any @var UnitEnum|string|null docblock directly above property and convert to typed property
    $content = preg_replace(
        '/(?:\s*\/\*\*[\s\S]*?@var\s+UnitEnum\|string\|null[\s\S]*?\*\/\s*)?(\s*)protected\s+static\s+\$navigationGroup\s*=\s*([^;]+);/U',
        '$1protected static UnitEnum|string|null $navigationGroup = $2;',
        $content
    ) ?? $content;

    // Normalize typed variants with leading backslash
    $content = preg_replace(
        '/(\s*)protected\s+static\s+\?UnitEnum\|string\|null\s+\$navigationGroup\s*=\s*([^;]+);/',
        '$1protected static UnitEnum|string|null $navigationGroup = $2;',
        $content
    ) ?? $content;

    // Deduplicate multiple typed declarations (keep the first)
    $lines = explode("\n", $content);
    $seen = false;
    for ($i = 0; $i < count($lines); $i++) {
        if (preg_match('/^\s*protected\s+static\s+UnitEnum\|string\|null\s+\$navigationGroup\s*=\s*[^;]+;\s*$/', $lines[$i])) {
            if ($seen) {
                // Remove duplicates
                $lines[$i] = '';
            } else {
                $seen = true;
            }
        }
        // Remove stray docblock lines that may have been left behind
        if (preg_match('/@var\s+UnitEnum\|string\|null/', $lines[$i])) {
            $lines[$i] = '';
            // Also attempt to remove surrounding /** and */ one-liners
            if ($i > 0 && preg_match('/^\s*\/\*\*\s*$/', $lines[$i - 1])) {
                $lines[$i - 1] = '';
            }
            if ($i + 1 < count($lines) && preg_match('/^\s*\*\/\s*$/', $lines[$i + 1])) {
                $lines[$i + 1] = '';
            }
        }
    }
    $content = implode("\n", $lines);

    if ($content !== $orig) {
        file_put_contents($path, $content);
        echo 'Normalized: '.$path."\n";

        return true;
    }

    return false;
}

$changed = 0;
foreach ($targets as $dir) {
    foreach (getPhpFiles($dir) as $file) {
        if (strpos(file_get_contents($file), '$navigationGroup') !== false) {
            if (normalizeFile($file)) {
                $changed++;
            }
        }
    }
}

echo "\nFiles changed: $changed\n";
