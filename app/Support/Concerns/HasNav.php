<?php

declare(strict_types=1);

namespace App\Support\Concerns;

use App\Support\Nav;
use BackedEnum;
use Illuminate\Contracts\Support\Htmlable;
use UnitEnum;

/**
 * Trait that proxies Filament navigation lookups through the shared {@see Nav} helper.
 *
 * Applying this trait to a resource allows us to gradually migrate legacy resources
 * without rewriting their existing navigation metadata declarations. The trait simply
 * delegates to the helper which already handles reflection and caching.
 */
trait HasNav
{
    /**
     * Proxy the navigation group lookup through the helper.
     */
    public static function getNavigationGroup(): UnitEnum|string|null
    {
        return Nav::groupForResource(static::class);
    }

    /**
     * Proxy the navigation icon lookup through the helper.
     */
    public static function getNavigationIcon(): BackedEnum|Htmlable|string|null
    {
        return Nav::iconForResource(static::class);
    }

    /**
     * Proxy the navigation sort lookup through the helper.
     */
    public static function getNavigationSort(): ?int
    {
        return Nav::sortForResource(static::class);
    }
}
