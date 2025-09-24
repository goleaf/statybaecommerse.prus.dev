<?php

declare(strict_types=1);
$base = __DIR__.'/app/Filament/Resources';
$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base));
$bad = [];
$validExtends = [
    'ListRecords', 'CreateRecord', 'EditRecord', 'ViewRecord', 'ManageRelatedRecords',
    'ManageRelatedPage', 'ManageRecords', 'RelationManager', 'Page',
];
foreach ($rii as $file) {
    if ($file->isDir()) {
        continue;
    }
    if (pathinfo($file->getFilename(), PATHINFO_EXTENSION) !== 'php') {
        continue;
    }
    $code = file_get_contents($file->getPathname());
    if ($code === false) {
        continue;
    }
    if (! preg_match('/class\s+([A-Za-z0-9_]+)\s+extends\s+([A-Za-z0-9_\\]+)/', $code, $m)) {
        continue;
    }
    $extends = $m[2];
    $short = str_contains($extends, '\\') ? substr($extends, strrpos($extends, '\\') + 1) : $extends;
    if (! in_array($short, $validExtends, true)) {
        continue;
    }
    if (! str_contains($file->getPathname(), DIRECTORY_SEPARATOR.'Pages'.DIRECTORY_SEPARATOR)) {
        continue;
    }
    if (! preg_match('/protected\s+static\s+string\s+\$resource\s*=\s*[^;]+;/', $code)) {
        $bad[] = $file->getPathname().' (extends '.$short.') missing protected static string $resource';
    }
}
if (empty($bad)) {
    echo "OK\n";
} else {
    sort($bad);
    echo implode("\n", $bad), "\n";
}
