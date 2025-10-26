<?php

declare(strict_types=1);

$directory = new RecursiveDirectoryIterator(__DIR__ . '/../app/Filament', FilesystemIterator::SKIP_DOTS);
$iterator = new RecursiveIteratorIterator($directory);

foreach ($iterator as $fileInfo) {
    if ($fileInfo->getExtension() !== 'php') {
        continue;
    }

    $path = $fileInfo->getPathname();
    $code = file_get_contents($path);
    $tokens = token_get_all($code);

    $output = '';
    $length = count($tokens);

    $state = null;
    $paramDepth = 0;
    $returnSkipping = false;
    $braceDepth = 0;
    $currentFunction = null;

    $usesFormIdentifier = false;
    $usesInfolistIdentifier = false;

    for ($index = 0; $index < $length; $index++) {
        $token = $tokens[$index];
        $text = is_array($token) ? $token[1] : $token;
        $id = is_array($token) ? $token[0] : null;

        if ($id === T_STRING && $text === 'Form') {
            $usesFormIdentifier = true;
        }

        if ($id === T_STRING && $text === 'Infolist') {
            $usesInfolistIdentifier = true;
        }

        if ($id === T_FUNCTION) {
            $state = 'function';
            $currentFunction = null;
            $output .= $text;

            continue;
        }

        if ($state === 'function') {
            $output .= $text;

            if ($id === T_STRING) {
                $name = $text;

                if ($name === 'form') {
                    $currentFunction = 'form';
                    $state = 'params';

                    continue;
                }

                if ($name === 'infolist') {
                    $currentFunction = 'infolist';
                    $state = 'params';

                    continue;
                }

                if ($name === 'table') {
                    $currentFunction = 'table';
                    $state = 'params';

                    continue;
                }

                $state = null;
            }

            continue;
        }

        if ($state === 'params') {
            if ($text === '(') {
                $paramDepth++;
                $output .= $text;

                continue;
            }

            if ($text === ')') {
                $paramDepth--;
                $output .= $text;

                if ($paramDepth === 0) {
                    $state = 'return';
                }

                continue;
            }

            if ($paramDepth > 0) {
                if ($currentFunction === 'form' && $id === T_STRING && trim($text) === 'Form') {
                    $output .= 'Schema';

                    continue;
                }

                if ($currentFunction === 'infolist' && $id === T_STRING && trim($text) === 'Infolist') {
                    $output .= 'Schema';

                    continue;
                }

                if (($currentFunction === 'form' || $currentFunction === 'infolist') && $id === T_VARIABLE && $text === '$form') {
                    $output .= '$schema';

                    continue;
                }

                if (($currentFunction === 'infolist') && $id === T_VARIABLE && $text === '$infolist') {
                    $output .= '$schema';

                    continue;
                }
            }

            $output .= $text;

            continue;
        }

        if ($state === 'return') {
            if ($text === '{') {
                $state = 'body';
                $braceDepth = 1;
                $output .= $text;

                continue;
            }

            if ($text === ':') {
                if ($currentFunction === 'form' || $currentFunction === 'infolist') {
                    $output .= ': Schema';
                } elseif ($currentFunction === 'table') {
                    $output .= ': Table';
                } else {
                    $output .= $text;
                }

                $returnSkipping = true;

                continue;
            }

            if ($returnSkipping) {
                if (trim($text) === '') {
                    // preserve whitespace after colon if necessary
                    if ($currentFunction !== null) {
                        $output .= $text;
                    }

                    continue;
                }

                if ($text === '{') {
                    $state = 'body';
                    $braceDepth = 1;
                    $output .= $text;
                    $returnSkipping = false;

                    continue;
                }

                // Skip original return type tokens
                continue;
            }

            $output .= $text;

            continue;
        }

        if ($state === 'body') {
            if ($text === '{') {
                $braceDepth++;
                $output .= $text;

                continue;
            }

            if ($text === '}') {
                $braceDepth--;
                $output .= $text;

                if ($braceDepth === 0) {
                    $state = null;
                    $currentFunction = null;
                }

                continue;
            }

            if ($currentFunction === 'form' && $id === T_VARIABLE && $text === '$form') {
                $output .= '$schema';

                continue;
            }

            if ($currentFunction === 'infolist' && $id === T_VARIABLE && in_array($text, ['$infolist', '$list'], true)) {
                $replacement = $text === '$list' ? '$schema' : '$schema';
                $output .= $replacement;

                continue;
            }

            $output .= $text;

            continue;
        }

        $output .= $text;
    }

    // Normalize any navigation icon declarations to use the shared docblock format required by Filament v4.
    $output = preg_replace_callback(
        '/(?m)^(\s*)(?:\/\*\*[^*]*\*+(?:[^\/*][^*]*\*+)*\/\s*)?protected static(?:\s+[^\s]+)?\s+\$navigationIcon\s*=\s*([^;]+);/',
        static function (array $matches): string {
            $indentation = $matches[1];
            $value = rtrim($matches[2]);

            return sprintf(
                '%1$s/** @var string|\\BackedEnum|null */' . "\n" .
                '%1$sprotected static $navigationIcon = %2$s;',
                $indentation,
                $value
            );
        },
        $output
    );

    if ($output !== $code) {
        if (str_contains($output, 'function form(') || str_contains($output, 'function infolist(')) {
            if (! str_contains($output, 'use Filament\\Schemas\\Schema;')) {
                $output = preg_replace(
                    '/(namespace [^;]+;\s*)/m',
                    "$1\nuse Filament\\\\Schemas\\\\Schema;\n",
                    $output,
                    1
                );
            }
        }

        if (! str_contains($output, 'use Filament\\Schemas\\Schema;') && str_contains($output, 'Schema $schema')) {
            $output = preg_replace(
                '/(namespace [^;]+;\s*)/m',
                "$1\nuse Filament\\\\Schemas\\\\Schema;\n",
                $output,
                1
            );
        }

        file_put_contents($path, $output);
    }
}
