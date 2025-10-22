<?php

declare(strict_types=1);

/**
 * Bootstrap file ensures PHPUnit loads Composer and Pest hooks when running without pest runner.
 */
require __DIR__ . '/../vendor/autoload.php';

Pest\TestSuite::getInstance(dirname(__DIR__), 'tests');

// Load the Pest configuration so PHPUnit execution honours Pest helpers and hooks.
if (file_exists(__DIR__ . '/Pest.php')) {
    require __DIR__ . '/Pest.php';
}
