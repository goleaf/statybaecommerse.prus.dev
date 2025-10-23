<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Document;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderShipping;
use App\Models\Product;
use App\Models\ProductHistory;
use App\Models\ProductVariant;
use App\Models\User;
use Database\Seeders\AdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * AdminSeederTest
 *
 * Comprehensive test suite for AdminSeeder with all menu items
 * and sample data validation.
 */
final class AdminSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    #[Test]
    public function it_can_run_admin_seeder_successfully(): void
    {
        $this->seed(AdminSeeder::class);

        // Verify admin user was created
        $this->assertDatabaseHas('users', [
            'email'     => 'admin@example.com',
            'is_admin'  => true,
            'is_active' => true,
        ]);
    }

    #[Test]
    public function it_creates_countries_correctly(): void
    {
        $this->seed(AdminSeeder::class);

        $this->assertDatabaseCount('countries', 3);

        $this->assertDatabaseHas('countries', [
            'name'          => 'Lithuania',
            'cca2'          => 'LT',
            'currency_code' => 'EUR',
        ]);

        $this->assertDatabaseHas('countries', [
            'name'          => 'Latvia',
            'cca2'          => 'LV',
            'currency_code' => 'EUR',
        ]);

        $this->assertDatabaseHas('countries', [
            'name'          => 'Estonia',
            'cca2'          => 'EE',
            'currency_code' => 'EUR',
        ]);
    }

    #[Test]
    public function it_creates_zones_correctly(): void
    {
        $this->seed(AdminSeeder::class);

        $this->assertDatabaseCount('zones', 3);

        $this->assertDatabaseHas('zones', [
            'name' => 'Europe',
            'code' => 'EU',
        ]);

        $this->assertDatabaseHas('zones', [
            'name' => 'North America',
            'code' => 'NA',
        ]);

        $this->assertDatabaseHas('zones', [
            'name' => 'Asia',
            'code' => 'AS',
        ]);
    }

    #[Test]
    public function it_creates_cities_correctly(): void
    {
        $this->seed(AdminSeeder::class);

        $this->assertDatabaseCount('cities', 3);

        $this->assertDatabaseHas('cities', [
            'name'       => 'Vilnius',
            'is_active'  => true,
            'is_enabled' => true,
        ]);

        $this->assertDatabaseHas('cities', [
            'name'       => 'Riga',
            'is_active'  => true,
            'is_enabled' => true,
        ]);

        $this->assertDatabaseHas('cities', [
            'name'       => 'Tallinn',
            'is_active'  => true,
            'is_enabled' => true,
        ]);
    }

    #[Test]
    public function it_creates_currencies_correctly(): void
    {
        $this->seed(AdminSeeder::class);

        $this->assertDatabaseCount('currencies', 3);

        $this->assertDatabaseHas('currencies', [
            'name'       => '{"lt":"Euro","en":"Euro"}',
            'code'       => 'EUR',
            'symbol'     => '€',
            'is_default' => true,
        ]);

        $this->assertDatabaseHas('currencies', [
            'name'       => '{"lt":"US Dollar","en":"US Dollar"}',
            'code'       => 'USD',
            'symbol'     => '$',
            'is_default' => false,
        ]);

        $this->assertDatabaseHas('currencies', [
            'name'       => '{"lt":"British Pound","en":"British Pound"}',
            'code'       => 'GBP',
            'symbol'     => '£',
            'is_default' => false,
        ]);
    }

    #[Test]
    public function it_creates_customer_groups_correctly(): void
    {
        $this->seed(AdminSeeder::class);

        $this->assertDatabaseCount('customer_groups', 4);

        $this->assertDatabaseHas('customer_groups', [
            'name'                => '{"lt":"VIP Customers","en":"VIP Customers"}',
            'code'                => 'VIP',
            'discount_percentage' => 15.0,
        ]);

        $this->assertDatabaseHas('customer_groups', [
            'name'                => '{"lt":"Regular Customers","en":"Regular Customers"}',
            'code'                => 'REGULAR',
            'discount_percentage' => 5.0,
        ]);

        $this->assertDatabaseHas('customer_groups', [
            'name'                => '{"lt":"New Customers","en":"New Customers"}',
            'code'                => 'NEW',
            'discount_percentage' => 10.0,
        ]);

        $this->assertDatabaseHas('customer_groups', [
            'name'                => '{"lt":"Wholesale","en":"Wholesale"}',
            'code'                => 'WHOLESALE',
            'discount_percentage' => 20.0,
        ]);
    }

    #[Test]
    public function it_creates_categories_correctly(): void
    {
        $this->seed(AdminSeeder::class);

        $this->assertDatabaseCount('categories', 5);

        $this->assertDatabaseHas('categories', [
            'name' => 'Electronics',
            'slug' => 'electronics',
        ]);

        $this->assertDatabaseHas('categories', [
            'name' => 'Clothing',
            'slug' => 'clothing',
        ]);

        $this->assertDatabaseHas('categories', [
            'name' => 'Home & Garden',
            'slug' => 'home-garden',
        ]);

        $this->assertDatabaseHas('categories', [
            'name' => 'Sports',
            'slug' => 'sports',
        ]);

        $this->assertDatabaseHas('categories', [
            'name' => 'Books',
            'slug' => 'books',
        ]);
    }

    #[Test]
    public function it_creates_products_correctly(): void
    {
        $this->seed(AdminSeeder::class);

        $this->assertDatabaseCount('products', 5);

        $productsBySku = Product::query()
            ->whereIn('sku', ['SP-001', 'WH-002', 'CT-003', 'GT-004', 'YM-005'])
            ->get()
            ->keyBy('sku');

        $smartphone = $productsBySku->get('SP-001');
        $headphones = $productsBySku->get('WH-002');
        $tShirt = $productsBySku->get('CT-003');
        $gardenTools = $productsBySku->get('GT-004');
        $yogaMat = $productsBySku->get('YM-005');

        $this->assertNotNull($smartphone);
        $this->assertSame('Smartphone Pro', $smartphone->name);
        $this->assertTrue($smartphone->is_visible);
        $this->assertSame('published', $smartphone->status);

        $this->assertNotNull($headphones);
        $this->assertSame('Wireless Headphones', $headphones->name);
        $this->assertTrue($headphones->is_visible);
        $this->assertSame('published', $headphones->status);

        $this->assertNotNull($tShirt);
        $this->assertSame('Cotton T-Shirt', $tShirt->name);
        $this->assertTrue($tShirt->is_visible);
        $this->assertSame('published', $tShirt->status);

        $this->assertNotNull($gardenTools);
        $this->assertSame('Garden Tools Set', $gardenTools->name);
        $this->assertTrue($gardenTools->is_visible);
        $this->assertSame('published', $gardenTools->status);

        $this->assertNotNull($yogaMat);
        $this->assertSame('Yoga Mat', $yogaMat->name);
        $this->assertTrue($yogaMat->is_visible);
        $this->assertSame('published', $yogaMat->status);
    }

    #[Test]
    public function it_creates_product_variants_correctly(): void
    {
        $this->seed(AdminSeeder::class);

        // Should have variants for each product (2-3 per product)
        $this->assertGreaterThanOrEqual(10, ProductVariant::count());  // 5 products * 2-3 variants each

        // Check that variants are linked to products
        $variants = ProductVariant::all();
        foreach ($variants as $variant) {
            $this->assertNotNull($variant->product_id);
            $this->assertNotNull($variant->sku);
            $this->assertTrue($variant->is_enabled);
        }
    }

    #[Test]
    public function it_creates_stock_records_correctly(): void
    {
        $this->seed(AdminSeeder::class);

        $this->assertGreaterThan(0, Inventory::count());  // Should have inventory records

        $stocks = Inventory::all();
        foreach ($stocks as $stock) {
            $this->assertNotNull($stock->product_id);
            $this->assertGreaterThan(0, $stock->quantity);
            $this->assertNotNull($stock->location);
        }
    }

    #[Test]
    public function it_creates_addresses_correctly(): void
    {
        $this->seed(AdminSeeder::class);

        $this->assertDatabaseCount('addresses', 2);

        $this->assertDatabaseHas('addresses', [
            'first_name'   => 'Admin',
            'last_name'    => 'User',
            'city'         => 'Vilnius',
            'country_code' => 'LT',
            'is_default'   => true,
            'is_active'    => true,
            'is_shipping'  => true,
        ]);

        $this->assertDatabaseHas('addresses', [
            'first_name'   => 'Admin',
            'last_name'    => 'User',
            'city'         => 'Vilnius',
            'country_code' => 'LT',
            'is_default'   => false,
            'is_active'    => true,
            'is_billing'   => true,
        ]);
    }

    #[Test]
    public function it_creates_orders_correctly(): void
    {
        $this->seed(AdminSeeder::class);

        $this->assertDatabaseCount('orders', 5);

        $this->assertDatabaseHas('orders', [
            'number'   => 'ORD-000001',
            'currency' => 'EUR',
        ]);

        $this->assertDatabaseHas('orders', [
            'number'   => 'ORD-000002',
            'currency' => 'EUR',
        ]);

        $this->assertDatabaseHas('orders', [
            'number'   => 'ORD-000003',
            'currency' => 'EUR',
        ]);

        $this->assertDatabaseHas('orders', [
            'number'   => 'ORD-000004',
            'currency' => 'EUR',
        ]);

        $this->assertDatabaseHas('orders', [
            'number'   => 'ORD-000005',
            'currency' => 'EUR',
        ]);
    }

    #[Test]
    public function it_creates_order_items_correctly(): void
    {
        $this->seed(AdminSeeder::class);

        $this->assertGreaterThanOrEqual(5, OrderItem::count());  // 5 orders * 1-3 items each

        $orderItems = OrderItem::all();
        foreach ($orderItems as $item) {
            $this->assertNotNull($item->order_id);
            $this->assertNotNull($item->product_id);
            $this->assertNotNull($item->product_variant_id);
            $this->assertNotNull($item->name);
            $this->assertNotNull($item->sku);
            $this->assertGreaterThan(0, $item->quantity);
            $this->assertGreaterThan(0, $item->unit_price);
            $this->assertGreaterThan(0, $item->total);
        }
    }

    #[Test]
    public function it_creates_order_shipping_correctly(): void
    {
        $this->seed(AdminSeeder::class);

        $this->assertDatabaseCount('order_shippings', 5);

        $shipping = OrderShipping::all();
        foreach ($shipping as $ship) {
            $this->assertNotNull($ship->order_id);
            $this->assertNotNull($ship->carrier_name);
            $this->assertNotNull($ship->service);
            $this->assertNotNull($ship->tracking_number);
            $this->assertGreaterThan(0, $ship->cost);
        }
    }

    #[Test]
    public function it_creates_documents_correctly(): void
    {
        $this->markTestSkipped('Documents creation is temporarily disabled in the seeder');

        $this->seed(AdminSeeder::class);

        $this->assertDatabaseCount('documents', 5);

        $documents = Document::all();
        foreach ($documents as $document) {
            $this->assertNotNull($document->order_id);
            $this->assertEquals('invoice', $document->type);
            $this->assertNotNull($document->title);
            $this->assertNotNull($document->content);
        }
    }

    #[Test]
    public function it_creates_discount_codes_correctly(): void
    {
        $this->markTestSkipped('Discount codes creation is temporarily disabled in the seeder');

        $this->seed(AdminSeeder::class);

        $this->assertDatabaseCount('discount_codes', 3);

        $this->assertDatabaseHas('discount_codes', [
            'code'                => 'WELCOME10',
            'discount_percentage' => 10.0,
            'is_active'           => true,
        ]);

        $this->assertDatabaseHas('discount_codes', [
            'code'                => 'SAVE20',
            'discount_percentage' => 20.0,
            'is_active'           => true,
        ]);

        $this->assertDatabaseHas('discount_codes', [
            'code'                => 'FREESHIP',
            'discount_percentage' => 0.0,
            'is_active'           => true,
        ]);
    }

    #[Test]
    public function it_creates_sliders_correctly(): void
    {
        $this->seed(AdminSeeder::class);

        $this->assertDatabaseCount('sliders', 3);

        $this->assertDatabaseHas('sliders', [
            'title'      => 'Welcome to Our Store',
            'is_active'  => true,
            'sort_order' => 1,
        ]);

        $this->assertDatabaseHas('sliders', [
            'title'      => 'New Arrivals',
            'is_active'  => true,
            'sort_order' => 2,
        ]);

        $this->assertDatabaseHas('sliders', [
            'title'      => 'Special Offers',
            'is_active'  => true,
            'sort_order' => 3,
        ]);
    }

    #[Test]
    public function it_creates_recommendation_blocks_correctly(): void
    {
        $this->seed(AdminSeeder::class);

        $this->assertDatabaseCount('recommendation_blocks', 3);

        $this->assertDatabaseHas('recommendation_blocks', [
            'title'     => 'Featured Products',
            'name'      => 'featured',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('recommendation_blocks', [
            'title'     => 'Best Sellers',
            'name'      => 'bestsellers',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('recommendation_blocks', [
            'title'     => 'New Arrivals',
            'name'      => 'new_arrivals',
            'is_active' => true,
        ]);
    }

    #[Test]
    public function it_creates_seo_data_correctly(): void
    {
        $this->seed(AdminSeeder::class);

        $this->assertDatabaseCount('seo_data', 2);

        $this->assertDatabaseHas('seo_data', [
            'seoable_type' => 'App\Models\Page',
            'seoable_id'   => 1,
            'locale'       => 'en',
            'title'        => 'Home - Your Store',
        ]);

        $this->assertDatabaseHas('seo_data', [
            'seoable_type' => 'App\Models\Page',
            'seoable_id'   => 2,
            'locale'       => 'en',
            'title'        => 'Products - Your Store',
        ]);
    }

    #[Test]
    public function it_creates_subscribers_correctly(): void
    {
        $this->seed(AdminSeeder::class);

        $this->assertDatabaseCount('subscribers', 5);

        $this->assertDatabaseHas('subscribers', [
            'email'  => 'subscriber1@example.com',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('subscribers', [
            'email'  => 'subscriber2@example.com',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('subscribers', [
            'email'  => 'subscriber3@example.com',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('subscribers', [
            'email'  => 'subscriber4@example.com',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('subscribers', [
            'email'  => 'subscriber5@example.com',
            'status' => 'active',
        ]);
    }

    #[Test]
    public function it_creates_referral_rewards_correctly(): void
    {
        $this->markTestSkipped('Referral rewards creation is temporarily disabled in the seeder');

        $this->seed(AdminSeeder::class);

        $this->assertDatabaseCount('referral_rewards', 1);

        $this->assertDatabaseHas('referral_rewards', [
            'reward_type'  => 'discount',
            'reward_value' => 15.0,
            'is_active'    => true,
        ]);
    }

    #[Test]
    public function it_creates_product_histories_correctly(): void
    {
        $this->seed(AdminSeeder::class);

        $this->assertDatabaseCount('product_histories', 5);

        $history = ProductHistory::all();
        foreach ($history as $record) {
            $this->assertNotNull($record->product_id);
            $this->assertEquals('created', $record->action);
            $this->assertNull($record->old_value);
            $this->assertNotNull($record->new_value);
            $this->assertEquals(1, $record->user_id);
        }
    }

    #[Test]
    public function it_creates_locations_correctly(): void
    {
        $this->seed(AdminSeeder::class);

        $this->assertDatabaseCount('locations', 2);

        $this->assertDatabaseHas('locations', [
            'name'       => 'Main Warehouse',
            'type'       => 'warehouse',
            'is_enabled' => true,
        ]);

        $this->assertDatabaseHas('locations', [
            'name'       => 'Store Location',
            'type'       => 'store',
            'is_enabled' => true,
        ]);
    }

    #[Test]
    public function it_creates_product_category_relationships(): void
    {
        $this->seed(AdminSeeder::class);

        // Check that products are linked to categories
        $products = Product::with('categories')->get();

        foreach ($products as $product) {
            $this->assertGreaterThan(0, $product->categories->count());
        }
    }

    #[Test]
    public function it_creates_user_address_relationships(): void
    {
        $this->seed(AdminSeeder::class);

        $admin = User::where('email', 'admin@example.com')->first();
        $this->assertNotNull($admin);

        $addresses = $admin->addresses;
        $this->assertCount(2, $addresses);

        $shippingAddress = $addresses->where('is_shipping', true)->first();
        $this->assertNotNull($shippingAddress);

        $billingAddress = $addresses->where('is_billing', true)->first();
        $this->assertNotNull($billingAddress);
    }

    #[Test]
    public function it_creates_order_user_relationships(): void
    {
        $this->seed(AdminSeeder::class);

        $admin = User::where('email', 'admin@example.com')->first();
        $this->assertNotNull($admin);

        $orders = $admin->orders;
        $this->assertGreaterThanOrEqual(4, $orders->count());

        foreach ($orders as $order) {
            $this->assertEquals($admin->id, $order->user_id);
        }
    }

    #[Test]
    public function it_creates_order_item_relationships(): void
    {
        $this->seed(AdminSeeder::class);

        $orders = Order::with('items')->get();

        foreach ($orders as $order) {
            $this->assertGreaterThan(0, $order->items->count());

            foreach ($order->items as $item) {
                $this->assertEquals($order->id, $item->order_id);
                $this->assertNotNull($item->product);
                $this->assertNotNull($item->productVariant);
            }
        }
    }

    #[Test]
    public function it_handles_duplicate_seeding_gracefully(): void
    {
        // Run seeder twice to test idempotency
        $this->seed(AdminSeeder::class);
        $this->seed(AdminSeeder::class);

        // Should still have the same counts (no duplicates)
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('countries', 3);
        $this->assertDatabaseCount('zones', 3);
        $this->assertDatabaseCount('cities', 3);
        $this->assertDatabaseCount('currencies', 3);
        $this->assertDatabaseCount('customer_groups', 4);
        $this->assertDatabaseCount('categories', 5);
        $this->assertDatabaseCount('products', 5);
        $this->assertDatabaseCount('orders', 5);
        $this->assertDatabaseCount('sliders', 3);
        $this->assertDatabaseCount('subscribers', 5);
    }

    #[Test]
    public function it_creates_all_required_data_for_admin_panel(): void
    {
        $this->seed(AdminSeeder::class);

        // Verify all essential data exists for admin panel functionality
        $this->assertDatabaseHas('users', ['email' => 'admin@example.com']);
        $this->assertDatabaseCount('countries', 3);
        $this->assertDatabaseCount('zones', 3);
        $this->assertDatabaseCount('cities', 3);
        $this->assertDatabaseCount('currencies', 3);
        $this->assertDatabaseCount('customer_groups', 4);
        $this->assertDatabaseCount('categories', 5);
        $this->assertDatabaseCount('products', 5);
        $this->assertGreaterThanOrEqual(10, ProductVariant::count());
        $this->assertDatabaseCount('inventories', 10);
        $this->assertDatabaseCount('addresses', 2);
        $this->assertDatabaseCount('orders', 5);
        $this->assertGreaterThanOrEqual(5, OrderItem::count());
        $this->assertDatabaseCount('order_shippings', 5);
        // Documents and discount codes are temporarily disabled in the seeder
        $this->assertDatabaseCount('sliders', 3);
        $this->assertDatabaseCount('recommendation_blocks', 3);
        $this->assertDatabaseCount('seo_data', 2);
        $this->assertDatabaseCount('subscribers', 5);
        // Referral rewards are temporarily disabled in the seeder
        $this->assertDatabaseCount('product_histories', 5);
        $this->assertDatabaseCount('locations', 2);
    }
}
