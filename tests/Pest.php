<?php

declare(strict_types=1);

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Assert;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\Support\TestingDatabase;

// Centralise dataset registrations so Pest automatically discovers shared model datasets.
require __DIR__ . '/Support/ModelDatasets.php';

// Register the base TestCase for top-level feature, unit, and panel test suites.
uses(Tests\TestCase::class)->in('Feature', 'Unit', 'admin', 'frontend', 'Performance');
// Model-focused suites declare their own TestCase bindings to attach specific traits.

beforeAll(function (): void {
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
                'file'    => 'css/app.css',
                'src'     => 'resources/css/app.scss',
                'isEntry' => true,
            ],
            'resources/js/app.js' => [
                'file'    => 'js/app.js',
                'src'     => 'resources/js/app.js',
                'isEntry' => true,
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    // Stub missing Filament resource routes referenced by navigation during tests
    if (! Route::has('filament.admin.resources.system-settings.index')) {
        Route::get('/__stub/system-settings', fn (): string => 'ok')
            ->name('filament.admin.resources.system-settings.index');
    }

    $variantAttributeValueRoutes = [
        'index'  => '/__stub/variant-attribute-values',
        'create' => '/__stub/variant-attribute-values/create',
        'view'   => '/__stub/variant-attribute-values/{record}',
        'edit'   => '/__stub/variant-attribute-values/{record}/edit',
    ];

    foreach ($variantAttributeValueRoutes as $name => $uri) {
        $routeName = 'filament.admin.resources.variant-attribute-values.' . $name;
        if (! Route::has($routeName)) {
            Route::get($uri, fn (): string => 'ok')->name($routeName);
        }
    }

    $filamentResourceStubs = [
        'campaign-clicks'            => 'Campaign Clicks',
        'campaign-conversions'       => 'Campaign Conversions',
        'campaign-customer-segments' => 'Campaign Customer Segments',
        'campaign-product-targets'   => 'Campaign Product Targets',
    ];

    foreach (array_keys($filamentResourceStubs) as $slug) {
        $routes = [
            'index'  => "/__stub/{$slug}",
            'create' => "/__stub/{$slug}/create",
            'view'   => "/__stub/{$slug}/{record}",
            'edit'   => "/__stub/{$slug}/{record}/edit",
        ];

        foreach ($routes as $name => $uri) {
            $routeName = "filament.admin.resources.{$slug}.{$name}";

            if (Route::has($routeName)) {
                continue;
            }

            Route::get($uri, fn (): string => 'ok')->name($routeName);
        }
    }
});

beforeEach(function (): void {
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

if (! function_exists('markTestSkipped')) {
    /**
     * Allow Pest style tests to skip dynamically without depending on PHPUnit's base TestCase.
     */
    function markTestSkipped(string $reason): void
    {
        test()->markTestSkipped($reason);
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

    expect($contains)->toBeTrue();

    return $this;
});

expect()->extend('toBeIn', function (iterable $values) {
    $needle = $this->value instanceof \BackedEnum ? $this->value->value : $this->value;

    $normalizedHaystack = [];
    foreach ($values as $item) {
        $normalizedHaystack[] = $item instanceof \BackedEnum ? $item->value : $item;
    }

    // Delegate to PHPUnit for the actual assertion while keeping enum comparisons
    // compatible with legacy expectations that rely on raw string values.
    Assert::assertContains($needle, $normalizedHaystack);

    return $this;
});

expect()->extend('toContain', function ($expected) {
    $haystack = $this->value;

    $normalize = static function ($value) {
        return $value instanceof \BackedEnum ? $value->value : $value;
    };

    if (is_iterable($haystack)) {
        $normalized = [];
        foreach ($haystack as $item) {
            $normalized[] = $normalize($item);
        }

        Assert::assertContains($normalize($expected), $normalized);

        return $this;
    }

    if (is_string($haystack)) {
        Assert::assertStringContainsString((string) $expected, $haystack);

        return $this;
    }

    Assert::assertContains($expected, $haystack);

    return $this;
});
