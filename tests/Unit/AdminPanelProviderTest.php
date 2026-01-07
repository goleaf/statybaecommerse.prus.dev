<?php

declare(strict_types=1);

use App\Filament\AdminPanelProvider;
use Filament\Panel;
use Illuminate\Foundation\Testing\RefreshDatabaseState;

beforeEach(function (): void {
    RefreshDatabaseState::$migrated = true;
});

it('unit: disables the topbar', function (): void {
    $provider = new AdminPanelProvider(app());

    $panel = $provider->panel(Panel::make());

    expect($panel->hasTopbar())->toBeFalse();
});
