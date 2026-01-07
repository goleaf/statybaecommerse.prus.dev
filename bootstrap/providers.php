<?php

declare(strict_types=1);

return [
    App\Filament\AdminPanelProvider::class,
    App\Providers\AdminNavigationServiceProvider::class,
    App\Providers\AppServiceProvider::class,
    App\Providers\CodeStyleServiceProvider::class,
    App\Providers\DebugServiceProvider::class,
    App\Providers\ExistsOrServiceProvider::class,
    App\Providers\SharedComponentServiceProvider::class,
    App\Providers\TranslationHookServiceProvider::class,
    App\Providers\VersionCompatibilityServiceProvider::class,
    BezhanSalleh\FilamentShield\FilamentShieldServiceProvider::class,
];
