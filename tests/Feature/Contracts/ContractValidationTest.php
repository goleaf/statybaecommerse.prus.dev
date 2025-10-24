<?php

declare(strict_types=1);

namespace Tests\Feature\Contracts;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderShipping;
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
use function collect;

final class ContractValidationTest extends TestCase
{
    use RefreshDatabase;

    private SimpleJsonSchemaValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = $this->app->make(SimpleJsonSchemaValidator::class);
    }

    public function test_product_search_response_matches_contract(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'is_visible' => true,
            'status' => 'active',
            'metadata' => ['power' => '1200W'],
        ]);
        $product->categories()->attach($category);

        $response = $this->getJson('/api/products/search?q='.urlencode(substr($product->name, 0, 4)));

        $response->assertOk();
        $payload = $response->json();
        $this->assertSame([], $this->validator->validate($payload, ProductContract::schemaPath()));

        $items = $payload['data']['items'] ?? [];
        $this->assertNotEmpty($items);
        $productPayload = collect($items)->firstWhere('id', $product->id);
        $this->assertNotNull($productPayload, 'Product payload not found in response.');

        $invalid = $payload;
        unset($invalid['data']['items'][0]['sku']);
        $this->assertNotEmpty($this->validator->validate($invalid, ProductContract::schemaPath()));
    }

    public function test_category_tree_response_matches_contract(): void
    {
        $parent = Category::factory()->create(['sort_order' => 1]);
        Category::factory()->withParent($parent)->create(['sort_order' => 2]);

        $response = $this->getJson('/api/categories/tree');
        $response->assertOk();
        $payload = $response->json();
        $this->assertSame([], $this->validator->validate($payload, CategoryContract::schemaPath()));

        $categories = $payload['data']['items'] ?? [];
        $this->assertNotEmpty($categories);

        $invalid = $payload;
        $invalid['data']['items'][0]['order'] = 'first';
        $this->assertNotEmpty($this->validator->validate($invalid, CategoryContract::schemaPath()));
    }

    public function test_brand_show_response_matches_contract(): void
    {
        $brand = Brand::factory()->create(['is_enabled' => true]);

        $response = $this->getJson('/api/brands/'.$brand->slug);
        $response->assertOk();
        $payload = $response->json();
        $this->assertSame([], $this->validator->validate($payload, BrandContract::schemaPath()));

        $invalid = $payload;
        $invalid['data']['item']['website'] = 123;
        $this->assertNotEmpty($this->validator->validate($invalid, BrandContract::schemaPath()));
    }

    public function test_order_show_response_matches_contract(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'is_visible' => true,
            'status' => 'active',
        ]);
        $order = Order::factory()->for($user)->create([
            'status' => 'completed',
            'payment_status' => 'paid',
        ]);
        OrderItem::factory()->for($order)->forProduct($product)->create([
            'quantity' => 2,
        ]);
        OrderShipping::factory()->create([
            'order_id' => $order->id,
            'carrier_name' => 'DPD',
            'service' => 'Classic',
            'status' => 'in_transit',
            'tracking_number' => 'LT123456789',
            'tracking_url' => 'https://tracking.example.com/LT123456789',
        ]);

        $this->actingAs($user);
        $response = $this->getJson('/api/orders/'.$order->number);
        $response->assertOk();
        $payload = $response->json();
        $this->assertSame([], $this->validator->validate($payload, OrderContract::schemaPath()));

        $invalid = $payload;
        $invalid['data']['order']['items'][0]['quantity'] = 'two';
        $this->assertNotEmpty($this->validator->validate($invalid, OrderContract::schemaPath()));
    }

    public function test_user_profile_response_matches_contract(): void
    {
        $user = User::factory()->create([
            'first_name' => 'Jonas',
            'last_name' => 'Meistras',
        ]);

        Sanctum::actingAs($user, ['profile.read']);
        $response = $this->getJson('/api/v1/user');
        $response->assertOk();
        $payload = $response->json();
        $this->assertSame([], $this->validator->validate($payload, UserContract::schemaPath()));

        $invalid = $payload;
        $invalid['data']['item']['contact']['email'] = 'not-an-email';
        $this->assertNotEmpty($this->validator->validate($invalid, UserContract::schemaPath()));
    }
}
