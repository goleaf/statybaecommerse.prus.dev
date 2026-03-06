<?php

declare(strict_types=1);

it('does not reference removed frontend legal route names in runtime code', function (): void {
    $roots = [
        base_path('app'),
        base_path('resources'),
        base_path('routes'),
    ];

    $violations = [];
    $pattern = '/(?:route|Route::has)\(\s*[\'"]frontend\.legal\.[^\'"]*[\'"]/';

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

            foreach ($matches[0] as [$match, $offset]) {
                $line = substr_count(substr($content, 0, (int) $offset), "\n") + 1;
                $relativePath = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $path);

                $violations[] = sprintf('%s:%d contains "%s"', $relativePath, $line, $match);
            }
        }
    }

    expect($violations)->toBe([], implode(PHP_EOL, $violations));
});

