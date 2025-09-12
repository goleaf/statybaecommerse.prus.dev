<?php

// Simple test runner for AnalyticsEventResourceTest
require_once __DIR__ . '/vendor/autoload.php';

// Set up Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Run the specific test
$exitCode = 0;
try {
    $process = new Symfony\Component\Process\Process([
        'php', 'artisan', 'test', 'tests/Feature/Filament/AnalyticsEventResourceTest.php', '--stop-on-failure'
    ], __DIR__);
    
    $process->run();
    $exitCode = $process->getExitCode();
    
    echo $process->getOutput();
    if ($process->getErrorOutput()) {
        echo "Error output:\n" . $process->getErrorOutput();
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    $exitCode = 1;
}

exit($exitCode);
