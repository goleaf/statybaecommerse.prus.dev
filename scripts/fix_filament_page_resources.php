<?php

declare(strict_types=1);

$baseDir = __DIR__.'/../app/Filament/Resources';

/** Recursively iterate over Pages/*.php and ensure the static $resource property exists. */
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($baseDir, FilesystemIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

$updated = 0;

foreach ($iterator as $file) {
    if (! $file->isFile()) {
        continue;
    }

    $path = $file->getPathname();

    // Only target page classes under a Pages directory
    if (! preg_match('~/Pages/[^/]+\.php$~', $path)) {
        continue;
    }

    $code = file_get_contents($path);
    if ($code === false) {
        continue;
    }

    // Skip if property already defined
    if (preg_match('/protected\s+static\s+(?:string\s+)?\$resource\s*=\s*[^;]+;/', $code)) {
        continue;
    }

    // Determine the Resource short class name from import or path
    $resourceShort = null;

    if (preg_match('/^use\s+App\\\\Filament\\\\Resources\\\\([A-Za-z0-9_\\\\]+Resource);/m', $code, $m)) {
        $parts = explode('\\\\', $m[1]);
        $resourceShort = end($parts).'Resource';
    }

    if ($resourceShort === null) {
        // Fallback: infer from directory name before /Pages
        // e.g. .../Resources/CustomerResource/Pages/EditCustomer.php
        $segments = explode(DIRECTORY_SEPARATOR, $path);
        $pagesIndex = array_search('Pages', $segments, true);
        if ($pagesIndex !== false && $pagesIndex > 0) {
            $resourceShort = $segments[$pagesIndex - 1];
        }
    }

    if (! $resourceShort || ! preg_match('/class\s+\w+\s+extends\s+[^\{]+\{/', $code) && ! preg_match('/class\s+\w+\s+extends\s+[^\n]+\n\{/', $code)) {
        // Can't safely determine insertion point or resource; skip
        continue;
    }

    // Insert the property after the opening brace of the class
    $newCode = preg_replace(
        // Match class declaration and the first opening brace
        '/(class\s+\w+\s+extends\s+[^\{]+\{\s*)/m',
        "\$1\n\tprotected static string \$resource = {$resourceShort}::class;\n",
        $code,
        1,
        $count
    );

    if ($count === 0) {
        // Alternate style where brace is on the next line
        $newCode = preg_replace(
            '/(class\s+\w+\s+extends\s+[^\n]+\n)\{\s*/m',
            "\$1{\n\tprotected static string \$resource = {$resourceShort}::class;\n",
            $code,
            1,
            $count
        );
    }

    if ($count > 0 && $newCode !== null) {
        file_put_contents($path, $newCode);
        $updated++;

        continue;
    }
}

fwrite(STDOUT, "Updated pages: {$updated}\n");
exit(0);
