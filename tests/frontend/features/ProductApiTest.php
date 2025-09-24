<?php

declare(strict_types=1);

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;

uses(RefreshDatabase::class);

describe('Product API', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->admin = User::factory()->create(['is_admin' => true]);

        $this->category = Category::factory()->create(['is_visible' => true]);
        $this->brand = Brand::factory()->create(['is_enabled' => true]);

        $this->product = Product::factory()->create([
            'name' => 'Test Product',
            'description' => 'Test Description',
            'is_visible' => true,
            'brand_id' => $this->brand->id,
        ]);

        $this->product->categories()->attach($this->category->id);

        ProductVariant::factory()->create([
            'product_id' => $this->product->id,
            'price' => 9999,  // $99.99
            'stock_quantity' => 10,
        ]);
    });

    describe('GET /api/products', function () {
        it('returns paginated list of visible products', function () {
            Product::factory()->count(15)->create(['is_visible' => true]);
            Product::factory()->count(5)->create(['is_visible' => false]);

            $response = $this->getJson('/api/products');

            $response
                ->assertOk()
                ->assertJson(fn (AssertableJson $json) => $json
                    ->has('data', 15)
                    ->has('meta')
                    ->has('links')
                    ->where('meta.total', 16)  // 15 + 1 from beforeEach
                );
        });

        it('filters products by category', function () {
            $otherCategory = Category::factory()->create(['is_visible' => true]);
            $otherProduct = Product::factory()->create(['is_visible' => true]);
            $otherProduct->categories()->attach($otherCategory->id);

            $response = $this->getJson('/api/products?category='.$this->category->id);

            $response
                ->assertOk()
                ->assertJson(fn (AssertableJson $json) => $json
                    ->has('data', 1)
                    ->where('data.0.id', $this->product->id));
        });

        it('filters products by brand', function () {
            $otherBrand = Brand::factory()->create(['is_enabled' => true]);
            Product::factory()->create(['is_visible' => true, 'brand_id' => $otherBrand->id]);

            $response = $this->getJson('/api/products?brand='.$this->brand->id);

            $response
                ->assertOk()
                ->assertJson(fn (AssertableJson $json) => $json
                    ->has('data', 1)
                    ->where('data.0.id', $this->product->id));
        });

        it('searches products by name', function () {
            Product::factory()->create(['name' => 'Different Product', 'is_visible' => true]);

            $response = $this->getJson('/api/products?search=Test');

            $response
                ->assertOk()
                ->assertJson(fn (AssertableJson $json) => $json
                    ->has('data', 1)
                    ->where('data.0.name', 'Test Product'));
        });

        it('sorts products by price', function () {
            $expensiveProduct = Product::factory()->create(['is_visible' => true]);
            ProductVariant::factory()->create([
                'product_id' => $expensiveProduct->id,
                'price' => 19999,  // $199.99
            ]);

            $response = $this->getJson('/api/products?sort=price&direction=asc');

            $response
                ->assertOk()
                ->assertJson(fn (AssertableJson $json) => $json
                    ->where('data.0.id', $this->product->id)
                    ->where('data.1.id', $expensiveProduct->id));
        });

        it('includes product relationships', function () {
            $response = $this->getJson('/api/products?include=brand,categories,variants');

            $response
                ->assertOk()
                ->assertJson(fn (AssertableJson $json) => $json
                    ->has('data.0.brand')
                    ->has('data.0.categories')
                    ->has('data.0.variants'));
        });
    });

    describe('GET /api/products/{id}', function () {
        it('returns single product with details', function () {
            $response = $this->getJson('/api/products/'.$this->product->id);

            $response
                ->assertOk()
                ->assertJson(fn (AssertableJson $json) => $json
                    ->where('id', $this->product->id)
                    ->where('name', 'Test Product')
                    ->where('description', 'Test Description')
                    ->has('brand')
                    ->has('categories')
                    ->has('variants'));
        });

        it('returns 404 for non-existent product', function () {
            $response = $this->getJson('/api/products/99999');

            $response->assertNotFound();
        });

        it('returns 404 for invisible product', function () {
            $invisibleProduct = Product::factory()->create(['is_visible' => false]);

            $response = $this->getJson('/api/products/'.$invisibleProduct->id);

            $response->assertNotFound();
        });
    });

    describe('POST /api/products', function () {
        it('creates product when authenticated as admin', function () {
            $productData = [
                'name' => 'New Product',
                'description' => 'New Description',
                'brand_id' => $this->brand->id,
                'is_visible' => true,
                'categories' => [$this->category->id],
                'variants' => [
                    [
                        'sku' => 'NEW-001',
                        'price' => 4999,
                        'stock_quantity' => 5,
                    ],
                ],
            ];

            $response = $this
                ->actingAs($this->admin, 'sanctum')
                ->postJson('/api/products', $productData);

            $response
                ->assertCreated()
                ->assertJson(fn (AssertableJson $json) => $json
                    ->where('name', 'New Product')
                    ->where('description', 'New Description')
                    ->has('id'));

            $this->assertDatabaseHas('products', [
                'name' => 'New Product',
                'description' => 'New Description',
            ]);
        });

        it('requires authentication', function () {
            $response = $this->postJson('/api/products', []);

            $response->assertUnauthorized();
        });

        it('requires admin privileges', function () {
            $response = $this
                ->actingAs($this->user, 'sanctum')
                ->postJson('/api/products', []);

            $response->assertForbidden();
        });

        it('validates required fields', function () {
            $response = $this
                ->actingAs($this->admin, 'sanctum')
                ->postJson('/api/products', []);

            $response
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['name', 'brand_id']);
        });
    });

    describe('PUT /api/products/{id}', function () {
        it('updates product when authenticated as admin', function () {
            $updateData = [
                'name' => 'Updated Product',
                'description' => 'Updated Description',
            ];

            $response = $this
                ->actingAs($this->admin, 'sanctum')
                ->putJson('/api/products/'.$this->product->id, $updateData);

            $response
                ->assertOk()
                ->assertJson(fn (AssertableJson $json) => $json
                    ->where('name', 'Updated Product')
                    ->where('description', 'Updated Description'));

            $this->assertDatabaseHas('products', [
                'id' => $this->product->id,
                'name' => 'Updated Product',
            ]);
        });

        it('requires authentication', function () {
            $response = $this->putJson('/api/products/'.$this->product->id, []);

            $response->assertUnauthorized();
        });

        it('requires admin privileges', function () {
            $response = $this
                ->actingAs($this->user, 'sanctum')
                ->putJson('/api/products/'.$this->product->id, []);

            $response->assertForbidden();
        });
    });

    describe('DELETE /api/products/{id}', function () {
        it('deletes product when authenticated as admin', function () {
            $response = $this
                ->actingAs($this->admin, 'sanctum')
                ->deleteJson('/api/products/'.$this->product->id);

            $response->assertNoContent();

            $this->assertSoftDeleted('products', ['id' => $this->product->id]);
        });

        it('requires authentication', function () {
            $response = $this->deleteJson('/api/products/'.$this->product->id);

            $response->assertUnauthorized();
        });

        it('requires admin privileges', function () {
            $response = $this
                ->actingAs($this->user, 'sanctum')
                ->deleteJson('/api/products/'.$this->product->id);

            $response->assertForbidden();
        });
    });

    describe('Product Stock Management', function () {
        it('can check product availability', function () {
            $response = $this->getJson('/api/products/'.$this->product->id.'/availability');

            $response
                ->assertOk()
                ->assertJson(fn (AssertableJson $json) => $json
                    ->where('available', true)
                    ->where('stock_quantity', 10)
                    ->where('in_stock', true));
        });

        it('shows out of stock when quantity is zero', function () {
            $this->product->variants()->update(['stock_quantity' => 0]);

            $response = $this->getJson('/api/products/'.$this->product->id.'/availability');

            $response
                ->assertOk()
                ->assertJson(fn (AssertableJson $json) => $json
                    ->where('available', false)
                    ->where('stock_quantity', 0)
                    ->where('in_stock', false));
        });
    });

    describe('Product Reviews API', function () {
        it('can get product reviews', function () {
            $response = $this->getJson('/api/products/'.$this->product->id.'/reviews');

            $response
                ->assertOk()
                ->assertJson(fn (AssertableJson $json) => $json
                    ->has('data')
                    ->has('meta'));
        });

        it('can create product review when authenticated', function () {
            $reviewData = [
                'rating' => 5,
                'title' => 'Great product!',
                'comment' => 'I love this product.',
            ];

            $response = $this
                ->actingAs($this->user, 'sanctum')
                ->postJson('/api/products/'.$this->product->id.'/reviews', $reviewData);

            $response
                ->assertCreated()
                ->assertJson(fn (AssertableJson $json) => $json
                    ->where('rating', 5)
                    ->where('title', 'Great product!')
                    ->where('comment', 'I love this product.'));
        });

        it('requires authentication to create review', function () {
            $response = $this->postJson('/api/products/'.$this->product->id.'/reviews', []);

            $response->assertUnauthorized();
        });
    });

    describe('Product Images API', function () {
        it('can get product images', function () {
            $response = $this->getJson('/api/products/'.$this->product->id.'/images');

            $response
                ->assertOk()
                ->assertJson(fn (AssertableJson $json) => $json->has('data'));
        });
    });

    describe('Related Products API', function () {
        it('can get related products', function () {
            // Create related products in same category
            Product::factory()
                ->count(3)
                ->create(['is_visible' => true])
                ->each(fn ($product) => $product->categories()->attach($this->category->id));

            $response = $this->getJson('/api/products/'.$this->product->id.'/related');

            $response
                ->assertOk()
                ->assertJson(fn (AssertableJson $json) => $json
                    ->has('data')
                    ->whereType('data', 'array'));
        });
    });
});
