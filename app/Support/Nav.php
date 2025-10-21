<?php

declare(strict_types=1);

namespace App\Support;

use BackedEnum;
use Filament\Resources\Resource;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use UnitEnum;

/**
 * Helper responsible for reading Filament navigation metadata from resource classes.
 *
 * The legacy resources define their navigation configuration either via explicit static
 * methods (for example `getNavigationGroup`) or via protected static properties. This
 * utility normalises the access pattern and exposes cache-backed helpers so tests and
 * services can consistently reference the navigation details without duplicating the
 * reflection logic in multiple places.
 */
final class Nav
{
    /**
     * Cache of navigation metadata keyed by resource class name.
     *
     * @var array<class-string, array{group: UnitEnum|string|null, icon: BackedEnum|string|null, sort: int|null}>
     */
    private static array $resourceMetadata = [];

    /**
     * Resolve the navigation group for the provided resource class.
     */
    public static function groupForResource(string $resource): UnitEnum|string|null
    {
        return self::metadataForResource($resource)['group'];
    }

    /**
     * Resolve the navigation icon for the provided resource class.
     */
    public static function iconForResource(string $resource): BackedEnum|string|null
    {
        return self::metadataForResource($resource)['icon'];
    }

    /**
     * Resolve the navigation sort order for the provided resource class.
     */
    public static function sortForResource(string $resource): ?int
    {
        $sort = self::metadataForResource($resource)['sort'];

        return is_int($sort) ? $sort : null;
    }

    /**
     * Resolve and cache the metadata for a resource class.
     *
     * @return array{group: UnitEnum|string|null, icon: BackedEnum|string|null, sort: int|null}
     */
    private static function metadataForResource(string $resource): array
    {
        if (! is_subclass_of($resource, Resource::class)) {
            throw new InvalidArgumentException(sprintf('%s must extend %s.', $resource, Resource::class));
        }

        if (! array_key_exists($resource, self::$resourceMetadata)) {
            self::$resourceMetadata[$resource] = [
                'group' => self::resolveValue($resource, 'getNavigationGroup', 'navigationGroup'),
                'icon'  => self::resolveValue($resource, 'getNavigationIcon', 'navigationIcon'),
                'sort'  => self::resolveValue($resource, 'getNavigationSort', 'navigationSort'),
            ];
        }

        return self::$resourceMetadata[$resource];
    }

    /**
     * Resolve a navigation metadata value either through a static method or property lookup.
     */
    private static function resolveValue(string $resource, string $method, string $property): mixed
    {
        if (is_callable([$resource, $method])) {
            /** @var mixed $value */
            $value = $resource::$method();

            return self::normaliseValue($value);
        }

        try {
            $reflection = new ReflectionClass($resource);
        } catch (ReflectionException $exception) {
            throw new InvalidArgumentException($exception->getMessage(), previous: $exception);
        }

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

        /** @var mixed $value */
        $value = $propertyReflection->getValue();

        return self::normaliseValue($value);
    }

    /**
     * Normalise metadata values returned from resources to the supported scalar/enum shapes.
     */
    private static function normaliseValue(mixed $value): mixed
    {
        if ($value instanceof UnitEnum || $value instanceof BackedEnum) {
            return $value;
        }

        if (is_string($value) || is_int($value) || $value === null) {
            return $value;
        }

        return null;
    }
}
