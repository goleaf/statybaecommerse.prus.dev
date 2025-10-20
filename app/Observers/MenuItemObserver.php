<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\MenuItem;
use App\UseCases\Menu\InvalidateMenuCache;

final class MenuItemObserver
{
    public function saved(MenuItem $menuItem): void
    {
        app(InvalidateMenuCache::class)();
    }

    public function deleted(MenuItem $menuItem): void
    {
        app(InvalidateMenuCache::class)();
    }
}
