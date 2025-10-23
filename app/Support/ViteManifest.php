<?php

declare(strict_types=1);

namespace App\Support;

use JsonException;

/**
 * Small helper responsible for inspecting the compiled Vite manifest.
 */
final class ViteManifest
{
    /**
     * Determine whether the Vite manifest exists and contains at least one valid entry.
     */
    public static function isPopulated(?string $path = null): bool
    {
        $path ??= self::manifestPath();

        if (! is_file($path)) {
            return false;
        }

        $contents = file_get_contents($path);

        if ($contents === false || $contents === '') {
            return false;
        }

        try {
            $manifest = json_decode($contents, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return false;
        }

        if (! is_array($manifest) || $manifest === []) {
            return false;
        }

        foreach ($manifest as $definition) {
            if (is_array($definition) && isset($definition['file']) && is_string($definition['file'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine whether the manifest contains a specific entry.
     */
    public static function hasEntry(string $key, ?string $path = null): bool
    {
        $path ??= self::manifestPath();

        if (! is_file($path)) {
            return false;
        }

        $contents = file_get_contents($path);

        if ($contents === false || $contents === '') {
            return false;
        }

        try {
            $manifest = json_decode($contents, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return false;
        }

        if (! is_array($manifest) || ! array_key_exists($key, $manifest)) {
            return false;
        }

        $definition = $manifest[$key];

        return is_array($definition) && isset($definition['file']) && is_string($definition['file']) && $definition['file'] !== '';
    }

    /**
     * Resolve the default manifest path used by the application.
     */
    public static function manifestPath(): string
    {
        return public_path('build/manifest.json');
    }
}

