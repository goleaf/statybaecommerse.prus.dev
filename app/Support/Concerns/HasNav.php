<?php

declare(strict_types=1);

namespace App\Support\Concerns;

use App\Support\Nav;
use BackedEnum;
use Illuminate\Contracts\Support\Htmlable;
use UnitEnum;

/**
 * Trait that exposes centralised navigation metadata helpers to Filament resources.
 */
trait HasNav
{
    /**
     * Delegate group resolution to the shared navigation helper.
     */
    public static function getNavigationGroup(): UnitEnum|string|null
    {
        return Nav::groupForResource(static::class);
    }

    /**
     * Delegate icon resolution to the shared navigation helper.
     */
    public static function getNavigationIcon(): BackedEnum|Htmlable|string|null
    {
        return Nav::iconForResource(static::class);
    }

    /**
     * Delegate sort resolution to the shared navigation helper.
     */
    public static function getNavigationSort(): ?int
    {
        return Nav::sortForResource(static::class);
    }
}
