<?php

declare(strict_types=1);

namespace App\Support\Media;

use Illuminate\Support\Arr;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

final class PlaceholderResolver
{
    /**
     * @var array<string, Media|null>
     */
    private array $cache = [];

    public function resolve(string $key, ?string $variant = null, ?string $default = null): ?string
    {
        $definition = Arr::get(config('media.placeholders', []), $key);

        if (! is_array($definition)) {
            return $default;
        }

        $media = $this->resolveMedia($key, $definition);

        if ($media === null) {
            return $this->resolveFallback($definition, $default);
        }

        $conversion = $this->determineConversion($definition, $variant);

        $url = $media->getUrl($conversion);

        if (is_string($url) && $url !== '') {
            return $url;
        }

        return $this->resolveFallback($definition, $default);
    }

    private function resolveMedia(string $key, array $definition): ?Media
    {
        if (array_key_exists($key, $this->cache)) {
            return $this->cache[$key];
        }

        if (Media::getConnectionResolver() === null) {
            return $this->cache[$key] = null;
        }

        $query = Media::query();

        if (isset($definition['uuid']) && is_string($definition['uuid']) && $definition['uuid'] !== '') {
            $query->where('uuid', $definition['uuid']);
        } elseif (isset($definition['id']) && is_numeric($definition['id'])) {
            $query->whereKey((int) $definition['id']);
        } else {
            return $this->cache[$key] = null;
        }

        return $this->cache[$key] = $query->first();
    }

    private function determineConversion(array $definition, ?string $variant): string
    {
        $conversion = $definition['conversion'] ?? null;

        if (is_array($definition['variants'] ?? null)) {
            $variants = $definition['variants'];
            if ($variant !== null) {
                $conversion = $variants[$variant] ?? $variant;
            } elseif (array_key_exists('default', $variants)) {
                $conversion = $variants['default'];
            }
        } elseif ($variant !== null) {
            $conversion = $variant;
        }

        return (string) ($conversion ?? '');
    }

    private function resolveFallback(array $definition, ?string $default): ?string
    {
        $fallback = $definition['fallback'] ?? $default;

        if (! is_string($fallback) || $fallback === '') {
            return $default;
        }

        if (str_starts_with($fallback, 'http://') || str_starts_with($fallback, 'https://')) {
            return $fallback;
        }

        return safe_asset($fallback);
    }
}
