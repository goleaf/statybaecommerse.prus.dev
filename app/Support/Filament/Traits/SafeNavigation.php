<?php

declare(strict_types=1);

namespace App\Support\Filament\Traits;

use Illuminate\Support\Str;

/**
 * Centralizes Filament navigation hardening so panel boot remains stable during automated tests.
 */
trait SafeNavigation
{
    /**
     * Disable navigation registration when running PHPUnit while delegating to any parent behaviour otherwise.
     */
    public static function shouldRegisterNavigation(): bool
    {
        if (app()->runningUnitTests()) {
            // Prevent Filament from attempting to resolve admin routes when HTTP layer is not booted inside tests.
            return false;
        }

        return static::shouldRegisterNavigationWhenLive();
    }

    /**
     * Allow resources to customize their runtime navigation checks while providing a safe default fallback.
     */
    protected static function shouldRegisterNavigationWhenLive(): bool
    {
        $parentClass = get_parent_class(static::class);

        if (is_string($parentClass) && method_exists($parentClass, 'shouldRegisterNavigation')) {
            // Respect custom logic declared on Filament\Resources\Resource.
            return parent::shouldRegisterNavigation();
        }

        return true;
    }

    /**
     * Resolve translation keys gracefully by falling back to provided defaults when localisation entries are missing.
     */
    protected static function translateString(string $key, string $fallback = ''): string
    {
        $translation = __($key);

        if (is_array($translation)) {
            // When the translation key resolves to an array we fall back to the provided label.
            return $fallback !== '' ? $fallback : $key;
        }

        $translation = (string) $translation;

        if (Str::of($translation)->trim()->isNotEmpty()) {
            return $translation;
        }

        return $fallback !== '' ? $fallback : $key;
    }
}
