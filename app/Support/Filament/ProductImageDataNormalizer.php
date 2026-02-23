<?php

declare(strict_types=1);

namespace App\Support\Filament;

use App\Support\Storage\SecureStorage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

final class ProductImageDataNormalizer
{
    private const PRODUCT_IMAGES_DIRECTORY = 'product-images';

    /**
     * @param  array<string, mixed> $data
     * @return array<string, mixed>
     */
    public static function normalize(array $data, bool $forUpdate = false): array
    {
        $path = $data['path'] ?? null;

        if (is_array($path)) {
            $path = Arr::first($path);
        }

        if ($path instanceof TemporaryUploadedFile || $path instanceof UploadedFile) {
            $storedPath = $path->store(self::PRODUCT_IMAGES_DIRECTORY, SecureStorage::disk());

            $path = is_string($storedPath) ? $storedPath : null;
        }

        if (is_string($path) && trim($path) !== '') {
            $normalizedPath = self::normalizePath(trim($path));

            if ($normalizedPath !== null) {
                $data['path'] = $normalizedPath;
            } elseif ($forUpdate) {
                unset($data['path']);
            } else {
                $data['path'] = null;
            }
        } elseif ($forUpdate) {
            unset($data['path']);
        }

        $data['sort_order'] = max(0, (int) ($data['sort_order'] ?? 0));
        $data['is_default'] = (bool) ($data['is_default'] ?? false);
        $data['is_active'] = (bool) ($data['is_active'] ?? true);

        return $data;
    }

    private static function normalizePath(string $path): ?string
    {
        $normalized = trim(str_replace('\\', '/', $path), '/');

        if ($normalized === '' || str_contains($normalized, '../')) {
            return null;
        }

        foreach (['public/', 'storage/', 'app/public/', 'app/secure-media/'] as $prefix) {
            if (str_starts_with($normalized, $prefix)) {
                $normalized = ltrim(substr($normalized, strlen($prefix)), '/');
                break;
            }
        }

        if (! str_starts_with($normalized, self::PRODUCT_IMAGES_DIRECTORY . '/')) {
            return null;
        }

        return $normalized;
    }
}
