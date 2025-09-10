<?php declare(strict_types=1);

it('has consolidated admin navigation labels configured', function (): void {
    expect(__('admin.navigation.dashboard'))->not()->toBe('admin.navigation.dashboard');
    expect(__('admin.navigation.commerce'))->not()->toBe('admin.navigation.commerce');
    expect(__('admin.navigation.marketing'))->not()->toBe('admin.navigation.marketing');
    expect(__('admin.navigation.content'))->not()->toBe('admin.navigation.content');
    expect(__('admin.navigation.analytics'))->not()->toBe('admin.navigation.analytics');
    expect(__('admin.navigation.system'))->not()->toBe('admin.navigation.system');
});
