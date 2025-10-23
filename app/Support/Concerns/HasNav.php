<?php

declare(strict_types=1);

namespace App\Support\Concerns;

use App\Support\Nav;
use BackedEnum;
use Illuminate\Contracts\Support\Htmlable;
use UnitEnum;

trait HasNav
{
    public static function getNavigationGroup(): UnitEnum|string|null
    {
        return Nav::groupForResource(static::class);
    }

    public static function getNavigationIcon(): BackedEnum|Htmlable|string|null
    {
        return Nav::iconForResource(static::class);
    }

    public static function getNavigationSort(): ?int
    {
        return Nav::sortForResource(static::class);
    }
}
