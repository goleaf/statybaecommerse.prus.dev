<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Support\Nav;

final class SimpleTranslator
{
    /** @var array<string, mixed> */
    private array $translations = [];

    public function __construct()
    {
        $base = __DIR__ . '/../resources/lang/en';
        if (! is_dir($base)) {
            return;
        }

        foreach ($this->phpFiles($base) as $file) {
            $key = str_replace(['/', '.php'], ['.', ''], substr($file, strlen($base) + 1));
            $this->setTranslation($key, require $file);
        }
    }

    /**
     * @param mixed $value
     */
    private function setTranslation(string $key, $value): void
    {
        $segments = explode('.', $key);
        $target = &$this->translations;

        foreach ($segments as $segment) {
            if (! is_array($target)) {
                $target = [];
            }
            if (! array_key_exists($segment, $target)) {
                $target[$segment] = [];
            }
            $target = &$target[$segment];
        }

        $target = $value;
    }

    /**
     * @return array<int, string>
     */
    private function phpFiles(string $directory): array
    {
        $files = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }

        sort($files);

        return $files;
    }

    public function get(string $key): string
    {
        $segments = explode('.', $key);
        $value = $this->translations;

        foreach ($segments as $segment) {
            if (! is_array($value) || ! array_key_exists($segment, $value)) {
                return $key;
            }
            $value = $value[$segment];
        }

        return is_scalar($value) ? (string) $value : $key;
    }
}

$__simpleTranslator = new SimpleTranslator;

Nav::setTranslator(static fn (string $key): string => $__simpleTranslator->get($key));

if (! function_exists('__')) {
    function __(string $key, array $replace = [], ?string $locale = null): string
    {
        global $__simpleTranslator;

        return $__simpleTranslator->get($key);
    }
}

$groups = Nav::navigationGroups();
$orderedResources = Nav::orderedResources();

$tree = [
    'groups' => array_map(
        static function (array $group) use ($orderedResources): array {
            $resources = array_values(array_filter(
                $orderedResources,
                static fn (string $resource): bool => Nav::groupKeyForResource($resource) === $group['key'],
            ));

            return [
                'label'     => $group['label'],
                'label_key' => $group['label_key'],
                'icon'      => $group['icon'],
                'sort'      => $group['sort'],
                'resources' => array_map(
                    static fn (string $resource): array => [
                        'class' => $resource,
                        'icon'  => Nav::iconForResource($resource),
                        'sort'  => Nav::sortForResource($resource),
                    ],
                    $resources,
                ),
            ];
        },
        $groups,
    ),
    'ungrouped' => array_map(
        static fn (string $resource): array => [
            'class' => $resource,
            'icon'  => Nav::iconForResource($resource),
            'sort'  => Nav::sortForResource($resource),
        ],
        array_values(array_filter(
            $orderedResources,
            static fn (string $resource): bool => Nav::groupKeyForResource($resource) === null,
        )),
    ),
];

echo json_encode($tree, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;

Nav::setTranslator(null);
