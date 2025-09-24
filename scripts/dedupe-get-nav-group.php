<?php

declare(strict_types=1);

$baseDir = __DIR__.'/../app/Filament/Resources';

$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($baseDir));
$updated = 0;

foreach ($rii as $file) {
    if (! $file->isFile() || $file->getExtension() !== 'php') {
        continue;
    }
    $path = $file->getPathname();
    $code = file_get_contents($path);
    if ($code === false) {
        continue;
    }
    $orig = $code;

    // Find all getNavigationGroup method blocks (very simple parser)
    $pattern = '/public\s+static\s+function\s+getNavigationGroup\s*\([^)]*\)\s*:[^{\n]*\{[\s\S]*?\n\}/m';
    if (preg_match_all($pattern, $code, $matches, PREG_OFFSET_CAPTURE)) {
        if (count($matches[0]) > 1) {
            // Keep the first, remove the rest
            // We will remove from later offsets backwards so positions stay valid
            for ($i = count($matches[0]) - 1; $i >= 1; $i--) {
                $match = $matches[0][$i];
                $start = $match[1];
                $length = strlen($match[0]);
                $code = substr($code, 0, $start).substr($code, $start + $length);
            }
        }
    }

    if ($code !== $orig) {
        file_put_contents($path, $code);
        $updated++;
        echo "Deduped getNavigationGroup in: {$path}\n";
    }
}

echo "Total deduped: {$updated}\n";
