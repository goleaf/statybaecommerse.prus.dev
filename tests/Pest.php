<?php declare(strict_types=1);

use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

uses(Tests\TestCase::class)->in('Feature', 'Unit', 'admin', 'frontend');

uses(RefreshDatabase::class)->in('Feature', 'Unit', 'admin', 'frontend');

beforeAll(function () {
    config()->set('database.default', 'sqlite');
    config()->set('database.connections.sqlite.database', ':memory:');

    // Ensure Filament uses the web guard for tests
    config()->set('filament.auth.guard', 'web');

    // Stub missing Filament resource routes referenced by navigation during tests
    if (!Route::has('filament.admin.resources.system-settings.index')) {
        Route::get('/__stub/system-settings', fn() => 'ok')
            ->name('filament.admin.resources.system-settings.index');
    }
});

beforeEach(function () {
    Filament::setCurrentPanel('admin');

    app(PermissionRegistrar::class)->forgetCachedPermissions();
    $guard = config('auth.defaults.guard', 'web');
    foreach (['admin', 'super_admin', 'Admin', 'administrator'] as $role) {
        if (!Role::where('name', $role)->where('guard_name', $guard)->exists()) {
            Role::findOrCreate($role, $guard);
        }
    }
});

function login($user = null)
{
    $user ??= \App\Models\User::factory()->create();

    return test()->actingAs($user);
}

if (!function_exists('actingAs')) {
    function actingAs($user)
    {
        return test()->actingAs($user);
    }
}

function get($uri, array $headers = [])
{
    return test()->get($uri, $headers);
}

function post($uri, array $data = [], array $headers = [])
{
    return test()->post($uri, $data, $headers);
}
