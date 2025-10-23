<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Menu;
use App\UseCases\Menu\InvalidateMenuCache;

final class MenuObserver
{
    public function saved(Menu $menu): void
    {
        app(InvalidateMenuCache::class)();
    }

    public function deleted(Menu $menu): void
    {
        app(InvalidateMenuCache::class)();
    }
}
