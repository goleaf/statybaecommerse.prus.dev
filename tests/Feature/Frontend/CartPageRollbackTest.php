<?php

declare(strict_types=1);

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows restored cart controls and action buttons on the cart page', function (): void {
    $brand = Brand::factory()->create([
        'is_active'  => true,
        'is_enabled' => true,
        'is_visible' => true,
    ]);
    $category = Category::factory()->create(['is_visible' => true]);
    $product = Product::factory()->create([
        'brand_id'     => $brand->id,
        'is_visible'   => true,
        'status'       => 'active',
        'published_at' => now(),
    ]);
    $product->categories()->attach($category->id);

    $response = $this->withSession([
        'cart' => [
            (string) $product->id => [
                'product_id' => $product->id,
                'name'       => $product->name,
                'price'      => 10.0,
                'quantity'   => 2,
                'sku'        => 'SKU-ROLLBACK',
            ],
        ],
    ])->get(route('frontend.cart.index'));

    $response->assertSuccessful();
    $response->assertViewIs('frontend.cart.index');
    $response->assertSee($product->name);
    $response->assertSee(__('translations.decrease_quantity'));
    $response->assertSee(__('translations.increase_quantity'));
    $response->assertSee(__('messages.remove'));
    $response->assertSee(route('frontend.cart.update'), false);
    $response->assertSee(route('frontend.cart.remove'), false);
    $response->assertSee(route('frontend.cart.clear'), false);
    $response->assertSee(__('frontend.cart.proceed_to_checkout'));
    $response->assertSee(__('frontend.cart.continue_shopping'));
});
