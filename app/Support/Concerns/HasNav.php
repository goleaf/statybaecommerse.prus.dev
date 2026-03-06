<?php

declare(strict_types=1);

namespace App\Support\Concerns;

use App\Support\Nav;
use BackedEnum;
use Illuminate\Contracts\Support\Htmlable;
use Throwable;
use UnitEnum;

/**
 * Shared helpers that proxy Filament navigation metadata lookups to the central Nav registry.
 *
 * Resources can opt-in to the trait to remove duplicated navigation boilerplate while
 * keeping backwards compatibility with Filament's static API surface.
 */
trait HasNav
{
    /**
     * Resolve the translated navigation group label for the resource.
     */
    public static function getNavigationGroup(): ?string
    {
        $group = Nav::groupForResource(static::class);

        if ($group instanceof UnitEnum) {
            return $group->value ?? $group->name;
        }

        return $group;
    }

    /**
     * Resolve the navigation icon using the central Nav metadata map.
     */
    public static function getNavigationIcon(): Htmlable|string|null
    {
        $icon = Nav::iconForResource(static::class);

        if ($icon instanceof BackedEnum) {
            return $icon->value ?? $icon->name;
        }

        return $icon;
    }

    /**
     * Resolve the navigation sort order so resources render deterministically.
     */
    public static function getNavigationSort(): ?int
    {
        return Nav::sortForResource(static::class);
    }

    /**
     * Safely resolve resource URLs, falling back to a placeholder when routes are unavailable.
     */
    public static function getUrl(
        ?string $name = null,
        array $parameters = [],
        bool $isAbsolute = true,
        ?string $panel = null,
        ?\Illuminate\Database\Eloquent\Model $tenant = null,
        bool $shouldGuessMissingParameters = false,
        ?string $configuration = null
    ): string {
        try {
            return parent::getUrl(
                $name,
                $parameters,
                $isAbsolute,
                $panel,
                $tenant,
                $shouldGuessMissingParameters,
                $configuration,
            );
        } catch (Throwable) {
            return '#';
        }
    }
}
