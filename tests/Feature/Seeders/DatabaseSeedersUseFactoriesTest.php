<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;

it('seeds attributes, brands, and analytics via factories')
    ->uses(RefreshDatabase::class)
    ->tap(fn () => config()->set('app.supported_locales', 'lt,en'))
    ->tap(fn () => config()->set('database.default', 'sqlite'))
    ->tap(fn () => config()->set('database.connections.sqlite.database', ':memory:'))
    ->refreshDatabase()
    ->seed(Database\Seeders\AttributeSeeder::class)
    ->seed(Database\Seeders\BrandSeeder::class)
    ->seed(Database\Seeders\AnalyticsEventsSeeder::class)
    ->seed(Database\Seeders\AttributeValueSeeder::class)
    ->seed(Database\Seeders\BasicFilamentSeeder::class)
    ->assertDatabaseCount('attributes', 8)
    ->assertDatabaseCount('attribute_translations', 16)
    ->assertDatabaseCount('brands', 12)
    ->assertDatabaseCount('brand_translations', 24)
    ->assertDatabaseHas('analytics_events', ['event_type' => 'product_view'])
    ->assertDatabaseHas('analytics_events', ['event_type' => 'add_to_cart']);
