<?php

declare(strict_types=1);

use App\Models\Report;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Register the RefreshDatabase trait while the shared Pest bootstrap handles the Laravel TestCase wiring.
uses(RefreshDatabase::class);
// Explicitly bind the core Laravel TestCase so factories resolve the shared SQLite connection before executing.
uses(TestCase::class);

it('orders reports by their english name by default', function (): void {
    // Arrange: create reports with shuffled names to verify alphabetical ordering.
    $alpha = Report::factory()->create([
        'name' => ['en' => 'Alpha Report', 'lt' => 'Alfa Ataskaita'],
        'slug' => 'alpha-report',
    ]);

    $zeta = Report::factory()->create([
        'name' => ['en' => 'Zeta Report', 'lt' => 'Zeta Ataskaita'],
        'slug' => 'zeta-report',
    ]);

    $mega = Report::factory()->create([
        'name' => ['en' => 'Mega Report', 'lt' => 'Mega Ataskaita'],
        'slug' => 'mega-report',
    ]);

    // Act: order reports using the shared scope and capture the slugs for stable assertions.
    $ascending = Report::query()->orderedByName()->pluck('slug')->all();
    $descending = Report::query()->orderedByName('desc')->pluck('slug')->all();

    // Assert: ensure ascending and descending behaviours mirror alphabetical expectations.
    expect($ascending)->toBe([
        $alpha->slug,
        $mega->slug,
        $zeta->slug,
    ]);

    expect($descending)->toBe([
        $zeta->slug,
        $mega->slug,
        $alpha->slug,
    ]);
});

it('supports locale-aware ordering via the scope', function (): void {
    // Arrange: craft reports with differing Lithuanian translations to exercise the locale argument.
    $ltFirst = Report::factory()->create([
        'name' => ['en' => 'Omega Report', 'lt' => 'Alfa Ataskaita'],
        'slug' => 'omega-report',
    ]);

    $ltSecond = Report::factory()->create([
        'name' => ['en' => 'Beta Report', 'lt' => 'Beta Ataskaita'],
        'slug' => 'beta-report',
    ]);

    $ltThird = Report::factory()->create([
        'name' => ['en' => 'Gamma Report', 'lt' => 'Zeta Ataskaita'],
        'slug' => 'gamma-report',
    ]);

    // Act: request ordering using the locale shortcut argument to confirm backwards compatibility.
    $orderedLt = Report::query()->orderedByName('lt')->pluck('slug')->all();

    // Assert: ordering should respect the Lithuanian values alphabetically.
    expect($orderedLt)->toBe([
        $ltFirst->slug,
        $ltSecond->slug,
        $ltThird->slug,
    ]);
});
