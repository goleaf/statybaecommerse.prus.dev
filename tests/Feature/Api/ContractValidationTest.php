<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Support\Contracts\Entities\BrandContract;
use App\Support\Contracts\Entities\CategoryContract;
use App\Support\Contracts\Entities\OrderContract;
use App\Support\Contracts\Entities\ProductContract;
use App\Support\Contracts\Entities\UserContract;
use App\Support\Contracts\SimpleJsonSchemaValidator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class ContractValidationTest extends TestCase
{
    use RefreshDatabase;

    private SimpleJsonSchemaValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = app(SimpleJsonSchemaValidator::class);
        config(['app.currency' => 'EUR']);
    }

    public function test_product_show_payload_matches_contract(): void
    {
        $brand = Brand::factory()->create(['is_visible' => true]);
        $category = Category::factory()->create(['is_visible' => true]);
        $product = Product::factory()
            ->for($brand)
            ->create([
                'is_visible' => true,
                'published_at' => now()->subDay(),
                'price' => 199,
                'sale_price' => 149,
                'manage_stock' => true,
                'stock_quantity' => 25,
            ]);
        $product->categories()->attach($category->getKey());

        $response = $this->getJson(route('api.products.show', ['product' => $product]));
        $response->assertOk();

        $payload = $response->json();
        $this->assertSame('product-resource', $payload['contract']);
        $this->assertSame('v2', $payload['version']);
        $this->assertSame($product->slug, $payload['data']['slug']);
        $this->assertSame($brand->name, $payload['data']['brand']['name']);
        $this->assertSame($category->slug, $payload['data']['categories'][0]['slug']);
    }

    public function test_product_search_payload_matches_contract(): void
    {
        $brand = Brand::factory()->create(['is_visible' => true]);
        $category = Category::factory()->create(['is_visible' => true]);
        Product::factory()->count(2)->for($brand)->create([
            'is_visible' => true,
            'published_at' => now()->subDay(),
            'price' => 59,
            'manage_stock' => true,
            'stock_quantity' => 8,
        ])->each(fn (Product $product) => $product->categories()->attach($category->getKey()));

        $response = $this->getJson(route('api.products.search', ['q' => '']));
        $response->assertOk();

        $payload = $response->json();
        $this->assertSame([], $this->validator->validate($payload, ProductContract::schemaPath()));
    }

    public function test_category_tree_payload_matches_contract(): void
    {
        $root = Category::factory()->create(['is_visible' => true]);
        Category::factory()->create(['is_visible' => true, 'parent_id' => $root->getKey()]);

        $response = $this->getJson(route('api.categories.tree'));
        $response->assertOk();

        $payload = $response->json();
        $this->assertSame([], $this->validator->validate($payload, CategoryContract::schemaPath()));
    }

    public function test_brand_index_payload_matches_contract(): void
    {
        Brand::factory()->count(2)->create(['is_visible' => true]);

        $response = $this->getJson(route('api.brands.index'));
        $response->assertOk();

        $payload = $response->json();
        $this->assertSame([], $this->validator->validate($payload, BrandContract::schemaPath()));
    }

    public function test_order_show_payload_matches_contract(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->for($user)->create([
            'status' => 'processing',
            'payment_status' => 'paid',
        ]);
        OrderItem::factory()->count(2)->forOrder($order)->create();

        $this->actingAs($user);
        $response = $this->getJson(route('api.orders.show', ['order' => $order->number]));
        $response->assertOk();

        $payload = $response->json();
        $this->assertSame([], $this->validator->validate($payload, OrderContract::schemaPath()));
    }

    public function test_authenticated_user_payload_matches_contract(): void
    {
        $user = User::factory()->create([
            'preferred_locale' => 'lt',
            'timezone' => 'Europe/Vilnius',
        ]);
        Sanctum::actingAs($user, ['profile.read']);

        $response = $this->getJson(route('api.v1.user.show'));
        $response->assertOk();

        $payload = $response->json();
        $this->assertSame([], $this->validator->validate($payload, UserContract::schemaPath()));
    }
}
