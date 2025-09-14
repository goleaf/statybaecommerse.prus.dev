<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\DebugServiceProvider::class,
    App\Providers\Filament\AdminPanelProvider::class,
    App\Providers\HorizonServiceProvider::class,
    App\Providers\SharedComponentServiceProvider::class,
    App\Providers\CodeStyleServiceProvider::class,
    App\Providers\SqliteOptimizationServiceProvider::class,
    BezhanSalleh\FilamentShield\FilamentShieldServiceProvider::class,
];
