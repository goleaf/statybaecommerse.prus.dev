<?php

require_once __DIR__.'/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

$app = Application::configure(basePath: __DIR__)
    ->withRouting(
        web: __DIR__.'/routes/web.php',
        commands: __DIR__.'/routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();

$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Test NotificationSeeder
echo "Testing NotificationSeeder...\n";
try {
    $seeder = new \Database\Seeders\NotificationSeeder;
    $seeder->run();
    echo "✅ NotificationSeeder completed successfully\n";
} catch (Exception $e) {
    echo '❌ NotificationSeeder failed: '.$e->getMessage()."\n";
}

// Test OrderSeeder
echo "\nTesting OrderSeeder...\n";
try {
    $seeder = new \Database\Seeders\OrderSeeder;
    $seeder->run();
    echo "✅ OrderSeeder completed successfully\n";
} catch (Exception $e) {
    echo '❌ OrderSeeder failed: '.$e->getMessage()."\n";
}

// Test PartnerSeeder
echo "\nTesting PartnerSeeder...\n";
try {
    $seeder = new \Database\Seeders\PartnerSeeder;
    $seeder->run();
    echo "✅ PartnerSeeder completed successfully\n";
} catch (Exception $e) {
    echo '❌ PartnerSeeder failed: '.$e->getMessage()."\n";
}

echo "\nSeeder testing completed.\n";
