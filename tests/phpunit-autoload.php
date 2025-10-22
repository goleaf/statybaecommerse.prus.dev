<?php

declare(strict_types=1);

/**
 * Ensure Composer's autoloader is registered before PHPUnit parses the configuration
 * so custom extensions can be instantiated without triggering class-not-found warnings.
 */
require __DIR__ . '/../vendor/autoload.php';
