<?php

declare(strict_types=1);

use App\Filament\AdminPanelProvider;
use Filament\Enums\UserMenuPosition;
use Filament\Panel;

it('disables the topbar and moves the user menu to the sidebar', function (): void {
    $provider = new AdminPanelProvider();

    $panel = $provider->panel(Panel::make());

    expect($panel->hasTopbar())->toBeFalse()
        ->and($panel->getUserMenuPosition())->toBe(UserMenuPosition::Sidebar);
});
