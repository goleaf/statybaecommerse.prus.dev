<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

use Illuminate\Support\Str;

$appDir = realpath(__DIR__.'/../app');
$baseDir = $appDir.'/Filament/Resources';
$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($baseDir, FilesystemIterator::SKIP_DOTS | FilesystemIterator::FOLLOW_SYMLINKS));

$errors = [];

foreach ($rii as $file) {
    if (! $file->isFile()) {
        continue;
    }
    if (strpos($file->getPathname(), '/Pages/') === false) {
        continue;
    }
    if (! str_ends_with($file->getFilename(), '.php')) {
        continue;
    }

    $path = $file->getPathname();
    $normPath = str_replace('\\', '/', $path);
    $rel = Str::after($normPath, str_replace('\\', '/', $appDir).'/');
    $class = 'App\\'.str_replace(['/', '.php'], ['\\', ''], $rel);

    require_once $path;
    if (! class_exists($class)) {
        $errors[] = [$class, 'class not found after include'];

        continue;
    }
    try {
        $res = $class::getResource();
        if (! is_string($res) || $res === '') {
            $errors[] = [$class, 'empty resource'];
        }
    } catch (Throwable $e) {
        $errors[] = [$class, $e->getMessage()];
    }
}

foreach ($errors as $e) {
    echo $e[0].' :: '.$e[1]."\n";
}

fwrite(STDERR, 'Errors: '.count($errors)."\n");
exit(count($errors) === 0 ? 0 : 1);
