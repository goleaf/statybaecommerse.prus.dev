<?php

declare(strict_types=1);

$baseDir = __DIR__.'/../app/Filament/Resources';

/**
 * @return array<string>
 */
function listPhpFiles(string $dir): array
{
    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
    $files = [];
    foreach ($rii as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $files[] = $file->getPathname();
        }
    }

    return $files;
}

$suspects = [];
foreach (listPhpFiles($baseDir) as $path) {
    // Only check Pages
    if (strpos($path, DIRECTORY_SEPARATOR.'Pages'.DIRECTORY_SEPARATOR) === false) {
        continue;
    }
    $code = file_get_contents($path);
    if ($code === false) {
        continue;
    }

    // Check if this file defines a class extending Filament\Resources\Pages\Page
    if (! preg_match('/class\s+\w+\s+extends\s+\?Filament\\\\Resources\\\\Pages\\\\\\w+/m', $code)) {
        continue;
    }

    // Does it have an initialized static $resource property?
    if (! preg_match('/protected\s+static\s+(?:string\s+)?\$resource\s*=\s*[^;]+;/', $code)) {
        $suspects[] = $path;
    }
}

foreach ($suspects as $file) {
    echo $file, "\n";
}

fwrite(STDERR, 'Total suspects: '.count($suspects)."\n");
exit(count($suspects) === 0 ? 0 : 1);
