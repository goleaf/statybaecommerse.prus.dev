<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

uses(Tests\TestCase::class)->in('Feature', 'Unit', 'admin');

uses(RefreshDatabase::class)->in('Feature', 'Unit', 'admin');

beforeAll(function () {
    config()->set('database.default', 'sqlite');
    config()->set('database.connections.sqlite.database', ':memory:');
});

beforeEach(function () {
    app(PermissionRegistrar::class)->forgetCachedPermissions();
    $guard = config('auth.defaults.guard', 'web');
    foreach (['admin', 'super_admin', 'Admin', 'administrator'] as $role) {
        if (! Role::where('name', $role)->where('guard_name', $guard)->exists()) {
            Role::findOrCreate($role, $guard);
        }
    }
});

function login($user = null)
{
    $user ??= \App\Models\User::factory()->create();

    return test()->actingAs($user);
}

if (! function_exists('actingAs')) {
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
