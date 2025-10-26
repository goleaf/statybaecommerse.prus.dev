<?php

declare(strict_types=1);

use App\Models\Brand;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns brand listings sorted alphabetically', function (): void {
    // Prime predictable records to ensure the API endpoint exposes A→Z ordering.
    $late = Brand::factory()->create([
        'name'       => 'Zulu Tools',
        'slug'       => 'zulu-tools',
        'is_visible' => true,
    ]);
    $early = Brand::factory()->create([
        'name'       => 'Alpha Tools',
        'slug'       => 'alpha-tools',
        'is_visible' => true,
    ]);

    $response = $this->getJson(route('api.brands.index', ['per_page' => 10]));

    $response->assertOk();

    /** @var array<int, array{name: string}> $payload */
    $payload = $response->json('data', []);
    $names = array_column($payload, 'name');

    expect($names)
        ->toBe(['Alpha Tools', 'Zulu Tools'])
        // Ensure the payload still references the created records to guard against accidental filtering.
        ->and(collect($payload)->pluck('id')->sort()->values()->all())
        ->toBe(collect([$early->id, $late->id])->sort()->values()->all());
});
