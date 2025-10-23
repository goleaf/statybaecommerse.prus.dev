<?php

declare(strict_types=1);

use App\Models\Product;

it('returns displayable products from the search use case', function (): void {
    $matching = Product::factory()->create([
        'name' => 'Profesionalus plaktukas',
        'slug' => 'profesionalus-plaktukas',
        'is_visible' => true,
        'price' => 120.0,
    ]);

    Product::factory()->create([
        'name' => 'Paslėptas įrankis',
        'slug' => 'pasleptas-irankis',
        'is_visible' => false,
        'price' => 99.0,
    ]);

    $response = $this->getJson('/api/products/search?q=plaktukas&limit=5');

    $response->assertOk()
        ->assertJsonPath('data.items.0.slug', $matching->slug)
        ->assertJsonPath('data.items.0.name', $matching->name)
        ->assertJsonPath('meta.query', 'plaktukas');
});
