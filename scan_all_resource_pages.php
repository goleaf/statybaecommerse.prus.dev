<?php

declare(strict_types=1);
require __DIR__.'/vendor/autoload.php';

$base = __DIR__.'/app';
$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base));
$bad = [];
$checked = 0;
foreach ($rii as $file) {
    if ($file->isDir()) {
        continue;
    }
    if (! str_ends_with($file->getFilename(), '.php')) {
        continue;
    }
    $code = file_get_contents($file->getPathname());
    if ($code === false) {
        continue;
    }
    if (! preg_match('/^namespace\s+([^;]+);/m', $code, $nm)) {
        continue;
    }
    if (! preg_match('/class\s+([A-Za-z0-9_]+)/', $code, $cm)) {
        continue;
    }
    $class = $nm[1].'\\'.$cm[1];
    try {
        if (! class_exists($class)) {
            continue;
        }
        if (! is_subclass_of($class, \Filament\Resources\Pages\Page::class)) {
            continue;
        }
        $checked++;
        try {
            $rp = new ReflectionProperty($class, 'resource');
            if ($rp->isStatic() && ! $rp->isInitialized()) {
                $bad[] = $class.' -> '.$file->getPathname();

                continue;
            }
            // Also try calling getResource()
            $class::getResource();
        } catch (Throwable $e) {
            $bad[] = $class.' -> '.$file->getPathname().' :: '.get_class($e).' :: '.$e->getMessage();
        }
    } catch (Throwable $e) {
        // ignore classes that cannot be loaded
    }
}
if (empty($bad)) {
    echo "OK checked $checked\n";
} else {
    sort($bad);
    echo implode("\n", $bad), "\n";
}
