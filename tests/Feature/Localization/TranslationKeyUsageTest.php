<?php

declare(strict_types=1);

use Illuminate\Filesystem\Filesystem;

it('feature: does not use inline notification strings', function (): void {
    $filesystem = new Filesystem();
    $invalidUsages = [];

    foreach ($filesystem->allFiles(app_path()) as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }

        $contents = $filesystem->get($file->getPathname());

        if (preg_match_all('/Notification::make\(\)->title\(\s*([\'\"])([^\'\"]+)\1/', $contents, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $invalidUsages[] = sprintf('%s: %s', $file->getRelativePathname(), $match[2]);
            }
        }

        if (preg_match_all('/Notification::make\(\)->body\(\s*([\'\"])([^\'\"]+)\1/', $contents, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $invalidUsages[] = sprintf('%s: %s', $file->getRelativePathname(), $match[2]);
            }
        }
    }

    expect($invalidUsages)->toBeEmpty();
});
