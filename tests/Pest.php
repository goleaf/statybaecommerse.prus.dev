<?php

declare(strict_types=1);

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\Support\TestingDatabase;

uses(Tests\TestCase::class)->in('Feature', 'Unit', 'admin', 'frontend', 'Performance');


beforeAll(function () {
    $testingDatabasePath = TestingDatabase::path();
    TestingDatabase::ensureExists();

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
    config()->set('database.connections.sqlite.database', $testingDatabasePath);
    \Illuminate\Foundation\Testing\RefreshDatabaseState::$migrated = false;

    // Ensure Filament uses the web guard for tests (unless explicitly skipped)
    if (! getenv('SKIP_FILAMENT_BOOT')) {
        config()->set('filament.auth.guard', 'web');
    }

    $manifestPath = public_path('build/manifest.json');
    if (! is_dir(dirname($manifestPath))) {
        mkdir(dirname($manifestPath), 0777, true);
    }
    if (! file_exists($manifestPath)) {
        file_put_contents($manifestPath, json_encode([
            'resources/css/app.scss' => [
                'file' => 'css/app.css',
                'src' => 'resources/css/app.scss',
                'isEntry' => true,
            ],
            'resources/js/app.js' => [
                'file' => 'js/app.js',
                'src' => 'resources/js/app.js',
                'isEntry' => true,
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    // Stub missing Filament resource routes referenced by navigation during tests
    if (! Route::has('filament.admin.resources.system-settings.index')) {
        Route::get('/__stub/system-settings', fn () => 'ok')
            ->name('filament.admin.resources.system-settings.index');
    }

    $variantAttributeValueRoutes = [
        'index'  => '/__stub/variant-attribute-values',
        'create' => '/__stub/variant-attribute-values/create',
        'view'   => '/__stub/variant-attribute-values/{record}',
        'edit'   => '/__stub/variant-attribute-values/{record}/edit',
    ];

    foreach ($variantAttributeValueRoutes as $name => $uri) {
        $routeName = "filament.admin.resources.variant-attribute-values." . $name;
        if (! Route::has($routeName)) {
            Route::get($uri, fn () => 'ok')->name($routeName);
        }
    }
});

beforeEach(function () {
    if (! getenv('SKIP_FILAMENT_BOOT')) {
        Filament::setCurrentPanel('admin');

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $guard = config('auth.defaults.guard', 'web');
        foreach (['admin', 'super_admin', 'Admin', 'administrator'] as $role) {
            if (! Role::where('name', $role)->where('guard_name', $guard)->exists()) {
                Role::findOrCreate($role, $guard);
            }
        }
    }
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

expect()->extend('toHaveCountLessThan', function (int $expected) {
    $value = $this->value;

    // Count array-like values so assertions remain expressive in navigation tests.
    $actualCount = is_countable($value) ? count($value) : count((array) $value);

    expect($actualCount)->toBeLessThan($expected);

    return $this;
});

expect()->extend('toContainModel', function (Model $model) {
    $value = $this->value;

    // Normalise the assertion target into a collection for consistent comparisons.
    $items = $value instanceof \Illuminate\Support\Collection ? $value : collect($value);

    $contains = $items->contains(function ($item) use ($model) {
        if (! $item instanceof Model) {
            return false;
        }

        // Use the is() helper so soft deleted or cached instances still match strictly.
        return $item->is($model);
    });

    if ($this->negated) {
        expect($contains)->toBeFalse();

        return $this;
    }

    expect($contains)->toBeTrue();

    return $this;
});
