<?php

declare(strict_types=1);

namespace App\Support\Contracts;

final class ContractPathResolver
{
    public static function schema(string $filename): string
    {
        return self::resolve("contracts/v1/{$filename}", "contracts/entities/v1/{$filename}");
    }

    public static function example(string $filename): string
    {
        return self::resolve("contracts/v1/examples/{$filename}", "contracts/entities/v1/examples/{$filename}");
    }

    private static function resolve(string $primaryRelative, ?string $fallbackRelative = null): string
    {
        $primaryPath = resource_path($primaryRelative);

        if (is_file($primaryPath)) {
            return $primaryPath;
        }

        if ($fallbackRelative !== null) {
            $fallbackPath = resource_path($fallbackRelative);

            if (is_file($fallbackPath)) {
                return $fallbackPath;
            }
        }

        return $primaryPath;
    }
}

