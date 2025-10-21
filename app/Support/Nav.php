<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\NavigationGroup;
use BackedEnum;
use Filament\Resources\Resource;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionException;
use Throwable;
use UnitEnum;

/**
 * Central registry responsible for discovering Filament resources and exposing
 * consistent navigation metadata (group, icon, sort) for admin panel wiring.
 */
final class Nav
{
    /**
     * Static fallbacks for groups that are represented by plain strings instead of the NavigationGroup enum.
     *
     * @var array<string, array{icon: string|null, sort: int|null}>
     */
    private const FALLBACK_GROUP_DEFINITIONS = [
        'Analytics'             => ['icon' => 'heroicon-o-chart-bar', 'sort' => 500],
        'Campaigns'             => ['icon' => 'heroicon-o-rocket-launch', 'sort' => 310],
        'Content'               => ['icon' => 'heroicon-o-document-text', 'sort' => 400],
        'Content Management'    => ['icon' => 'heroicon-o-folder', 'sort' => 410],
        'Customers'             => ['icon' => 'heroicon-o-user-group', 'sort' => 210],
        'Discounts'             => ['icon' => 'heroicon-o-tag', 'sort' => 320],
        'E-commerce'            => ['icon' => 'heroicon-o-shopping-bag', 'sort' => 205],
        'Inventory'             => ['icon' => 'heroicon-o-archive-box', 'sort' => 110],
        'Locations'             => ['icon' => 'heroicon-o-globe-alt', 'sort' => 215],
        'Marketing'             => ['icon' => 'heroicon-o-megaphone', 'sort' => 300],
        'News'                  => ['icon' => 'heroicon-o-newspaper', 'sort' => 420],
        'Orders'                => ['icon' => 'heroicon-o-shopping-bag', 'sort' => 200],
        'Products'              => ['icon' => 'heroicon-o-cube', 'sort' => 100],
        'Recommendation System' => ['icon' => 'heroicon-o-sparkles', 'sort' => 520],
        'Referral'              => ['icon' => 'heroicon-o-gift', 'sort' => 330],
        'Referral System'       => ['icon' => 'heroicon-o-gift', 'sort' => 330],
        'Reports'               => ['icon' => 'heroicon-o-document-chart-bar', 'sort' => 510],
        'Settings'              => ['icon' => 'heroicon-o-cog-6-tooth', 'sort' => 600],
        'System'                => ['icon' => 'heroicon-o-cog-6-tooth', 'sort' => 610],
        'Users'                 => ['icon' => 'heroicon-o-users', 'sort' => 220],
    ];

    /**
     * Cached navigation metadata for discovered resources.
     *
     * @var array<class-string, array{group_key: ?string, group_label: ?string, group_icon: ?string, group_sort: ?int, icon: BackedEnum|Htmlable|string|null, sort: ?int}>
     */
    private static array $resourceMetaCache = [];

    /**
     * Cached group metadata aggregated from resources.
     *
     * @var array<string, array{key: string, label: string, icon: string|null, sort: int|null}>
     */
    private static array $groupMetaCache = [];

    /**
     * Discover all first-party Filament resource classes under the application namespace.
     *
     * @return array<int, class-string<\Filament\Resources\Resource>>
     */
    private static function resourceClasses(): array
    {
        $resourcePath = app_path('Filament/Resources');
        $files = glob($resourcePath . '/*Resource.php');
        $classes = [];

        if ($files === false) {
            return $classes;
        }

        foreach ($files as $file) {
            $relative = Str::after($file, app_path() . DIRECTORY_SEPARATOR);
            $class = 'App\\' . str_replace(['/', '.php'], ['\\', ''], $relative);

            if (class_exists($class) && is_subclass_of($class, Resource::class)) {
                $classes[] = $class;
            }
        }

        sort($classes);

        return $classes;
    }

    /**
     * Resolve the metadata for the given resource, caching the result to avoid repeated reflection.
     *
     * @param  class-string<\Filament\Resources\Resource>                                                                                                $resource
     * @return array{group_key: ?string, group_label: ?string, group_icon: ?string, group_sort: ?int, icon: BackedEnum|Htmlable|string|null, sort: ?int}
     */
    private static function resourceMeta(string $resource): array
    {
        if (isset(self::$resourceMetaCache[$resource])) {
            return self::$resourceMetaCache[$resource];
        }

        [$groupKey, $groupLabel, $groupIcon, $groupSort] = self::resolveGroupMeta($resource);
        $icon = self::resolveIcon($resource);
        $sort = self::resolveSort($resource);

        if ($groupKey !== null && ! isset(self::$groupMetaCache[$groupKey])) {
            self::$groupMetaCache[$groupKey] = [
                'key'   => $groupKey,
                'label' => $groupLabel ?? __($groupKey),
                'icon'  => $groupIcon,
                'sort'  => $groupSort,
            ];
        } elseif ($groupKey !== null) {
            $existing = self::$groupMetaCache[$groupKey];
            $existing['label'] = $existing['label'] ?: ($groupLabel ?? __($groupKey));
            $existing['icon'] = $existing['icon'] ?: $groupIcon;
            $existing['sort'] = $existing['sort'] ?? $groupSort;
            self::$groupMetaCache[$groupKey] = $existing;
        }

        return self::$resourceMetaCache[$resource] = [
            'group_key'   => $groupKey,
            'group_label' => $groupLabel,
            'group_icon'  => $groupIcon,
            'group_sort'  => $groupSort,
            'icon'        => $icon,
            'sort'        => $sort,
        ];
    }

    /**
     * Resolve the raw navigation group definition for a resource and return normalized metadata tuple.
     *
     * @param  class-string<\Filament\Resources\Resource>         $resource
     * @return array{0: ?string, 1: ?string, 2: ?string, 3: ?int}
     */
    private static function resolveGroupMeta(string $resource): array
    {
        $value = self::getRawGroupValue($resource);

        if ($value instanceof NavigationGroup) {
            return [
                $value->value,
                $value->label(),
                'heroicon-o-' . $value->icon(),
                $value->priority(),
            ];
        }

        if ($value instanceof UnitEnum) {
            $key = $value instanceof BackedEnum ? (string) $value->value : $value->name;
            $label = method_exists($value, 'label') ? $value->label() : __($key);
            $icon = method_exists($value, 'icon') ? self::normalizeIcon($value->icon()) : null;
            $sort = method_exists($value, 'priority') ? $value->priority() : null;

            return [$key, $label, $icon, $sort];
        }

        if (is_string($value) && $value !== '') {
            $fallback = self::FALLBACK_GROUP_DEFINITIONS[$value] ?? null;
            $label = __($value);

            return [
                $value,
                $label,
                $fallback['icon'] ?? null,
                $fallback['sort'] ?? null,
            ];
        }

        return [null, null, null, null];
    }

    /**
     * Retrieve the raw navigation group configuration from the resource via static methods or properties.
     *
     * @param class-string<\Filament\Resources\Resource> $resource
     */
    private static function getRawGroupValue(string $resource): mixed
    {
        if (method_exists($resource, 'getNavigationGroup')) {
            try {
                $group = $resource::getNavigationGroup();

                if ($group !== null) {
                    return $group;
                }
            } catch (Throwable) {
                // Intentionally swallow exceptions so a misbehaving resource does not break discovery.
            }
        }

        return self::getStaticPropertyValue($resource, 'navigationGroup');
    }

    /**
     * Resolve the icon metadata for a resource.
     *
     * @param class-string<\Filament\Resources\Resource> $resource
     */
    private static function resolveIcon(string $resource): BackedEnum|Htmlable|string|null
    {
        if (method_exists($resource, 'getNavigationIcon')) {
            try {
                $icon = $resource::getNavigationIcon();

                if ($icon !== null) {
                    return $icon;
                }
            } catch (Throwable) {
                // Ignore and fall back to the static property if present.
            }
        }

        return self::getStaticPropertyValue($resource, 'navigationIcon');
    }

    /**
     * Resolve the navigation sort order for a resource.
     *
     * @param class-string<\Filament\Resources\Resource> $resource
     */
    private static function resolveSort(string $resource): ?int
    {
        if (method_exists($resource, 'getNavigationSort')) {
            try {
                $sort = $resource::getNavigationSort();

                if ($sort !== null) {
                    return $sort;
                }
            } catch (Throwable) {
                // Ignore and look for a property below.
            }
        }

        $value = self::getStaticPropertyValue($resource, 'navigationSort');

        return is_int($value) ? $value : null;
    }

    /**
     * Fetch a protected static property value through reflection.
     *
     * @param class-string $class
     */
    private static function getStaticPropertyValue(string $class, string $property): mixed
    {
        try {
            $reflection = new ReflectionClass($class);

            if (! $reflection->hasProperty($property)) {
                return null;
            }

            $propertyReflection = $reflection->getProperty($property);

            if (! $propertyReflection->isStatic()) {
                return null;
            }

            if (! $propertyReflection->isPublic()) {
                $propertyReflection->setAccessible(true);
            }

            return $propertyReflection->getValue();
        } catch (ReflectionException) {
            return null;
        }
    }

    /**
     * Normalise icon values to a consistent heroicon format when enums provide bare icon names.
     */
    private static function normalizeIcon(string $icon): string
    {
        return Str::startsWith($icon, 'heroicon-') ? $icon : 'heroicon-o-' . $icon;
    }

    /**
     * Retrieve the raw group key for a resource.
     */
    public static function groupKeyForResource(string $resource): ?string
    {
        return self::resourceMeta($resource)['group_key'];
    }

    /**
     * Retrieve the translated group label for a resource.
     */
    public static function groupForResource(string $resource): ?string
    {
        return self::resourceMeta($resource)['group_label'];
    }

    /**
     * Retrieve the icon defined for a resource, if any.
     */
    public static function iconForResource(string $resource): BackedEnum|Htmlable|string|null
    {
        return self::resourceMeta($resource)['icon'];
    }

    /**
     * Retrieve the navigation sort for a resource, if defined.
     */
    public static function sortForResource(string $resource): ?int
    {
        return self::resourceMeta($resource)['sort'];
    }

    /**
     * Resolve the icon configured for a navigation group key.
     */
    public static function groupIcon(?string $group): ?string
    {
        if ($group === null) {
            return null;
        }

        $groupMeta = self::$groupMetaCache[$group] ?? null;

        if (($groupMeta['icon'] ?? null) !== null) {
            return $groupMeta['icon'];
        }

        return self::FALLBACK_GROUP_DEFINITIONS[$group]['icon'] ?? null;
    }

    /**
     * Resolve the configured sort order for a navigation group key.
     */
    public static function groupSort(?string $group): ?int
    {
        if ($group === null) {
            return null;
        }

        $groupMeta = self::$groupMetaCache[$group] ?? null;

        if (($groupMeta['sort'] ?? null) !== null) {
            return $groupMeta['sort'];
        }

        return self::FALLBACK_GROUP_DEFINITIONS[$group]['sort'] ?? null;
    }

    /**
     * Build the navigation group definitions consumed by Filament.
     *
     * @return array<int, array{key: string, label: string, icon: string|null, sort: int|null}>
     */
    public static function navigationGroups(): array
    {
        // Ensure cache is primed by touching every resource once.
        foreach (self::resourceClasses() as $resource) {
            self::resourceMeta($resource);
        }

        $groups = array_values(self::$groupMetaCache);

        usort(
            $groups,
            static fn (array $a, array $b): int => ($a['sort'] ?? PHP_INT_MAX) <=> ($b['sort'] ?? PHP_INT_MAX)
        );

        return $groups;
    }

    /**
     * Determine the ordered list of resource classes to register with the panel.
     *
     * @return array<int, class-string<\Filament\Resources\Resource>>
     */
    public static function orderedResources(): array
    {
        $resources = self::resourceClasses();

        usort(
            $resources,
            static function (string $a, string $b): int {
                $metaA = self::resourceMeta($a);
                $metaB = self::resourceMeta($b);

                $groupSortA = $metaA['group_sort'] ?? PHP_INT_MAX;
                $groupSortB = $metaB['group_sort'] ?? PHP_INT_MAX;

                if ($groupSortA !== $groupSortB) {
                    return $groupSortA <=> $groupSortB;
                }

                $sortA = $metaA['sort'] ?? PHP_INT_MAX;
                $sortB = $metaB['sort'] ?? PHP_INT_MAX;

                if ($sortA !== $sortB) {
                    return $sortA <=> $sortB;
                }

                return $a <=> $b;
            }
        );

        return $resources;
    }
}
