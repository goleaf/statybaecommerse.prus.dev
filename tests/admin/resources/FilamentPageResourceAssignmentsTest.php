<?php

declare(strict_types=1);

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

$validPageAncestors = [
    'ListRecords',
    'CreateRecord',
    'EditRecord',
    'ViewRecord',
    'ManageRelatedRecords',
    'ManageRelatedPage',
    'ManageRecords',
    'RelationManager',
    'Page',
];

test('each Filament resource page declares its resource property', function () use ($validPageAncestors) {
    $filesystem = new Filesystem;
    $resourcesPath = app_path('Filament/Resources');

    if (! $filesystem->isDirectory($resourcesPath)) {
        $this->markTestSkipped('No Filament resources defined.');
    }

    $missingResourceProperty = [];

    foreach ($filesystem->allFiles($resourcesPath) as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }

        if (! Str::contains($file->getPathname(), DIRECTORY_SEPARATOR . 'Pages' . DIRECTORY_SEPARATOR)) {
            continue;
        }

        $code = $filesystem->get($file->getPathname());

        if (! preg_match('/class\s+([A-Za-z0-9_]+)\s+extends\s+([A-Za-z0-9_\\]+)/', $code, $matches)) {
            continue;
        }

        $extends = Str::afterLast($matches[2], '\\');
        if (! in_array($extends, $validPageAncestors, true)) {
            continue;
        }

        if (! Str::contains($code, 'protected static string $resource')) {
            $missingResourceProperty[] = $file->getPathname();
        }
    }

    expect($missingResourceProperty, 'The following page classes do not declare a protected static string $resource property: ' . implode(', ', $missingResourceProperty))
        ->toBeEmpty();
});
