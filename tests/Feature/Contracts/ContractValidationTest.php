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
use App\Support\Contracts\SimpleJsonSchemaValidator;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        $contractProducts = $response->json('data.products');
        $this->assertNotEmpty($contractProducts);
        $payload = collect($contractProducts)->firstWhere('id', $product->id);
        $this->assertNotNull($payload, 'Product payload not found in response.');
        $this->assertSame([], $this->validator->validate('product', $payload));

        $invalid = $payload;
        unset($invalid['sku']);
        $this->assertNotEmpty($this->validator->validate('product', $invalid));
    }

    public function test_category_tree_response_matches_contract(): void
    {
        $parent = Category::factory()->create(['sort_order' => 1]);
        Category::factory()->withParent($parent)->create(['sort_order' => 2]);

        $response = $this->getJson('/api/categories/tree');
        $response->assertOk();
        $categories = $response->json('data.categories');
        $this->assertNotEmpty($categories);
        $payload = $categories[0];
        $this->assertSame([], $this->validator->validate('category', $payload));

        $invalid = $payload;
        $invalid['order'] = 'first';
        $this->assertNotEmpty($this->validator->validate('category', $invalid));
    }

    public function test_brand_show_response_matches_contract(): void
    {
        $brand = Brand::factory()->create(['is_enabled' => true]);

        $response = $this->getJson('/api/brands/'.$brand->slug);
        $response->assertOk();
        $payload = $response->json('data.brand');
        $this->assertSame([], $this->validator->validate('brand', $payload));

        $invalid = $payload;
        $invalid['url'] = 123;
        $this->assertNotEmpty($this->validator->validate('brand', $invalid));
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
        $response = $this->getJson('/api/orders/'.$order->getKey());
        $response->assertOk();
        $payload = $response->json('data.order');
        $this->assertSame([], $this->validator->validate('order', $payload));

        $invalid = $payload;
        $invalid['items'][0]['quantity'] = 'two';
        $this->assertNotEmpty($this->validator->validate('order', $invalid));
    }

    public function test_user_profile_response_matches_contract(): void
    {
        $user = User::factory()->create([
            'first_name' => 'Jonas',
            'last_name' => 'Meistras',
        ]);

        $this->actingAs($user);
        $response = $this->getJson('/api/user/profile');
        $response->assertOk();
        $payload = $response->json('data');
        $this->assertSame([], $this->validator->validate('user', $payload));

        $invalid = $payload;
        $invalid['email'] = 'not-an-email';
        $this->assertNotEmpty($this->validator->validate('user', $invalid));
    }
}
