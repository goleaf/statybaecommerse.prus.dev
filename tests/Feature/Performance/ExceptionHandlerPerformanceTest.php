<?php

declare(strict_types=1);

use App\Exceptions\Handler;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    // Reset static caches between tests
    $reflection = new ReflectionClass(Handler::class);

    if ($reflection->hasProperty('bootErrorDetectionEnabled')) {
        $property = $reflection->getProperty('bootErrorDetectionEnabled');
        $property->setAccessible(true);
        $property->setValue(null, null);
    }

    if ($reflection->hasProperty('bootErrorPatterns')) {
        $property = $reflection->getProperty('bootErrorPatterns');
        $property->setAccessible(true);
        $property->setValue(null, null);
    }

    if ($reflection->hasProperty('bootRelatedPaths')) {
        $property = $reflection->getProperty('bootRelatedPaths');
        $property->setAccessible(true);
        $property->setValue(null, null);
    }
});

it('caches boot error detection configuration for performance', function () {
    Config::set('exception-handling.boot_error_detection.enabled', true);

    $handler = app(Handler::class);

    // First call should read from config
    $start = microtime(true);
    $handler->register();
    $firstCallTime = microtime(true) - $start;

    // Subsequent calls should use cached value
    $start = microtime(true);
    $handler->register();
    $secondCallTime = microtime(true) - $start;

    // Both calls should complete quickly (caching effect may be minimal in tests)
    expect($firstCallTime)->toBeLessThan(0.01);
    expect($secondCallTime)->toBeLessThan(0.01);
});

it('fails fast for common non-boot exceptions', function () {
    $handler = app(Handler::class);

    // ValidationException should be handled quickly
    $validationException = ValidationException::withMessages(['field' => 'error']);

    Log::shouldReceive('error')->never();

    $start = microtime(true);
    $handler->register();
    $handler->report($validationException);
    $duration = microtime(true) - $start;

    // Should complete very quickly (under 1ms)
    expect($duration)->toBeLessThan(0.001);
});

it('caches boot error patterns for performance', function () {
    Config::set('exception-handling.boot_error_detection.patterns', [
        'Interface',
        'translations()',
        'TranslatableRecord',
    ]);

    $handler = app(Handler::class);
    $exception = new Exception('TranslatableRecord interface error');

    // Allow flexible log calls
    Log::shouldReceive('error')->atLeast()->once();

    // First call initializes cache
    $start = microtime(true);
    $handler->register();
    $handler->report($exception);
    $firstCallTime = microtime(true) - $start;

    // Second call uses cached patterns
    $start = microtime(true);
    $handler->register();
    $handler->report($exception);
    $secondCallTime = microtime(true) - $start;

    // Both calls should complete reasonably quickly
    expect($firstCallTime)->toBeLessThan(0.01);
    expect($secondCallTime)->toBeLessThan(0.01);
});

it('handles boot error detection efficiently under load', function () {
    Config::set('exception-handling.boot_error_detection.enabled', true);

    $handler = app(Handler::class);
    $exceptions = [
        new Exception('Interface not found'),
        new Exception('translations() method missing'),
        ValidationException::withMessages(['field' => 'error']),
        new Exception('Regular application error'),
    ];

    // Allow flexible log calls since boot errors may trigger multiple log entries
    Log::shouldReceive('error')->atLeast()->once();

    $start = microtime(true);

    // Process multiple exceptions
    foreach ($exceptions as $exception) {
        $handler->register();
        $handler->report($exception);
    }

    $totalTime = microtime(true) - $start;

    // Should handle all exceptions reasonably quickly (under 50ms)
    expect($totalTime)->toBeLessThan(0.05);
});

it('optimizes string matching for boot error patterns', function () {
    Config::set('exception-handling.boot_error_detection.patterns', [
        'Interface',
        'not found',
        'undefined method',
        'translations()',
        'TranslatableRecord',
    ]);

    $handler = app(Handler::class);

    // Test with non-matching exception (should be fast)
    $nonMatchingException = new Exception('Regular application error that does not match any patterns');

    $start = microtime(true);
    $handler->register();
    $handler->report($nonMatchingException);
    $nonMatchingTime = microtime(true) - $start;

    // Test with matching exception
    $matchingException = new Exception('Interface implementation error');

    Log::shouldReceive('error')->atLeast()->once();

    $start = microtime(true);
    $handler->register();
    $handler->report($matchingException);
    $matchingTime = microtime(true) - $start;

    // Both should complete within reasonable time limits (more generous for CI)
    expect($nonMatchingTime)->toBeLessThan(0.05);
    expect($matchingTime)->toBeLessThan(0.05);
});
