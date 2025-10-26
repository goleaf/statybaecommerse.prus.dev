<?php

declare(strict_types=1);

$providers = [
    App\Providers\AppServiceProvider::class,
    App\Providers\DebugServiceProvider::class,
    App\Providers\SharedComponentServiceProvider::class,
    App\Providers\CodeStyleServiceProvider::class,
    App\Providers\ExistsOrServiceProvider::class,
    App\Providers\Filament\AdminPanelProvider::class,
];

$env = function_exists('env') ? env('APP_ENV', 'production') : ($_ENV['APP_ENV'] ?? getenv('APP_ENV') ?? 'production');
$queueConnection = function_exists('env') ? env('QUEUE_CONNECTION', 'sync') : ($_ENV['QUEUE_CONNECTION'] ?? getenv('QUEUE_CONNECTION') ?? 'sync');

if ($env !== 'testing') {
    $providers[] = App\Providers\AdminNavigationServiceProvider::class;
    if (! ($env === 'local' && $queueConnection === 'sync')) {
        $providers[] = App\Providers\HorizonServiceProvider::class;
    }
    $providers[] = BezhanSalleh\FilamentShield\FilamentShieldServiceProvider::class;
} else {
    $providers[] = App\Providers\TestingLivewireAliasesProvider::class;
}

return $providers;
