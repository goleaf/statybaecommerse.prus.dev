<?php

declare(strict_types=1);

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Pest\Expectation;
use PHPUnit\Framework\Assert;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

uses(Tests\TestCase::class)->in('Feature', 'Unit', 'admin', 'frontend', 'Performance');

uses(RefreshDatabase::class)->in('Feature', 'Unit', 'admin', 'frontend', 'Performance');

beforeAll(function () {
    $envPath = base_path('.env');
    if (! file_exists($envPath)) {
        file_put_contents($envPath, implode(PHP_EOL, [
            'APP_NAME="StatybaEcommerce"',
            'APP_ENV=testing',
            'APP_KEY=base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=',
            'APP_DEBUG=true',
            'LOG_CHANNEL=stack',
            'DB_CONNECTION=sqlite',
            'DB_DATABASE=:memory:',
        ]) . PHP_EOL);
    }

    config()->set('database.default', 'sqlite');
    config()->set('database.connections.sqlite.database', ':memory:');

    // Ensure Filament uses the web guard for tests
    config()->set('filament.auth.guard', 'web');

    // Stub missing Filament resource routes referenced by navigation during tests
    if (! Route::has('filament.admin.resources.system-settings.index')) {
        Route::get('/__stub/system-settings', fn () => 'ok')
            ->name('filament.admin.resources.system-settings.index');
    }
});

beforeEach(function () {
    Filament::setCurrentPanel('admin');

    app(PermissionRegistrar::class)->forgetCachedPermissions();
    $guard = config('auth.defaults.guard', 'web');
    foreach (['admin', 'super_admin', 'Admin', 'administrator'] as $role) {
        if (! Role::where('name', $role)->where('guard_name', $guard)->exists()) {
            Role::findOrCreate($role, $guard);
        }
    }
});

expect()->extend('toContain', function ($expected): Expectation {
    $value = $this->value;

    if ($value instanceof Collection && $expected instanceof Model) {
        Assert::assertTrue(
            $value->contains(static fn ($item): bool => $item instanceof Model && $item->is($expected)),
            sprintf('Failed asserting that the collection contains model [%s].', $expected->getKey())
        );

        return $this;
    }

    Assert::assertContains($expected, $value);

    return $this;
});

if (! function_exists('login')) {
    /**
     * Log in a user for Pest tests while preventing duplicate helper declarations.
     */
    function login($user = null)
    {
        $user ??= \App\Models\User::factory()->create();

        return test()->actingAs($user);
    }
}

if (! function_exists('actingAs')) {
    function actingAs($user)
    {
        return test()->actingAs($user);
    }
}

if (! function_exists('get')) {
    /**
     * Provide a helper wrapper around the Pest test() helper for GET requests while
     * avoiding redeclaration when the file is loaded multiple times by Pest.
     */
    function get($uri, array $headers = [])
    {
        return test()->get($uri, $headers);
    }
}

if (! function_exists('post')) {
    /**
     * Provide a helper wrapper around the Pest test() helper for POST requests while
     * avoiding redeclaration when the file is loaded multiple times by Pest.
     */
    function post($uri, array $data = [], array $headers = [])
    {
        return test()->post($uri, $data, $headers);
    }
}
