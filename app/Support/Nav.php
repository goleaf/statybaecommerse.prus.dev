<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\NavigationGroup;
use App\Support\Concerns\HasNav;
use BackedEnum;
use Filament\Resources\Resource;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Str;
use ParseError;
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
     * Recursion guards to prevent infinite loops when resources delegate back to Nav.
     *
     * @var array<string, bool>
     */
    private static array $resolving = [];

    /**
     * Discover all first-party Filament resource classes under the application namespace.
     *
     * @return array<int, class-string<\Filament\Resources\Resource>>
     */
    private static function resourceClasses(): array
    {
        // Allow tests to opt-out of automatic resource discovery when they
        // explicitly request a limited set of resources via configuration.
        if (app()->environment('testing')) {
            $shouldDiscover = (bool) config('filament.testing.autodiscover_resources', true);

            if (! $shouldDiscover) {
                /** @var array<class-string<resource>> $configured */
                $configured = array_values(array_filter(
                    (array) config('filament.testing.resources', []),
                    static fn (mixed $resource): bool => is_string($resource) &&
                        class_exists($resource) &&
                        is_subclass_of($resource, Resource::class),
                ));

                return $configured;
            }
        }

        $resourcePath = app_path('Filament/Resources');
        $files = glob($resourcePath . '/*Resource.php');
        $classes = [];

        if ($files === false) {
            return $classes;
        }

        foreach ($files as $file) {
            $relative = Str::after($file, app_path() . DIRECTORY_SEPARATOR);
            $class = 'App\\' . str_replace(['/', '.php'], ['\\', ''], $relative);

            try {
                if (class_exists($class) && is_subclass_of($class, Resource::class)) {
                    $classes[] = $class;
                }
            } catch (ParseError $exception) {
                // Skip resources that cannot be parsed so storefront pages remain accessible during tests.
                continue;
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

        // Normalize label here so downstream calls always get a string (or null).
        $normalizedGroupLabel = $groupKey !== null
            ? self::normalizeLabel($groupLabel ?? __($groupKey), $groupKey)
            : null;

        $icon = self::resolveIcon($resource);
        $sort = self::resolveSort($resource);

        if ($groupKey !== null && ! isset(self::$groupMetaCache[$groupKey])) {
            self::$groupMetaCache[$groupKey] = [
                'key'   => $groupKey,
                'label' => $normalizedGroupLabel,
                'icon'  => $groupIcon,
                'sort'  => $groupSort,
            ];
        } elseif ($groupKey !== null) {
            $existing = self::$groupMetaCache[$groupKey];
            $existing['label'] = $existing['label']
                ?: self::normalizeLabel($groupLabel ?? __($groupKey), $groupKey);
            $existing['icon'] = $existing['icon'] ?: $groupIcon;
            $existing['sort'] = $existing['sort'] ?? $groupSort;
            self::$groupMetaCache[$groupKey] = $existing;
        }

        return self::$resourceMetaCache[$resource] = [
            'group_key'   => $groupKey,
            'group_label' => $normalizedGroupLabel,
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
            // When a resource stores the translated navigation label directly, try to map it back to
            // the canonical NavigationGroup enum so icon and priority metadata remain available.
            foreach (NavigationGroup::cases() as $navigationCase) {
                if ($navigationCase->label() === $value) {
                    return [
                        $navigationCase->value,
                        $navigationCase->label(),
                        self::normalizeIcon($navigationCase->icon()),
                        $navigationCase->priority(),
                    ];
                }
            }

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
        if (! self::resourceUsesNavTrait($resource) && method_exists($resource, 'getNavigationGroup')) {
            $key = 'group:' . $resource;
            if (! isset(self::$resolving[$key])) {
                self::$resolving[$key] = true;
                try {
                    $group = $resource::getNavigationGroup();

                    if ($group !== null) {
                        return $group;
                    }
                } catch (Throwable) {
                    // Intentionally swallow exceptions so a misbehaving resource does not break discovery.
                } finally {
                    unset(self::$resolving[$key]);
                }
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
        if (! self::resourceUsesNavTrait($resource) && method_exists($resource, 'getNavigationIcon')) {
            $key = 'icon:' . $resource;
            if (! isset(self::$resolving[$key])) {
                self::$resolving[$key] = true;
                try {
                    $icon = $resource::getNavigationIcon();

                    if ($icon !== null) {
                        return $icon;
                    }
                } catch (Throwable) {
                    // Ignore and fall back to the static property if present.
                } finally {
                    unset(self::$resolving[$key]);
                }
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
        if (! self::resourceUsesNavTrait($resource) && method_exists($resource, 'getNavigationSort')) {
            $key = 'sort:' . $resource;
            if (! isset(self::$resolving[$key])) {
                self::$resolving[$key] = true;
                try {
                    $sort = $resource::getNavigationSort();

                    if ($sort !== null) {
                        return $sort;
                    }
                } catch (Throwable) {
                    // Ignore and look for a property below.
                } finally {
                    unset(self::$resolving[$key]);
                }
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
     * Determine whether the resource proxies navigation metadata through the HasNav trait.
     * When the trait is present we must avoid calling the static accessors directly to
     * prevent infinite recursion (`Nav::groupForResource()` would otherwise re-enter here).
     */
    private static function resourceUsesNavTrait(string $resource): bool
    {
        /** @var array<int, class-string> $traits */
        $traits = class_uses_recursive($resource);

        return in_array(HasNav::class, $traits, true);
    }

    /**
     * Build the navigation group definitions consumed by Filament.
     *
     * @return array<int, array{key: string, label: string, label_key: string|null, icon: string|null, sort: int|null}>
     */
    public static function navigationGroups(): array
    {
        // Ensure cache is primed by touching every resource once.
        foreach (self::resourceClasses() as $resource) {
            self::resourceMeta($resource);
        }

        /** @var array<int, array{key?: string, label?: string, icon?: string|null}> $configured */
        $configured = (array) config('filament.navigation.groups', []);

        $groups = [];

        // Seed groups from configuration so tests can assert deterministic ordering and translation keys.
        $order = 0;
        foreach ($configured as $group) {
            if (! is_array($group)) {
                continue;
            }

            $key = (string) ($group['key'] ?? $group['label'] ?? '');
            if ($key === '') {
                continue;
            }

            $labelKey = (string) ($group['label'] ?? $key);
            $resolvedLabel = trans($labelKey, locale: 'en');

            if (is_array($resolvedLabel)) {
                $resolvedLabel = $labelKey;
            }

            $groups[$key] = [
                'key'       => $key,
                'label'     => (string) $resolvedLabel,
                'label_key' => $labelKey,
                'icon'      => $group['icon'] ?? null,
                'sort'      => $order++,
            ];
        }

        // Bring in any additional groups discovered from resources that were not configured explicitly.
        foreach (self::$groupMetaCache as $key => $meta) {
            $groupKey = $meta['key'] ?? null;
            if (! is_string($groupKey) || $groupKey === '' || isset($groups[$groupKey])) {
                continue;
            }

            $groups[$groupKey] = [
                'key'       => $groupKey,
                'label'     => self::normalizeLabel($meta['label'] ?? $groupKey, $groupKey),
                'label_key' => $groupKey,
                'icon'      => $meta['icon'] ?? null,
                'sort'      => $meta['sort'] ?? null,
            ];
        }

        $groups = array_values($groups);

        usort(
            $groups,
            static fn (array $a, array $b): int => ($a['sort'] ?? PHP_INT_MAX) <=> ($b['sort'] ?? PHP_INT_MAX)
        );

        return $groups;
    }

    /**
     * Ensure nav labels are always strings. If a translation returns an array,
     * fall back to the group key; if Htmlable, cast; if scalar, cast to string.
     */
    private static function normalizeLabel(mixed $value, string $fallback): string
    {
        if ($value instanceof Htmlable) {
            return (string) $value;
        }

        if (is_array($value)) {
            $first = reset($value);
            return is_string($first) ? $first : $fallback;
        }

        if (is_scalar($value) || (is_object($value) && method_exists($value, '__toString'))) {
            return (string) $value;
        }

        return $fallback;
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
