<?php

declare(strict_types=1);

namespace App\Support\Media;

use App\Support\Storage\SecureStorage;

use function array_unique;
use function asset;

use Illuminate\Support\Facades\Storage;

use function str_starts_with;

use Throwable;

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

        $secureDisk = SecureStorage::disk();
        $defaultDisk = config('filesystems.default', 'public');
        $disks = array_values(array_unique(array_filter(['public', $secureDisk, $defaultDisk], static fn (mixed $disk): bool => is_string($disk) && $disk !== '')));

        foreach ($disks as $disk) {
            try {
                if (! Storage::disk($disk)->exists($path)) {
                    continue;
                }
            } catch (Throwable) {
                continue;
            }

            if ($disk === $secureDisk) {
                return SecureStorage::temporarySignedUrl($path);
            }

            return Storage::disk($disk)->url($path);
        }

        return Storage::disk('public')->url($path);
    }
}
