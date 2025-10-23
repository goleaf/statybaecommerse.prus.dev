<?php

declare(strict_types=1);

namespace App\UseCases\Menu;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

final class InvalidateMenuCache
{
    public const MENU_VERSION_KEY = 'menus:cache:version';

    public function __invoke(): void
    {
        Cache::forever(self::MENU_VERSION_KEY, Str::uuid()->toString());
    }
}
