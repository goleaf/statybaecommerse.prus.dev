<?php

declare(strict_types=1);

use Filament\Resources\Pages\Page;
use Illuminate\Filesystem\Filesystem;

it('ensures Filament resource pages can resolve their resource classes', function () {
    $filesystem = new Filesystem;
    $resourcesPath = app_path('Filament/Resources');

    if (! $filesystem->isDirectory($resourcesPath)) {
        $this->markTestSkipped('No Filament resources defined.');
    }

    $issues = [];

    foreach ($filesystem->allFiles($resourcesPath) as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }

        $code = $filesystem->get($file->getPathname());

        if (! preg_match('/^namespace\s+([^;]+);/m', $code, $namespaceMatch)) {
            continue;
        }

        if (! preg_match('/class\s+([A-Za-z0-9_]+)/', $code, $classMatch)) {
            continue;
        }

        $class = $namespaceMatch[1] . '\\' . $classMatch[1];

        if (! class_exists($class) || ! is_subclass_of($class, Page::class)) {
            continue;
        }

        try {
            $property = new \ReflectionProperty($class, 'resource');
            if ($property->isStatic() && ! $property->isInitialized()) {
                $issues[] = $class . ' has an uninitialised static $resource property (' . $file->getPathname() . ').';

                continue;
            }
        } catch (\ReflectionException $exception) {
            $issues[] = $class . ' is missing the static $resource property: ' . $exception->getMessage();

            continue;
        }

        try {
            $class::getResource();
        } catch (\Throwable $exception) {
            $issues[] = $class . '::getResource() failed: ' . $exception->getMessage();
        }
    }

    expect($issues, 'The following Filament page classes are misconfigured: ' . implode(', ', $issues))->toBeEmpty();
});
