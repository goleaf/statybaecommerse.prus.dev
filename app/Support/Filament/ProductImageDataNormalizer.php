<?php

declare(strict_types=1);

namespace App\Support\Filament;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

final class ProductImageDataNormalizer
{
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
            $storedPath = $path->store('product-images', 'public');

            $path = is_string($storedPath) ? $storedPath : null;
        }

        if (is_string($path) && trim($path) !== '') {
            $data['path'] = trim($path);
        } elseif ($forUpdate) {
            unset($data['path']);
        }

        $data['sort_order'] = max(0, (int) ($data['sort_order'] ?? 0));
        $data['is_default'] = (bool) ($data['is_default'] ?? false);
        $data['is_active'] = (bool) ($data['is_active'] ?? true);

        return $data;
    }
}

