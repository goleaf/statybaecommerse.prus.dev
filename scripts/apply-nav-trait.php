<?php
$directory = new RecursiveDirectoryIterator(__DIR__ . '/../app/Filament/Resources', RecursiveDirectoryIterator::SKIP_DOTS);
$iterator = new RecursiveIteratorIterator($directory);
$files = [];
foreach ($iterator as $file) {
    if ($file->isFile() && str_ends_with($file->getFilename(), 'Resource.php')) {
        $files[] = $file->getPathname();
    }
}

function removeMethod(string $contents, string $methodName): string
{
    while (($pos = strpos($contents, 'public static function ' . $methodName)) !== false) {
        $start = $pos;
        $bracePos = strpos($contents, '{', $start);
        if ($bracePos === false) {
            break;
        }
        $depth = 0;
        $len = strlen($contents);
        for ($i = $bracePos; $i < $len; $i++) {
            $char = $contents[$i];
            if ($char === '{') {
                $depth++;
            } elseif ($char === '}') {
                $depth--;
                if ($depth === 0) {
                    $i++;
                    break;
                }
            }
        }
        $end = $i;
        $contents = substr($contents, 0, $start) . substr($contents, $end);
    }

    return $contents;
}

function ensureTraitUsage(string $contents): string
{
    if (strpos($contents, 'use HasNav;') !== false) {
        return $contents;
    }
    $classPos = strpos($contents, 'class ');
    if ($classPos === false) {
        return $contents;
    }
    $bracePos = strpos($contents, '{', $classPos);
    if ($bracePos === false) {
        return $contents;
    }
    $bracePos++;

    return substr($contents, 0, $bracePos) . "\n    use HasNav;\n" . substr($contents, $bracePos);
}

foreach ($files as $file) {
    $contents = file_get_contents($file);
    $original = $contents;

    if (strpos($contents, 'App\\Support\\Concerns\\HasNav') === false) {
        $needle = 'namespace App\\Filament\\Resources';
        if (($pos = strpos($contents, $needle)) !== false) {
            $endOfNamespace = strpos($contents, "\n", $pos + strlen($needle));
            $insertPos = $endOfNamespace !== false ? $endOfNamespace + 1 : $pos + strlen($needle);
            $contents = substr($contents, 0, $insertPos)
                . "use App\\Support\\Concerns\\HasNav;\n"
                . substr($contents, $insertPos);
        }
    }

    $contents = removeMethod($contents, 'getNavigationGroup');
    $contents = removeMethod($contents, 'getNavigationIcon');
    $contents = removeMethod($contents, 'getNavigationSort');

    $contents = ensureTraitUsage($contents);

    if ($contents !== $original) {
        file_put_contents($file, $contents);
    }
}
