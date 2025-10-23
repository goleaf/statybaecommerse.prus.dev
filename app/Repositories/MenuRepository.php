<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Menu;
use App\UseCases\Menu\InvalidateMenuCache;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

final class MenuRepository
{
    private const CACHE_TTL_MINUTES = 15;

    public function __construct(private readonly CacheRepository $cache)
    {
    }

    public function getActiveMenus(?string $location = null): Collection
    {
        $version = $this->cacheVersion(InvalidateMenuCache::MENU_VERSION_KEY);
        $cacheKey = sprintf('menus:list:%s:%s', $version, $location ?? 'all');

        return $this->cache->remember($cacheKey, now()->addMinutes(self::CACHE_TTL_MINUTES), static function () use ($location) {
            return Menu::query()
                ->when($location, static function ($query) use ($location): void {
                    $query->where('location', $location);
                })
                ->with(['allItems' => static function ($query): void {
                    $query->orderBy('sort_order');
                }])
                ->orderBy('name')
                ->get();
        });
    }

    public function findActiveMenuByKey(string $key): ?Menu
    {
        $version = $this->cacheVersion(InvalidateMenuCache::MENU_VERSION_KEY);
        $cacheKey = sprintf('menus:single:%s:key:%s', $version, $key);

        return $this->cache->remember($cacheKey, now()->addMinutes(self::CACHE_TTL_MINUTES), static function () use ($key) {
            return Menu::query()
                ->where('key', $key)
                ->with(['allItems' => static function ($query): void {
                    $query->orderBy('sort_order');
                }])
                ->first();
        });
    }

    public function findActiveMenuByLocation(string $location): ?Menu
    {
        $version = $this->cacheVersion(InvalidateMenuCache::MENU_VERSION_KEY);
        $cacheKey = sprintf('menus:single:%s:location:%s', $version, $location);

        return $this->cache->remember($cacheKey, now()->addMinutes(self::CACHE_TTL_MINUTES), static function () use ($location) {
            return Menu::query()
                ->where('location', $location)
                ->with(['allItems' => static function ($query): void {
                    $query->orderBy('sort_order');
                }])
                ->first();
        });
    }

    private function cacheVersion(string $key): string
    {
        return $this->cache->rememberForever($key, static fn (): string => Str::uuid()->toString());
    }
}
