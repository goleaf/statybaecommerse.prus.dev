<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

it('guards missing localized route references in runtime code', function (): void {
    $roots = [
        base_path('app'),
        base_path('resources'),
        base_path('routes'),
    ];

    $violations = [];
    $pattern = '/route\(\s*[\'\"](?<name>localized\.[A-Za-z0-9_.-]+)[\'\"]/';

    foreach ($roots as $root) {
        if (! is_dir($root)) {
            continue;
        }

        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root));

        foreach ($iterator as $file) {
            if (! $file->isFile()) {
                continue;
            }

            $path = $file->getPathname();
            $filename = $file->getFilename();

            if (! str_ends_with($path, '.php') && ! str_ends_with($filename, '.blade.php')) {
                continue;
            }

            $content = @file_get_contents($path);

            if (! is_string($content) || $content === '') {
                continue;
            }

            if (! preg_match_all($pattern, $content, $matches, PREG_OFFSET_CAPTURE)) {
                continue;
            }

            foreach ($matches['name'] as [$routeName, $offset]) {
                if (! is_string($routeName) || $routeName === '') {
                    continue;
                }

                if (Route::has($routeName)) {
                    continue;
                }

                $guardPattern = sprintf('/Route::has\(\s*[\'\"]%s[\'\"]\s*\)/', preg_quote($routeName, '/'));

                if (preg_match($guardPattern, $content) === 1) {
                    continue;
                }

                $line = substr_count(substr($content, 0, (int) $offset), "\n") + 1;
                $relativePath = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $path);

                $violations[] = sprintf(
                    '%s:%d uses missing route [%s] without Route::has guard',
                    $relativePath,
                    $line,
                    $routeName
                );
            }
        }
    }

    expect($violations)->toBe([], implode(PHP_EOL, $violations));
});