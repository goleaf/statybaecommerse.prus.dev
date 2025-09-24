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
    // Only process widget classes
    if (strpos($path, '/Widgets/') === false) {
        continue;
    }
    $orig = $code;

    // Replace static heading property with non-static
    $code = preg_replace('/protected\s+static\s+\?string\s+\$heading\s*=/', 'protected ?string $heading =', $code);
    $code = preg_replace('/protected\s+static\s+\$heading\s*=/', 'protected $heading =', $code);

    if ($code !== $orig) {
        file_put_contents($path, $code);
        $updated++;
        echo "Fixed heading in: {$path}\n";
    }
}

echo "Total widgets updated: {$updated}\n";
