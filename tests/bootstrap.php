<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

if (class_exists(Pest\TestSuite::class)) {
    Pest\TestSuite::getInstance(dirname(__DIR__), 'tests');
}
