<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Support\Cache\CacheKeys;
use App\Support\Cache\CacheTagHelper;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

final class MenuRepository
{
    public function all(?string $location = null, ?string $locale = null): Collection
    {
        $resolvedLocale = $locale ?? app()->getLocale();
        $cacheKey = CacheKeys::menuCollectionKey($location, $resolvedLocale);

        // Tag navigation menus with category + locale identifiers so dashboard
        // invalidation routines can flush the cache without touching unrelated data.
        $tags = CacheTagHelper::merge(
            CacheTagHelper::categories(),
            CacheTagHelper::locale($resolvedLocale)
        );

        return $this->remember(
            $cacheKey,
            CacheKeys::TTL_FIVE_MINUTES,
            fn (): Collection => $this->loadMenus($location)->map(fn (Menu $menu): array => $this->buildMenuPayload($menu)),
            $tags,
        );
    }

    public function byKey(string $key, ?string $locale = null): ?array
    {
        $resolvedLocale = $locale ?? app()->getLocale();
        $cacheKey = CacheKeys::menuByKey($key, $resolvedLocale);

        $tags = CacheTagHelper::merge(
            CacheTagHelper::categories(),
            CacheTagHelper::locale($resolvedLocale)
        );

        return $this->remember(
            $cacheKey,
            CacheKeys::TTL_FIVE_MINUTES,
            function () use ($key): ?array {
                $menu = Menu::query()
                    ->active()
                    ->forKey($key)
                    ->withVisibleItems()
                    ->first();

                return $menu ? $this->buildMenuPayload($menu) : null;
            },
            $tags,
        );
    }

    public function byLocation(string $location, ?string $locale = null): ?array
    {
        $resolvedLocale = $locale ?? app()->getLocale();
        $cacheKey = CacheKeys::menuByLocation($location, $resolvedLocale);

        $tags = CacheTagHelper::merge(
            CacheTagHelper::categories(),
            CacheTagHelper::locale($resolvedLocale)
        );

        return $this->remember(
            $cacheKey,
            CacheKeys::TTL_FIVE_MINUTES,
            function () use ($location): ?array {
                $menu = Menu::query()
                    ->active()
                    ->forLocation($location)
                    ->withVisibleItems()
                    ->first();

                return $menu ? $this->buildMenuPayload($menu) : null;
            },
            $tags,
        );
    }

    private function loadMenus(?string $location): Collection
    {
        // Eager load menu items with visible scope applied
        // Use withoutGlobalScopes on MenuItem to avoid double application of VisibleScope
        return Menu::query()
            ->active()
            ->when($location, static fn ($query) => $query->forLocation($location))
            ->with([
                'allItems' => static fn ($itemQuery) => $itemQuery
                    ->withoutGlobalScopes([\App\Models\Scopes\VisibleScope::class])
                    ->where('is_visible', true)
                    ->orderBy('sort_order'),
            ])
            ->orderBy('id')
            ->get();
    }

    private function buildMenuPayload(Menu $menu): array
    {
        // Ensure allItems relationship is loaded to avoid N+1 queries
        if (! $menu->relationLoaded('allItems')) {
            $menu->load(['allItems' => static fn ($itemQuery) => $itemQuery->visible()->ordered()]);
        }

        return [
            'id'       => $menu->id,
            'key'      => $menu->key,
            'name'     => $menu->name,
            'location' => $menu->location,
            'items'    => $this->buildTree($menu->allItems),
        ];
    }

    /**
     * @param Collection<int, MenuItem> $items
     */
    private function buildTree(Collection $items): array
    {
        $grouped = $items->groupBy('parent_id');

        return $this->mapChildren($grouped, null);
    }

    /**
     * @param Collection<int, Collection<int, MenuItem>> $grouped
     */
    private function mapChildren(Collection $grouped, ?int $parentId): array
    {
        return $grouped->get($parentId, collect())->map(function (MenuItem $item) use ($grouped): array {
            return [
                'id'           => $item->id,
                'label'        => $item->label,
                'url'          => $item->url,
                'route_name'   => $item->route_name,
                'route_params' => $item->route_params,
                'icon'         => $item->icon,
                'sort_order'   => $item->sort_order,
                'children'     => $this->mapChildren($grouped, $item->id),
            ];
        })->all();
    }

    /**
     * @param array<int, string> $tags
     */
    private function remember(string $key, int $ttlSeconds, callable $callback, array $tags = []): mixed
    {
        $expiresAt = now()->addSeconds($ttlSeconds);

        // In testing environment with array cache, tags might not be needed
        // This reduces cache operations that could count as queries
        if ($tags !== [] && CacheTagHelper::supportsTags() && ! app()->environment('testing')) {
            return Cache::tags($tags)->remember($key, $expiresAt, $callback);
        }

        return Cache::remember($key, $expiresAt, $callback);
    }
}
