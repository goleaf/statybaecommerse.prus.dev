<?php

declare(strict_types=1);

$baseDir = __DIR__.'/../app/Filament/Resources';

$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($baseDir));
$updated = 0;

foreach ($rii as $file) {
    if (! $file->isFile() || $file->getExtension() !== 'php') {
        continue;
    }
    $path = $file->getPathname();
    $code = file_get_contents($path);
    if ($code === false) {
        continue;
    }
    $orig = $code;

    // Capture existing navigation group value if property exists (typed or untyped)
    $navGroupValue = null;
    if (preg_match('/protected\s+static\s+[^$]*\$navigationGroup\s*=\s*([^;]+);/m', $code, $m)) {
        $navGroupValue = trim($m[1]);
        // Remove the property declaration entirely
        $code = preg_replace('/\n?\s*protected\s+static\s+[^$]*\$navigationGroup\s*=\s*[^;]+;\s*/m', "\n", $code, 1);
    }

    // Ensure UnitEnum import if method will be added
    if ($navGroupValue !== null && strpos($code, 'getNavigationGroup(): UnitEnum|string|null') === false) {
        if (strpos($code, 'use UnitEnum;') === false) {
            // Insert after last use statement
            if (preg_match('/^(.*?namespace[^;]+;\s*)((?:use[^;]+;\s*)*)/s', $code, $mm)) {
                $code = preg_replace('/^(.*?namespace[^;]+;\s*)((?:use[^;]+;\s*)*)/s', '$1$2'."use UnitEnum;\n", $code, 1);
            }
        }
        // Insert method after class opening brace
        $code = preg_replace('/(final\s+class\s+[^\{]+\{)/', "$1\n\n    public static function getNavigationGroup(): UnitEnum|string|null\n    {\n        return {$navGroupValue};\n    }\n", $code, 1);
    }

    if ($code !== $orig) {
        file_put_contents($path, $code);
        $updated++;
        echo "Updated navigation group in: {$path}\n";
    }
}

echo "Total updated: {$updated}\n";
