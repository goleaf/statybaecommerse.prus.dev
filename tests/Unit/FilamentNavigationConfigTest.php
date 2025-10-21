<?php

declare(strict_types=1);

use App\Filament\Pages\AdvancedReports;
use App\Filament\Pages\Dashboard;
use App\Filament\Resources\SystemSettingResource;

it('defines navigation groups in filament config', function (): void {
    $groups = config('filament.navigation.groups');

    expect($groups)
        ->toBeArray()
        ->not()->toBeEmpty();

    foreach ($groups as $group) {
        expect($group)
            ->toBeArray()
            ->toHaveKeys(['key', 'label', 'icon']);
    }
});

it('registers admin resources via config', function (): void {
    expect(config('filament.navigation.resources'))
        ->toBeArray()
        ->toContain(SystemSettingResource::class);
});

it('registers admin pages via config', function (): void {
    expect(config('filament.navigation.pages'))
        ->toBeArray()
        ->toContain(Dashboard::class)
        ->toContain(AdvancedReports::class);
});
