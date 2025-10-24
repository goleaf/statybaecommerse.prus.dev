<?php

declare(strict_types=1);

namespace App\Support\Media;

use Illuminate\Support\Facades\Storage;

use function asset;
use function array_unique;
use function str_starts_with;

/**
 * Normalises stored product image paths to browser-accessible URLs.
 */
final class ProductImageUrlResolver
{
    public static function resolve(?string $path): ?string
    {
        if ($path === null) {
            return null;
        }

        if ($path === '') {
            return $path;
        }

        foreach (['http://', 'https://'] as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return $path;
            }
        }

        if (str_starts_with($path, '/')) {
            return asset(ltrim($path, '/'));
        }

        $defaultDisk = config('filesystems.default', 'public');
        $disks = array_unique([$defaultDisk, 'public']);

        foreach ($disks as $disk) {
            if (Storage::disk($disk)->exists($path)) {
                return Storage::disk($disk)->url($path);
            }
        }

        return Storage::disk('public')->url($path);
    }
}

