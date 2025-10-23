<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\MenuItem;
use App\UseCases\Cache\InvalidateMenuCache;

final class MenuItemObserver
{
    public function __construct(
        private readonly InvalidateMenuCache $invalidateMenuCache,
    ) {}

    public function created(MenuItem $menuItem): void
    {
        ($this->invalidateMenuCache)($menuItem->menu_id);
    }

    public function updated(MenuItem $menuItem): void
    {
        ($this->invalidateMenuCache)($menuItem->menu_id);
    }

    public function deleted(MenuItem $menuItem): void
    {
        ($this->invalidateMenuCache)($menuItem->menu_id);
    }

    public function restored(MenuItem $menuItem): void
    {
        ($this->invalidateMenuCache)($menuItem->menu_id);
    }

    public function forceDeleted(MenuItem $menuItem): void
    {
        ($this->invalidateMenuCache)($menuItem->menu_id);
    }
}
