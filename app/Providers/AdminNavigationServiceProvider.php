<?php

declare(strict_types=1);

namespace App\Providers;

use App\Filament\Components\TopNavigation;
use Filament\Facades\Filament;
use Illuminate\Support\ServiceProvider;

class AdminNavigationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Filament::serving(function () {
            // Add custom render hook for top navigation
            Filament::renderHook(
                'panels::topbar.start',
                fn (): string => view('filament.components.admin-top-menu')->render()
            );
        });
    }
}
