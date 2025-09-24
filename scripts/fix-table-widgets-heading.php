<?php

declare(strict_types=1);

$baseDir = __DIR__.'/../app/Filament';

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
    if (strpos($path, '/Widgets/') === false) {
        continue;
    }
    $orig = $code;

    // Determine if class extends TableWidget (directly or via alias BaseWidget)
    $isTableWidget = (bool) (preg_match('/extends\s+\\?Filament\\\\Widgets\\\\TableWidget\b/', $code)
        || (preg_match('/use\s+Filament\\\\Widgets\\\\TableWidget\s+as\s+([A-Za-z_][A-Za-z0-9_]*)\s*;/', $code, $m) && preg_match('/extends\s+'.preg_quote($m[1], '/').'\b/', $code))
        || preg_match('/extends\s+TableWidget\b/', $code));

    if (! $isTableWidget) {
        continue;
    }

    // Ensure heading is static for TableWidget descendants
    $code = preg_replace('/protected\s+\?string\s+\$heading\s*=/', 'protected static ?string $heading =', $code);
    $code = preg_replace('/protected\s+\$heading\s*=/', 'protected static $heading =', $code);

    if ($code !== $orig) {
        file_put_contents($path, $code);
        $updated++;
        echo "Fixed TableWidget heading in: {$path}\n";
    }
}

echo "Total table widgets updated: {$updated}\n";
