<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders the search index page without errors', function (): void {
    Category::factory()->create([
        'name'      => 'Tools',
        'is_active' => true,
    ]);

    $response = $this->get(route('frontend.search.index'));

    $response->assertOk();
    $response->assertViewIs('frontend.search.index');
    $response->assertSee(__('frontend.search.help'));
});

it('returns suggestion urls using the frontend product route', function (): void {
    $product = Product::factory()->create([
        'name'         => 'Precision Hammer',
        'slug'         => 'precision-hammer',
        'is_visible'   => true,
        'status'       => 'published',
        'published_at' => now()->subDay(),
    ]);
    $product->forceFill(['is_active' => true])->save();

    $response = $this->getJson(route('frontend.search.suggestions', ['q' => 'Hammer']));

    $response->assertOk();
    $response->assertJsonStructure([
        '*' => ['id', 'name', 'url'],
    ]);

    $expectedUrl = route('frontend.products.show', $product);

    expect($response->json('0.url'))->toBe($expectedUrl);
});

it('returns autocomplete data for valid queries', function (): void {
    $product = Product::factory()->create([
        'name'         => 'Cordless Drill',
        'is_visible'   => true,
        'status'       => 'published',
        'published_at' => now()->subDay(),
    ]);
    $product->forceFill(['is_active' => true])->save();

    $response = $this->getJson(route('frontend.search.autocomplete', ['q' => 'Dr']));

    $response->assertOk();
    $response->assertJson(fn ($json) => $json->each(fn ($item) => $item->hasAll(['value', 'type'])));
});

it('sanitizes catalogue search queries before applying filters', function (): void {
    $product = Product::factory()->create([
        'name'         => 'Safety Helmet Pro',
        'description'  => 'Industrial grade protection for active worksites.',
        'is_visible'   => true,
        'status'       => 'published',
        'published_at' => now()->subDay(),
    ]);
    $product->forceFill(['is_active' => true])->save();

    // Include a category to ensure the controller still responds with a hydrated dataset.
    $category = Category::factory()->create([
        'name'      => 'Safety',
        'is_active' => true,
    ]);
    $product->categories()->attach($category->id);

    $response = $this->get(route('frontend.search.index', ['q' => '<script>alert(1)</script> Helmet']));

    $response->assertOk();
    $response->assertViewHas('query', 'alert(1) Helmet');
    $response->assertSee('Safety Helmet Pro');
});

it('strips unsafe characters from suggestion queries', function (): void {
    $response = $this->getJson(route('frontend.search.suggestions', ['q' => '<>']))
        ->assertOk();

    expect($response->json())->toBeArray()->toBeEmpty();
});
