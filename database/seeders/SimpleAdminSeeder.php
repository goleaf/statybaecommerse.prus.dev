<?php declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Category;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Slider;
use App\Models\User;
use App\Models\Zone;
use App\Enums\AddressType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * SimpleAdminSeeder
 * 
 * Simple seeder for admin@example.com user with essential data
 * for testing the admin panel functionality.
 */
final class SimpleAdminSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🌱 Starting Simple Admin Seeder...');

        // Create admin user
        $admin = $this->createAdminUser();
        
        // Create essential data
        $this->createEssentialData($admin);
        
        $this->command->info('✅ Simple Admin Seeder completed successfully!');
        $this->command->info("👤 Admin user: admin@example.com");
        $this->command->info("🔑 Password: password");
    }

    private function createAdminUser(): User
    {
        $this->command->info('👤 Creating admin user...');
        
        $admin = User::where('email', 'admin@example.com')->first();
        
        if (!$admin) {
            $admin = User::create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'is_admin' => true,
                'is_active' => true,
            ]);
        }
        
        return $admin;
    }

    private function createEssentialData(User $admin): void
    {
        // Create countries
        $this->command->info('🌍 Creating countries...');
        $countries = $this->createCountries();
        
        // Create zones
        $this->command->info('🗺️ Creating zones...');
        $zones = $this->createZones();
        
        // Create currencies
        $this->command->info('💰 Creating currencies...');
        $currencies = $this->createCurrencies();
        
        // Create categories
        $this->command->info('📂 Creating categories...');
        $categories = $this->createCategories();
        
        // Create products
        $this->command->info('📦 Creating products...');
        $products = $this->createProducts($categories);
        
        // Create product variants
        $this->command->info('🔧 Creating product variants...');
        $variants = $this->createProductVariants($products);
        
        // Create addresses
        $this->command->info('🏠 Creating addresses...');
        $addresses = $this->createAddresses($admin, $countries, $zones);
        
        // Create orders
        $this->command->info('🛒 Creating orders...');
        $orders = $this->createOrders($admin, $addresses);
        
        // Create order items
        $this->command->info('📦 Creating order items...');
        $this->createOrderItems($orders, $variants);
        
        // Create sliders
        $this->command->info('🎠 Creating sliders...');
        $this->createSliders();
    }

    private function createCountries(): array
    {
        $countries = [
            [
                'name' => 'Lithuania', 
                'code' => 'LT', 
                'currency_code' => 'EUR',
                'cca2' => 'LT',
                'cca3' => 'LTU',
                'ccn3' => '440',
                'iso_code' => 'LT',
                'currency_symbol' => '€',
                'phone_code' => '370',
                'phone_calling_code' => '370',
                'region' => 'Europe',
                'subregion' => 'Northern Europe',
                'latitude' => 55.1694,
                'longitude' => 23.8813,
                'is_active' => true,
                'is_eu_member' => true,
                'requires_vat' => true,
                'vat_rate' => 21.0,
                'timezone' => 'Europe/Vilnius',
                'is_enabled' => true,
                'sort_order' => 1,
            ],
        ];

        $createdCountries = [];
        foreach ($countries as $country) {
            $createdCountries[] = Country::firstOrCreate(
                ['cca2' => $country['cca2']],
                $country
            );
        }
        return $createdCountries;
    }

    private function createZones(): array
    {
        $zones = [
            ['name' => 'Europe', 'code' => 'EU'],
            ['name' => 'North America', 'code' => 'NA'],
        ];

        $createdZones = [];
        foreach ($zones as $zone) {
            $createdZones[] = Zone::firstOrCreate(
                ['code' => $zone['code']],
                $zone
            );
        }
        return $createdZones;
    }

    private function createCurrencies(): array
    {
        $currencies = [
            ['name' => 'Euro', 'code' => 'EUR', 'symbol' => '€', 'exchange_rate' => 1.0, 'is_default' => true, 'is_enabled' => true, 'decimal_places' => 2],
            ['name' => 'US Dollar', 'code' => 'USD', 'symbol' => '$', 'exchange_rate' => 0.85, 'is_default' => false, 'is_enabled' => true, 'decimal_places' => 2],
        ];

        $createdCurrencies = [];
        foreach ($currencies as $currency) {
            $createdCurrencies[] = Currency::firstOrCreate(
                ['code' => $currency['code']],
                $currency
            );
        }
        return $createdCurrencies;
    }

    private function createCategories(): array
    {
        $categories = [
            ['name' => 'Electronics', 'slug' => 'electronics', 'description' => 'Electronic devices and gadgets'],
            ['name' => 'Clothing', 'slug' => 'clothing', 'description' => 'Fashion and apparel'],
            ['name' => 'Home & Garden', 'slug' => 'home-garden', 'description' => 'Home improvement and gardening'],
        ];

        $createdCategories = [];
        foreach ($categories as $category) {
            $createdCategories[] = Category::firstOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
        return $createdCategories;
    }

    private function createProducts(array $categories): array
    {
        $products = [
            [
                'name' => 'Smartphone Pro',
                'slug' => 'smartphone-pro',
                'description' => 'Latest generation smartphone with advanced features',
                'price' => 899.99,
                'is_visible' => true,
                'sku' => 'SP-001',
                'status' => 'published',
                'published_at' => now(),
            ],
            [
                'name' => 'Cotton T-Shirt',
                'slug' => 'cotton-t-shirt',
                'description' => 'Comfortable cotton t-shirt in various colors',
                'price' => 29.99,
                'is_visible' => true,
                'sku' => 'CT-002',
                'status' => 'published',
                'published_at' => now(),
            ],
        ];

        $createdProducts = [];
        foreach ($products as $index => $product) {
            $createdProduct = Product::firstOrCreate(
                ['sku' => $product['sku']],
                $product
            );
            
            // Attach category to product if not already attached
            if (isset($categories[$index]) && !$createdProduct->categories()->where('category_id', $categories[$index]->id)->exists()) {
                $createdProduct->categories()->attach($categories[$index]->id);
            }
            
            $createdProducts[] = $createdProduct;
        }
        return $createdProducts;
    }

    private function createProductVariants(array $products): array
    {
        $variants = [];
        
        foreach ($products as $product) {
            $variants[] = ProductVariant::firstOrCreate(
                ['product_id' => $product->id, 'sku' => $product->sku . '-V1'],
                [
                    'product_id' => $product->id,
                    'name' => $product->name . ' - Variant 1',
                    'sku' => $product->sku . '-V1',
                    'price' => $product->price,
                    'is_enabled' => true,
                ]
            );
        }

        return $variants;
    }

    private function createAddresses(User $admin, array $countries, array $zones): array
    {
        $addresses = [
            [
                'user_id' => $admin->id,
                'type' => AddressType::SHIPPING,
                'first_name' => 'Admin',
                'last_name' => 'User',
                'address_line_1' => '123 Main Street',
                'city' => 'Vilnius',
                'postal_code' => '01234',
                'country_code' => 'LT',
                'phone' => '+37012345678',
                'email' => 'admin@example.com',
                'is_default' => true,
                'is_active' => true,
                'is_shipping' => true,
            ],
        ];

        $createdAddresses = [];
        foreach ($addresses as $address) {
            $createdAddresses[] = Address::create($address);
        }
        return $createdAddresses;
    }

    private function createOrders(User $admin, array $addresses): array
    {
        $orders = [];
        
        for ($i = 0; $i < 3; $i++) {
            $orders[] = Order::create([
                'user_id' => $admin->id,
                'order_number' => 'ORD-' . str_pad($i + 1, 6, '0', STR_PAD_LEFT),
                'status' => ['pending', 'processing', 'shipped'][$i],
                'total_amount' => rand(100, 1000),
                'shipping_address_id' => $addresses[0]->id,
                'billing_address_id' => $addresses[0]->id,
                'notes' => 'Sample order ' . ($i + 1),
            ]);
        }

        return $orders;
    }

    private function createOrderItems(array $orders, array $variants): void
    {
        foreach ($orders as $order) {
            $selectedVariant = $variants[array_rand($variants)];
            
            OrderItem::create([
                'order_id' => $order->id,
                'product_variant_id' => $selectedVariant->id,
                'quantity' => rand(1, 3),
                'unit_price' => $selectedVariant->price,
                'total' => $selectedVariant->price * rand(1, 3),
                'status' => 'pending',
            ]);
        }
    }

    private function createSliders(): void
    {
        $sliders = [
            [
                'title' => 'Welcome to Our Store',
                'description' => 'Discover amazing products at great prices',
                'button_text' => 'Shop Now',
                'button_url' => '/products',
                'background_color' => '#3B82F6',
                'text_color' => '#FFFFFF',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'title' => 'New Arrivals',
                'description' => 'Check out our latest products',
                'button_text' => 'View Collection',
                'button_url' => '/new-arrivals',
                'background_color' => '#10B981',
                'text_color' => '#FFFFFF',
                'is_active' => true,
                'sort_order' => 2,
            ],
        ];

        foreach ($sliders as $slider) {
            Slider::firstOrCreate(
                ['title' => $slider['title']],
                $slider
            );
        }
    }
}
