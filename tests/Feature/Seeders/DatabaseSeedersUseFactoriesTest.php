<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;

// Apply the RefreshDatabase trait to guarantee each test runs against a clean database snapshot.
uses(RefreshDatabase::class);

/**
 * Ensure the database and localization configuration mirror the expectations of the seeders while keeping
 * the original values for subsequent tests.
 */
beforeEach(function (): void {
    // Store original configuration values so they can be restored after the test finishes.
    $this->originalSupportedLocales = config('app.supported_locales');
    $this->originalDefaultConnection = config('database.default');
    $this->originalSqliteDatabase = config('database.connections.sqlite.database');

    // Force the application to use SQLite in-memory database with the supported locales required by the factories.
    config()->set('app.supported_locales', 'lt,en');
    config()->set('database.default', 'sqlite');
    config()->set('database.connections.sqlite.database', ':memory:');
});

/**
 * Restore configuration mutated in the setup hook so the rest of the suite runs with the defaults.
 */
afterEach(function (): void {
    // Reinstate the previously saved configuration values to avoid side-effects in other tests.
    config()->set('app.supported_locales', $this->originalSupportedLocales);
    config()->set('database.default', $this->originalDefaultConnection);
    config()->set('database.connections.sqlite.database', $this->originalSqliteDatabase);
});

it('seeds attributes, brands, and analytics via factories', function (): void {
    // Execute the required seeders so the assertions can verify factory-backed data instead of static fixtures.
    $this->seed(Database\Seeders\AttributeSeeder::class);
    $this->seed(Database\Seeders\BrandSeeder::class);
    $this->seed(Database\Seeders\AnalyticsEventsSeeder::class);
    $this->seed(Database\Seeders\AttributeValueSeeder::class);
    $this->seed(Database\Seeders\BasicFilamentSeeder::class);

    // Confirm each seeded table receives the expected number of records sourced from the factories.
    $this->assertDatabaseCount('attributes', 16);
    $this->assertDatabaseCount('attribute_translations', 48);
    $this->assertDatabaseCount('brands', 10);
    $this->assertDatabaseCount('brand_translations', 20);

    // Ensure analytics events seeded through factories match the key event types tracked by the application.
    $this->assertDatabaseHas('analytics_events', ['event_type' => 'product_view']);
    $this->assertDatabaseHas('analytics_events', ['event_type' => 'add_to_cart']);
});
