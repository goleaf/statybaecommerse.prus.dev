<?php declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\AddressType;
use App\Enums\NavigationGroup;
use App\Models\Address;
use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\Currency;
use App\Models\CustomerGroup;
use App\Models\DiscountCode;
use App\Models\Document;
use App\Models\Location;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderShipping;
use App\Models\Product;
use App\Models\ProductHistory;
use App\Models\ProductVariant;
use App\Models\RecommendationBlock;
use App\Models\ReferralReward;
use App\Models\SeoData;
use App\Models\Slider;
use App\Models\Stock;
use App\Models\Subscriber;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * FullAdminSeeder
 * 
 * Comprehensive seeder for admin@example.com user with all menu items
 * and sample data for testing and demonstration purposes.
 */
final class FullAdminSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🌱 Starting Full Admin Seeder...');

        // Create admin user
        $admin = $this->createAdminUser();
        
        // Create countries and zones
        $countries = $this->createCountries();
        $zones = $this->createZones();
        $cities = $this->createCities($countries);
        
        // Create currencies
        $currencies = $this->createCurrencies();
        
        // Create customer groups
        $customerGroups = $this->createCustomerGroups();
        
        // Create categories
        $categories = $this->createCategories();
        
        // Create products and variants
        $products = $this->createProducts($categories);
        $variants = $this->createProductVariants($products);
        
        // Create stock records
        $this->createStockRecords($variants);
        
        // Create addresses
        $addresses = $this->createAddresses($admin, $countries, $zones, $cities);
        
        // Create orders and order items
        $orders = $this->createOrders($admin, $addresses);
        $this->createOrderItems($orders, $variants);
        
        // Create order shipping
        $this->createOrderShipping($orders);
        
        // Create documents
        $this->createDocuments($orders);
        
        // Create discount codes
        $this->createDiscountCodes();
        
        // Create sliders
        $this->createSliders();
        
        // Create recommendation blocks
        $this->createRecommendationBlocks();
        
        // Create SEO data
        $this->createSeoData();
        
        // Create subscribers
        $this->createSubscribers();
        
        // Create referral rewards
        $this->createReferralRewards($admin);
        
        // Create product history
        $this->createProductHistory($products);
        
        // Create reports
        $this->createReports();
        
        // Create locations
        $this->createLocations($countries, $zones, $cities);
        
        $this->command->info('✅ Full Admin Seeder completed successfully!');
        $this->command->info("👤 Admin user: admin@example.com");
        $this->command->info("🔑 Password: password");
    }

    private function createAdminUser(): User
    {
        $this->command->info('👤 Creating admin user...');
        
        return User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'is_admin' => true,
                'is_active' => true,
            ]
        );
    }

    private function createCountries(): array
    {
        $this->command->info('🌍 Creating countries...');
        
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
            [
                'name' => 'Latvia', 
                'code' => 'LV', 
                'currency_code' => 'EUR',
                'cca2' => 'LV',
                'cca3' => 'LVA',
                'ccn3' => '428',
                'iso_code' => 'LV',
                'currency_symbol' => '€',
                'phone_code' => '371',
                'phone_calling_code' => '371',
                'region' => 'Europe',
                'subregion' => 'Northern Europe',
                'latitude' => 56.8796,
                'longitude' => 24.6032,
                'is_active' => true,
                'is_eu_member' => true,
                'requires_vat' => true,
                'vat_rate' => 21.0,
                'timezone' => 'Europe/Riga',
                'is_enabled' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Estonia', 
                'code' => 'EE', 
                'currency_code' => 'EUR',
                'cca2' => 'EE',
                'cca3' => 'EST',
                'ccn3' => '233',
                'iso_code' => 'EE',
                'currency_symbol' => '€',
                'phone_code' => '372',
                'phone_calling_code' => '372',
                'region' => 'Europe',
                'subregion' => 'Northern Europe',
                'latitude' => 58.5953,
                'longitude' => 25.0136,
                'is_active' => true,
                'is_eu_member' => true,
                'requires_vat' => true,
                'vat_rate' => 20.0,
                'timezone' => 'Europe/Tallinn',
                'is_enabled' => true,
                'sort_order' => 3,
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
        $this->command->info('🗺️ Creating zones...');
        
        $zones = [
            ['name' => 'Europe', 'code' => 'EU'],
            ['name' => 'North America', 'code' => 'NA'],
            ['name' => 'Asia', 'code' => 'AS'],
            ['name' => 'Africa', 'code' => 'AF'],
            ['name' => 'Oceania', 'code' => 'OC'],
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

    private function createCities(array $countries): array
    {
        $this->command->info('🏙️ Creating cities...');
        
        $cities = [
            ['name' => 'Vilnius', 'country_id' => $countries[0]->id, 'is_active' => true, 'is_enabled' => true],
            ['name' => 'Riga', 'country_id' => $countries[1]->id, 'is_active' => true, 'is_enabled' => true],
            ['name' => 'Tallinn', 'country_id' => $countries[2]->id, 'is_active' => true, 'is_enabled' => true],
        ];

        $createdCities = [];
        foreach ($cities as $city) {
            $createdCities[] = City::firstOrCreate(
                ['name' => $city['name'], 'country_id' => $city['country_id']],
                $city
            );
        }
        return $createdCities;
    }

    private function createCurrencies(): array
    {
        $this->command->info('💰 Creating currencies...');
        
        $currencies = [
            ['name' => 'Euro', 'code' => 'EUR', 'symbol' => '€', 'rate' => 1.0, 'is_default' => true],
            ['name' => 'US Dollar', 'code' => 'USD', 'symbol' => '$', 'rate' => 0.85],
            ['name' => 'British Pound', 'code' => 'GBP', 'symbol' => '£', 'rate' => 1.15],
            ['name' => 'Polish Zloty', 'code' => 'PLN', 'symbol' => 'zł', 'rate' => 0.22],
        ];

        return collect($currencies)->map(function ($currency) {
            return Currency::firstOrCreate(
                ['code' => $currency['code']],
                $currency
            );
        })->toArray();
    }

    private function createCustomerGroups(): array
    {
        $this->command->info('👥 Creating customer groups...');
        
        $groups = [
            ['name' => 'VIP Customers', 'code' => 'VIP', 'description' => 'High-value customers with special privileges', 'discount_percentage' => 15.0],
            ['name' => 'Regular Customers', 'code' => 'REGULAR', 'description' => 'Standard customers', 'discount_percentage' => 5.0],
            ['name' => 'New Customers', 'code' => 'NEW', 'description' => 'First-time customers', 'discount_percentage' => 10.0],
            ['name' => 'Wholesale', 'code' => 'WHOLESALE', 'description' => 'Bulk purchase customers', 'discount_percentage' => 20.0],
        ];

        $createdGroups = [];
        foreach ($groups as $group) {
            $createdGroups[] = CustomerGroup::firstOrCreate(
                ['code' => $group['code']],
                $group
            );
        }
        return $createdGroups;
    }

    private function createCategories(): array
    {
        $this->command->info('📂 Creating categories...');
        
        $categories = [
            ['name' => 'Electronics', 'slug' => 'electronics', 'description' => 'Electronic devices and gadgets'],
            ['name' => 'Clothing', 'slug' => 'clothing', 'description' => 'Fashion and apparel'],
            ['name' => 'Home & Garden', 'slug' => 'home-garden', 'description' => 'Home improvement and gardening'],
            ['name' => 'Sports', 'slug' => 'sports', 'description' => 'Sports and fitness equipment'],
            ['name' => 'Books', 'slug' => 'books', 'description' => 'Books and literature'],
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
        $this->command->info('📦 Creating products...');
        
        $products = [
            [
                'name' => 'Smartphone Pro',
                'slug' => 'smartphone-pro',
                'description' => 'Latest generation smartphone with advanced features',
                'price' => 899.99,
                'category_id' => $categories[0]->id,
                'is_active' => true,
                'sku' => 'SP-001',
            ],
            [
                'name' => 'Wireless Headphones',
                'slug' => 'wireless-headphones',
                'description' => 'High-quality wireless headphones with noise cancellation',
                'price' => 199.99,
                'category_id' => $categories[0]->id,
                'is_active' => true,
                'sku' => 'WH-002',
            ],
            [
                'name' => 'Cotton T-Shirt',
                'slug' => 'cotton-t-shirt',
                'description' => 'Comfortable cotton t-shirt in various colors',
                'price' => 29.99,
                'category_id' => $categories[1]->id,
                'is_active' => true,
                'sku' => 'CT-003',
            ],
            [
                'name' => 'Garden Tools Set',
                'slug' => 'garden-tools-set',
                'description' => 'Complete set of professional garden tools',
                'price' => 149.99,
                'category_id' => $categories[2]->id,
                'is_active' => true,
                'sku' => 'GT-004',
            ],
            [
                'name' => 'Yoga Mat',
                'slug' => 'yoga-mat',
                'description' => 'Premium yoga mat for all fitness activities',
                'price' => 49.99,
                'category_id' => $categories[3]->id,
                'is_active' => true,
                'sku' => 'YM-005',
            ],
        ];

        return collect($products)->map(function ($product) {
            return Product::create($product);
        })->toArray();
    }

    private function createProductVariants(array $products): array
    {
        $this->command->info('🔧 Creating product variants...');
        
        $variants = [];
        
        foreach ($products as $product) {
            // Create 2-3 variants per product
            $variantCount = rand(2, 3);
            
            for ($i = 0; $i < $variantCount; $i++) {
                $variants[] = ProductVariant::create([
                    'product_id' => $product->id,
                    'name' => $product->name . ' - Variant ' . ($i + 1),
                    'sku' => $product->sku . '-' . ($i + 1),
                    'price' => $product->price + rand(-50, 50),
                    'is_enabled' => true,
                    'attributes' => json_encode([
                        'color' => ['Red', 'Blue', 'Green', 'Black', 'White'][$i % 5],
                        'size' => ['S', 'M', 'L', 'XL'][$i % 4],
                    ]),
                ]);
            }
        }

        return $variants;
    }

    private function createStockRecords(array $variants): void
    {
        $this->command->info('📊 Creating stock records...');
        
        foreach ($variants as $variant) {
            Stock::create([
                'product_variant_id' => $variant->id,
                'quantity' => rand(10, 100),
                'reserved_quantity' => rand(0, 5),
                'location' => 'Main Warehouse',
            ]);
        }
    }

    private function createAddresses(User $admin, array $countries, array $zones, array $cities): array
    {
        $this->command->info('🏠 Creating addresses...');
        
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
                'country_id' => $countries[0]->id,
                'zone_id' => $zones[0]->id,
                'phone' => '+37012345678',
                'email' => 'admin@example.com',
                'is_default' => true,
                'is_active' => true,
                'is_shipping' => true,
            ],
            [
                'user_id' => $admin->id,
                'type' => AddressType::BILLING,
                'first_name' => 'Admin',
                'last_name' => 'User',
                'address_line_1' => '456 Business Ave',
                'city' => 'Vilnius',
                'postal_code' => '01235',
                'country_code' => 'LT',
                'country_id' => $countries[0]->id,
                'zone_id' => $zones[0]->id,
                'phone' => '+37012345679',
                'email' => 'admin@example.com',
                'is_default' => false,
                'is_active' => true,
                'is_billing' => true,
            ],
        ];

        return collect($addresses)->map(function ($address) {
            return Address::create($address);
        })->toArray();
    }

    private function createOrders(User $admin, array $addresses): array
    {
        $this->command->info('🛒 Creating orders...');
        
        $orders = [];
        
        for ($i = 0; $i < 5; $i++) {
            $orders[] = Order::create([
                'user_id' => $admin->id,
                'order_number' => 'ORD-' . str_pad($i + 1, 6, '0', STR_PAD_LEFT),
                'status' => ['pending', 'processing', 'shipped', 'delivered', 'cancelled'][$i % 5],
                'total_amount' => rand(100, 1000),
                'shipping_address_id' => $addresses[0]->id,
                'billing_address_id' => $addresses[1]->id,
                'notes' => 'Sample order ' . ($i + 1),
            ]);
        }

        return $orders;
    }

    private function createOrderItems(array $orders, array $variants): void
    {
        $this->command->info('📦 Creating order items...');
        
        foreach ($orders as $order) {
            $itemCount = rand(1, 3);
            $selectedVariants = collect($variants)->random($itemCount);
            
            foreach ($selectedVariants as $variant) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_variant_id' => $variant->id,
                    'quantity' => rand(1, 5),
                    'unit_price' => $variant->price,
                    'total' => $variant->price * rand(1, 5),
                    'status' => ['pending', 'processing', 'shipped', 'delivered'][rand(0, 3)],
                ]);
            }
        }
    }

    private function createOrderShipping(array $orders): void
    {
        $this->command->info('🚚 Creating order shipping...');
        
        foreach ($orders as $order) {
            OrderShipping::create([
                'order_id' => $order->id,
                'shipping_method' => ['standard', 'express', 'overnight'][rand(0, 2)],
                'carrier' => ['DHL', 'UPS', 'FedEx', 'Post'][rand(0, 3)],
                'tracking_number' => 'TRK' . rand(100000, 999999),
                'status' => ['pending', 'shipped', 'in_transit', 'delivered'][rand(0, 3)],
                'base_cost' => rand(10, 50),
                'total_cost' => rand(15, 75),
            ]);
        }
    }

    private function createDocuments(array $orders): void
    {
        $this->command->info('📄 Creating documents...');
        
        foreach ($orders as $order) {
            Document::create([
                'order_id' => $order->id,
                'type' => ['invoice', 'receipt', 'shipping_label'][rand(0, 2)],
                'title' => 'Document for Order ' . $order->order_number,
                'content' => 'Sample document content',
                'is_public' => rand(0, 1),
                'status' => ['draft', 'approved', 'rejected'][rand(0, 2)],
            ]);
        }
    }

    private function createDiscountCodes(): void
    {
        $this->command->info('🎫 Creating discount codes...');
        
        $codes = [
            ['code' => 'WELCOME10', 'description' => 'Welcome discount', 'discount_percentage' => 10.0, 'is_active' => true],
            ['code' => 'SAVE20', 'description' => 'Save 20% on all items', 'discount_percentage' => 20.0, 'is_active' => true],
            ['code' => 'FREESHIP', 'description' => 'Free shipping code', 'discount_percentage' => 0.0, 'is_active' => true],
        ];

        foreach ($codes as $code) {
            DiscountCode::create($code);
        }
    }

    private function createSliders(): void
    {
        $this->command->info('🎠 Creating sliders...');
        
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
            [
                'title' => 'Special Offers',
                'description' => 'Limited time offers - Don\'t miss out!',
                'button_text' => 'Get Offers',
                'button_url' => '/offers',
                'background_color' => '#F59E0B',
                'text_color' => '#FFFFFF',
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($sliders as $slider) {
            Slider::create($slider);
        }
    }

    private function createRecommendationBlocks(): void
    {
        $this->command->info('💡 Creating recommendation blocks...');
        
        $blocks = [
            [
                'title' => 'Featured Products',
                'description' => 'Our top-rated products',
                'type' => 'featured',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'title' => 'Best Sellers',
                'description' => 'Most popular items',
                'type' => 'bestsellers',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'title' => 'New Arrivals',
                'description' => 'Latest products in store',
                'type' => 'new_arrivals',
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($blocks as $block) {
            RecommendationBlock::create($block);
        }
    }

    private function createSeoData(): void
    {
        $this->command->info('🔍 Creating SEO data...');
        
        $seoData = [
            [
                'page' => 'home',
                'title' => 'Home - Your Store',
                'description' => 'Welcome to our amazing store with great products',
                'keywords' => 'store, products, shopping, online',
                'is_active' => true,
            ],
            [
                'page' => 'products',
                'title' => 'Products - Your Store',
                'description' => 'Browse our wide selection of products',
                'keywords' => 'products, items, goods, merchandise',
                'is_active' => true,
            ],
        ];

        foreach ($seoData as $seo) {
            SeoData::create($seo);
        }
    }

    private function createSubscribers(): void
    {
        $this->command->info('📧 Creating subscribers...');
        
        $emails = [
            'subscriber1@example.com',
            'subscriber2@example.com',
            'subscriber3@example.com',
            'subscriber4@example.com',
            'subscriber5@example.com',
        ];

        foreach ($emails as $email) {
            Subscriber::create([
                'email' => $email,
                'is_active' => true,
                'subscribed_at' => now(),
            ]);
        }
    }

    private function createReferralRewards(User $admin): void
    {
        $this->command->info('🎁 Creating referral rewards...');
        
        ReferralReward::create([
            'user_id' => $admin->id,
            'referral_code' => 'ADMIN' . rand(100, 999),
            'reward_type' => 'discount',
            'reward_value' => 15.0,
            'is_active' => true,
            'expires_at' => now()->addMonths(6),
        ]);
    }

    private function createProductHistory(array $products): void
    {
        $this->command->info('📈 Creating product history...');
        
        foreach ($products as $product) {
            ProductHistory::create([
                'product_id' => $product->id,
                'action' => 'created',
                'old_data' => null,
                'new_data' => json_encode($product->toArray()),
                'user_id' => 1, // Admin user
            ]);
        }
    }

    private function createReports(): void
    {
        $this->command->info('📊 Creating reports...');
        
        // This would create report records if you have a reports table
        // For now, we'll just log that reports would be created
        $this->command->info('📊 Reports would be created here (if reports table exists)');
    }

    private function createLocations(array $countries, array $zones, array $cities): void
    {
        $this->command->info('📍 Creating locations...');
        
        $locations = [
            [
                'name' => 'Main Warehouse',
                'type' => 'warehouse',
                'address' => '123 Warehouse Street',
                'city' => 'Vilnius',
                'country_code' => 'LT',
                'country_id' => $countries[0]->id,
                'zone_id' => $zones[0]->id,
                'is_active' => true,
            ],
            [
                'name' => 'Store Location',
                'type' => 'store',
                'address' => '456 Main Street',
                'city' => 'Vilnius',
                'country_code' => 'LT',
                'country_id' => $countries[0]->id,
                'zone_id' => $zones[0]->id,
                'is_active' => true,
            ],
        ];

        foreach ($locations as $location) {
            Location::create($location);
        }
    }
}
