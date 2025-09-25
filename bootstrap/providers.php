<?php

$providers = [
    App\Providers\AppServiceProvider::class,
    App\Providers\DebugServiceProvider::class,
    App\Providers\SharedComponentServiceProvider::class,
    App\Providers\CodeStyleServiceProvider::class,
    App\Providers\ExistsOrServiceProvider::class,
    App\Providers\Filament\AdminPanelProvider::class,
];

$env = function_exists('env') ? env('APP_ENV', 'production') : ($_ENV['APP_ENV'] ?? getenv('APP_ENV') ?? 'production');

if ($env !== 'testing') {
    $providers[] = App\Providers\AdminNavigationServiceProvider::class;
    $providers[] = App\Providers\HorizonServiceProvider::class;
    $providers[] = BezhanSalleh\FilamentShield\FilamentShieldServiceProvider::class;
} else {
    $providers[] = App\Providers\TestingLivewireAliasesProvider::class;
}

return $providers;
