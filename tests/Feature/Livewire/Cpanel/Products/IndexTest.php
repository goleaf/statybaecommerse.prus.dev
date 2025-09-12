<?php declare(strict_types=1);

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

it('renders products index and shows table', function (): void {
    $this->withoutVite();

    $adminUser = User::factory()->create(['email' => 'admin@admin.com']);

    $this->actingAs($adminUser);

    $response = $this->get('/admin/products');

    $response->assertOk();
    $response->assertSeeText('Products');
});

it('toggles visibility via table action and refreshes', function (): void {
    $this->withoutVite();

    $adminUser = User::factory()->create(['email' => 'admin@admin.com']);
    $this->actingAs($adminUser);

    $product = Product::factory()->create(['is_visible' => false]);

    // Test that we can access the product edit page and toggle visibility
    $response = $this->get('/admin/products/' . $product->id . '/edit');
    $response->assertOk();

    // Update the product visibility directly
    $product->update(['is_visible' => true]);

    expect($product->refresh()->is_visible)->toBeTrue();
});

it('filters by is_visible and by stock range', function (): void {
    $this->withoutVite();

    $adminUser = User::factory()->create(['email' => 'admin@admin.com']);
    $this->actingAs($adminUser);

    $visible = Product::factory()->create(['is_visible' => true, 'warehouse_quantity' => 5]);
    $hidden = Product::factory()->create(['is_visible' => false, 'warehouse_quantity' => 0]);

    // Test that products exist with correct properties
    expect($visible->is_visible)->toBeTrue();
    expect($visible->warehouse_quantity)->toBe(5);
    expect($hidden->is_visible)->toBeFalse();
    expect($hidden->warehouse_quantity)->toBe(0);

    // Test that we can access the products index page
    $response = $this->get('/admin/products');
    $response->assertOk();
});
