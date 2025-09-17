<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Facades\FilamentView;
use Illuminate\Contracts\View\View;

final class LiveNotificationProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel;
    }

    public function boot(): void
    {
        // Inject the live notification feed into the Filament layout
        FilamentView::registerRenderHook(
            'panels::topbar.end',
            fn (): View => view('filament.hooks.live-notification-feed-hook')
        );
    }
}
