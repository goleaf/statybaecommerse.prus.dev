<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\NavigationGroup;
use BackedEnum;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Throwable;
use UnitEnum;

/**
 * Helper responsible for aggregating Filament navigation metadata across the admin panel.
 *
 * The implementation avoids instantiating resource classes directly (which would require
 * the entire Filament stack to boot) by parsing the source files for the navigation
 * properties and accessors. This keeps the helper safe to run in tooling contexts where
 * the application container is not yet initialised.
 */
final class Nav
{
    /**
     * Cache of discovered resource class names to avoid repeated filesystem traversal.
     *
     * @var array<int, class-string>|null
     */
    private static ?array $resourceCache = null;

    /**
     * Normalised navigation group definitions loaded from configuration.
     *
     * @var array<string, array{label_key: string, label: string, icon: string|null, sort: int, collapsed: bool}>
     */
    private static array $groupCache = [];

    /**
     * Cache of raw file contents keyed by resource class for repeated metadata lookups.
     *
     * @var array<class-string, string>
     */
    private static array $fileCache = [];

    /**
     * Optional translator resolver used when Laravel's global helper is unavailable.
     */
    /** @var null|callable(string): (string|null) */
    private static $translatorResolver = null;

    /**
     * Resolve the translated navigation group label for the given resource.
     */
    public static function groupForResource(string $resource): UnitEnum|string|null
    {
        $group = self::metadataValue($resource, 'Group');

        return $group !== null ? self::translate((string) $group) : null;
    }

    /**
     * Resolve the icon identifier configured for the provided resource.
     */
    public static function iconForResource(string $resource): BackedEnum|Htmlable|string|null
    {
        return self::metadataValue($resource, 'Icon');
    }

    /**
     * Resolve the navigation sort order configured for the provided resource.
     */
    public static function sortForResource(string $resource): ?int
    {
        $sort = self::metadataValue($resource, 'Sort');

        return $sort !== null ? (int) $sort : null;
    }

    /**
     * Fetch the logical group identifier that the resource belongs to, if any exists.
     */
    public static function groupKeyForResource(string $resource): ?string
    {
        $groupLabel = self::metadataValue($resource, 'Group');

        if ($groupLabel === null) {
            return null;
        }

        foreach (self::groupDefinitions() as $key => $definition) {
            if ($definition['label'] === self::translate((string) $groupLabel) || $definition['label_key'] === (string) $groupLabel) {
                return $key;
            }
        }

        return null;
    }

    /**
     * Retrieve the configured navigation icon for the specified group key.
     */
    public static function groupIcon(?string $groupKey): ?string
    {
        return $groupKey !== null ? (self::groupDefinitions()[$groupKey]['icon'] ?? null) : null;
    }

    /**
     * Retrieve the configured navigation sort order for the specified group key.
     */
    public static function groupSort(?string $groupKey): ?int
    {
        return $groupKey !== null ? (self::groupDefinitions()[$groupKey]['sort'] ?? null) : null;
    }

    /**
     * Build the navigation group definitions consumed by the Filament panel provider.
     *
     * @return array<int, array{key: string, label_key: string, label: string, icon: string|null, sort: int, collapsed: bool}>
     */
    public static function navigationGroups(): array
    {
        $groups = [];

        foreach (self::groupDefinitions() as $key => $definition) {
            $groups[] = [
                'key'       => $key,
                'label'     => $definition['label'],
                'label_key' => $definition['label_key'],
                'icon'      => $definition['icon'],
                'sort'      => $definition['sort'],
                'collapsed' => $definition['collapsed'],
            ];
        }

        usort($groups, static function (array $a, array $b): int {
            if ($a['sort'] === $b['sort']) {
                return $a['key'] <=> $b['key'];
            }

            return $a['sort'] <=> $b['sort'];
        });

        return $groups;
    }

    /**
     * Determine the ordered list of resource classes honouring group and resource sorting.
     *
     * @return array<int, class-string>
     */
    public static function orderedResources(): array
    {
        $resources = [];

        foreach (self::resourceClasses() as $class) {
            $groupKey = self::groupKeyForResource($class);
            $resources[$class] = [
                'group_sort' => self::groupSort($groupKey) ?? PHP_INT_MAX,
                'sort'       => self::sortForResource($class) ?? PHP_INT_MAX,
            ];
        }

        uksort($resources, static function (string $a, string $b) use ($resources): int {
            $groupSortA = $resources[$a]['group_sort'];
            $groupSortB = $resources[$b]['group_sort'];

            if ($groupSortA !== $groupSortB) {
                return $groupSortA <=> $groupSortB;
            }

            $sortA = $resources[$a]['sort'];
            $sortB = $resources[$b]['sort'];

            if ($sortA !== $sortB) {
                return $sortA <=> $sortB;
            }

            return $a <=> $b;
        });

        return array_keys($resources);
    }

    /**
     * Extract metadata by scanning the resource source file for navigation declarations.
     */
    private static function metadataValue(string $resource, string $type): mixed
    {
        $contents = self::resourceFileContents($resource);

        if ($contents === null) {
            return null;
        }

        $propertyPattern = '/protected\s+static[^\n]*\$navigation' . $type . '\s*=\s*([^;]+);/m';
        if (preg_match($propertyPattern, $contents, $propertyMatch) === 1) {
            return self::evaluateExpression(trim($propertyMatch[1]));
        }

        $methodPattern = '/public\s+static\s+function\s+getNavigation' . $type . '[^{]*\{(?P<body>.*?)\}/s';
        if (preg_match($methodPattern, $contents, $methodMatch) === 1) {
            if (preg_match('/return\s+([^;]+);/', $methodMatch['body'], $returnMatch) === 1) {
                return self::evaluateExpression(trim($returnMatch[1]));
            }
        }

        return null;
    }

    /**
     * @return array<int, class-string>
     */
    private static function resourceClasses(): array
    {
        if (self::$resourceCache !== null) {
            return self::$resourceCache;
        }

        $basePath = self::resourceBasePath();
        if (! is_dir($basePath)) {
            self::$resourceCache = [];

            return self::$resourceCache;
        }

        $filesystem = new Filesystem;
        $files = $filesystem->allFiles($basePath);

        $classes = [];
        foreach ($files as $file) {
            if ($file->getExtension() !== 'php' || ! Str::endsWith($file->getFilename(), 'Resource.php')) {
                continue;
            }

            $relativePath = Str::after($file->getPathname(), $basePath . DIRECTORY_SEPARATOR);

            // Normalise to a fully-qualified class name while avoiding fragile backslash escaping.
            $classes[] = 'App\\Filament\\Resources\\' . str_replace(['/', '.php'], ['\\', ''], $relativePath);
        }

        self::$resourceCache = array_values(array_unique($classes));

        return self::$resourceCache;
    }

    /**
     * @return array<string, array{label_key: string, label: string, icon: string|null, sort: int, collapsed: bool}>
     */
    private static function groupDefinitions(): array
    {
        if (self::$groupCache !== []) {
            return self::$groupCache;
        }

        $groups = (array) self::configValue('filament.navigation.groups', []);

        foreach ($groups as $index => $group) {
            if (! is_array($group)) {
                continue;
            }

            $key = (string) Arr::get($group, 'key', (string) $index);
            $labelKey = (string) Arr::get($group, 'label', $key);
            $label = self::translate($labelKey);
            $icon = Arr::get($group, 'icon');
            $sort = (int) Arr::get($group, 'sort', $index);
            $collapsed = (bool) Arr::get($group, 'collapsed', false);

            self::$groupCache[$key] = [
                'label_key' => $labelKey,
                'label'     => $label,
                'icon'      => $icon !== null ? (string) $icon : null,
                'sort'      => $sort,
                'collapsed' => $collapsed,
            ];
        }

        return self::$groupCache;
    }

    private static function resourceFileContents(string $resource): ?string
    {
        if (isset(self::$fileCache[$resource])) {
            return self::$fileCache[$resource];
        }

        $relative = Str::after($resource, 'App\\Filament\\Resources\\');
        if ($relative === $resource) {
            return null;
        }

        // Convert the namespace-style segment into an absolute filesystem path.
        $path = self::resourceBasePath() . '/' . str_replace('\\', '/', $relative) . '.php';
        if (! is_file($path)) {
            return null;
        }

        $contents = file_get_contents($path);
        if ($contents === false) {
            return null;
        }

        self::$fileCache[$resource] = $contents;

        return self::$fileCache[$resource];
    }

    private static function evaluateExpression(string $expression): mixed
    {
        $expression = trim($expression);

        if ($expression === '' || strcasecmp($expression, 'null') === 0) {
            return null;
        }

        if (preg_match('/^-?\d+$/', $expression) === 1) {
            return (int) $expression;
        }

        if (preg_match('/^(true|false)$/i', $expression) === 1) {
            return strtolower($expression) === 'true';
        }

        $firstChar = $expression[0] ?? '';
        $lastChar = $expression !== '' ? $expression[strlen($expression) - 1] : '';
        if (($firstChar === "'" || $firstChar === '"') && $firstChar === $lastChar) {
            // Strip the wrapping quotes and unescape sequences to return the literal string value.
            return stripcslashes(substr($expression, 1, -1));
        }

        if (preg_match('/__\(\s*["\'](.+?)["\']/', $expression, $translationMatch) === 1) {
            // Translate the resolved key when the expression wraps it in the __() helper.
            return self::translate($translationMatch[1]);
        }

        if (preg_match('/NavigationGroup::([A-Za-z0-9_]+)(?:->value)?/', $expression, $enumMatch) === 1) {
            $constant = NavigationGroup::class . '::' . $enumMatch[1];
            if (defined($constant)) {
                /** @var NavigationGroup $enum */
                $enum = constant($constant);

                return $enum->value;
            }
        }

        return null;
    }

    /**
     * Safely resolve configuration values even when the Laravel container is not bootstrapped.
     */
    private static function configValue(string $key, mixed $default = null): mixed
    {
        if (function_exists('config')) {
            try {
                return config($key, $default);
            } catch (Throwable) {
                // Swallow the exception and fall back to manual file loading.
            }
        }

        if (! str_starts_with($key, 'filament.')) {
            return $default;
        }

        $configPath = self::basePath() . '/config/filament.php';
        if (! is_file($configPath)) {
            return $default;
        }

        /** @var array<string, mixed> $config */
        $config = require $configPath;

        $nestedKey = Str::after($key, 'filament.');

        return Arr::get($config, $nestedKey, $default);
    }

    /**
     * Allow tooling to inject a lightweight translator implementation.
     */
    public static function setTranslator(?callable $resolver): void
    {
        self::$translatorResolver = $resolver;
    }

    private static function translate(string $key): string
    {
        if (function_exists('__')) {
            try {
                $translated = __($key);

                if (is_string($translated)) {
                    return $translated;
                }
            } catch (Throwable) {
                // Fall through to the custom resolver when the container is not ready.
            }
        }

        if (self::$translatorResolver !== null) {
            $fallback = (self::$translatorResolver)($key);

            if (is_string($fallback)) {
                return $fallback;
            }
        }

        return $key;
    }

    private static function resourceBasePath(): string
    {
        if (function_exists('app_path')) {
            try {
                return app_path('Filament/Resources');
            } catch (Throwable) {
                // Ignore the failure and fall back to the manual path resolution.
            }
        }

        return self::basePath() . '/app/Filament/Resources';
    }

    private static function basePath(): string
    {
        if (function_exists('base_path')) {
            try {
                return base_path();
            } catch (Throwable) {
                // Ignore the failure and fall through to the manual path resolution.
            }
        }

        return dirname(__DIR__, 2);
    }
}
