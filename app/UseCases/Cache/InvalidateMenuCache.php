<?php

declare(strict_types=1);

namespace App\UseCases\Cache;

use App\Observers\Concerns\ResolvesSupportedLocales;
use App\Support\Cache\CacheKeys;
use App\Support\Cache\CacheTagHelper;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

final class InvalidateMenuCache
{
    use ResolvesSupportedLocales;

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
    }
}
