<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

$appDir = realpath(__DIR__.'/../app');
$baseDir = $appDir.'/Filament/Resources';

$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($baseDir, FilesystemIterator::SKIP_DOTS | FilesystemIterator::FOLLOW_SYMLINKS));

$missing = [];

foreach ($rii as $file) {
    if (! $file->isFile()) {
        continue;
    }
    if (strpos($file->getPathname(), '/Pages/') === false) {
        continue;
    }
    if (substr($file->getFilename(), -4) !== '.php') {
        continue;
    }

    $path = $file->getPathname();
    $normPath = str_replace('\\', '/', $path);
    $relFromApp = ltrim(str_replace(str_replace('\\', '/', $appDir).'/', '', $normPath), '/');
    // e.g., Filament/Resources/PartnerResource/Pages/EditPartner.php
    $class = 'App\\'.str_replace(['/', '.php'], ['\\', ''], $relFromApp);

    if (! class_exists($class)) {
        require_once $path;
    }
    if (! class_exists($class)) {
        continue;
    }

    try {
        $rc = new ReflectionClass($class);
    } catch (Throwable $e) {
        fwrite(STDERR, "Reflection failed for {$class}: {$e->getMessage()}\n");

        continue;
    }

    // Only consider Filament Resource Page subclasses
    $isResourcePage = false;
    foreach ([
        '\Filament\Resources\Pages\CreateRecord',
        '\Filament\Resources\Pages\EditRecord',
        '\Filament\Resources\Pages\ListRecords',
        '\Filament\Resources\Pages\ViewRecord',
        '\Filament\Resources\Pages\ManageRelatedRecords',
        '\Filament\Resources\Pages\Page',
    ] as $parent) {
        if ($rc->isSubclassOf(ltrim($parent, '\\'))) {
            $isResourcePage = true;
            break;
        }
    }
    if (! $isResourcePage) {
        continue;
    }

    if (! $rc->hasProperty('resource')) {
        $missing[] = [$class, 'no property'];

        continue;
    }
    $prop = $rc->getProperty('resource');
    if (! $prop->isStatic()) {
        $missing[] = [$class, 'not static'];

        continue;
    }
    if ($prop->getDeclaringClass()->getName() !== $class) {
        $missing[] = [$class, 'inherited'];

        continue;
    }
}

foreach ($missing as $row) {
    echo $row[0].' :: '.$row[1]."\n";
}

fwrite(STDERR, 'Total missing: '.count($missing)."\n");
exit(count($missing) === 0 ? 0 : 1);
