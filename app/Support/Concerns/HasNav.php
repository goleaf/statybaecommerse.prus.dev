<?php

declare(strict_types=1);

namespace App\Support\Concerns;

use App\Support\Nav;
use BackedEnum;
use Filament\Navigation\NavigationGroup as FilamentNavigationGroup;
use Illuminate\Contracts\Support\Htmlable;
use Throwable;

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
    public static function getNavigationGroup(): FilamentNavigationGroup|array|string|null
    {
        return Nav::groupForResource(static::class);
    }

    /**
     * Resolve the navigation icon using the central Nav metadata map.
     */
    public static function getNavigationIcon(): BackedEnum|Htmlable|string|null
    {
        return Nav::iconForResource(static::class);
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
        bool $shouldGuessMissingParameters = false
    ): string {
        try {
            return parent::getUrl($name, $parameters, $isAbsolute, $panel, $tenant, $shouldGuessMissingParameters);
        } catch (Throwable) {
            return '#';
        }
    }
}
