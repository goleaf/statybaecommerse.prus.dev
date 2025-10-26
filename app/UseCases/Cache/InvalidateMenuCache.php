<?php

declare(strict_types=1);

namespace App\UseCases\Cache;

use App\Observers\Concerns\ResolvesSupportedLocales;
use App\Support\Cache\CacheKeys;
use App\Support\Cache\CacheTagHelper;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final class InvalidateMenuCache
{
    use ResolvesSupportedLocales;

    private const MENU_VERSION_KEY = 'menus:cache:version';

    public function __invoke(?int $menuId = null): void
    {
        if (CacheTagHelper::supportsTags()) {
            $tags = [CacheKeys::navigationTag()];

            if ($menuId !== null) {
                $tags[] = CacheKeys::menuTag($menuId);
            }

            Cache::tags($tags)->flush();

            return;
        }

        foreach ($this->supportedLocales() as $locale) {
            Cache::forget(CacheKeys::menuCollectionKey(null, $locale));
        }

        Log::debug('Menu caches invalidated via fallback path.', [
            'menu_id' => $menuId,
        ]);

        // Bump the shared menu version token to keep array-backed navigation
        // payloads consistent with the freshly flushed tag-aware caches.
        $this->bumpVersion(self::MENU_VERSION_KEY);
    }

    private function bumpVersion(string $key): void
    {
        // Persist a unique value so any cached front-end payloads that embed
        // the version token automatically detect invalidation events.
        Cache::forever($key, Str::uuid()->toString());
    }
}
