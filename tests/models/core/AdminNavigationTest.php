<?php

declare(strict_types=1);

it('has consolidated admin navigation labels configured', function (): void {
    // Set locale to Lithuanian to ensure translations work
    app()->setLocale('lt');

    // Test that translations exist and are not just the key
    expect(__('admin.navigation.dashboard'))->not()->toBe('admin.navigation.dashboard');
    expect(__('admin.navigation.marketing'))->not()->toBe('admin.navigation.marketing');
    expect(__('admin.navigation.content'))->not()->toBe('admin.navigation.content');
    expect(__('admin.navigation.analytics'))->not()->toBe('admin.navigation.analytics');
    expect(__('admin.navigation.system'))->not()->toBe('admin.navigation.system');

    expect(__('admin.navigation.commerce'))->toBe('Prekyba');
});
