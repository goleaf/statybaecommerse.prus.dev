<?php

declare(strict_types=1);

use Filament\Facades\Filament;
use Tests\TestCase;

uses(TestCase::class);

/**
 * Verify a minimal Filament interaction so CI environments confirm the admin
 * panel container is bootable before heavier resource tests execute.
 */
it('resolves the admin panel container', function (): void {
    // Align with the default panel identifier used throughout the application.
    Filament::setCurrentPanel('admin');

    // Ensure the resolved panel is not null so navigation builders remain stable.
    expect(Filament::getCurrentPanel())->not->toBeNull();
});
