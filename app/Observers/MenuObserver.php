<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Menu;
use App\UseCases\Cache\InvalidateMenuCache;

final class MenuObserver
{
    public function __construct(
        private readonly InvalidateMenuCache $invalidateMenuCache,
    ) {}

    public function created(Menu $menu): void
    {
        ($this->invalidateMenuCache)($menu->id);
    }

    public function updated(Menu $menu): void
    {
        ($this->invalidateMenuCache)($menu->id);
    }

    public function deleted(Menu $menu): void
    {
        ($this->invalidateMenuCache)($menu->id);
    }

    public function restored(Menu $menu): void
    {
        ($this->invalidateMenuCache)($menu->id);
    }

    public function forceDeleted(Menu $menu): void
    {
        ($this->invalidateMenuCache)($menu->id);
    }
}
