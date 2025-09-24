<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\AddressType;
use App\Models\Address;
use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\Currency;
use App\Models\CustomerGroup;
use App\Models\DiscountCode;
use App\Models\Document;
use App\Models\Inventory;
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
use App\Models\Subscriber;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * AdminSeeder
 *
 * Comprehensive seeder for admin@example.com user with all menu items
 * and sample data for testing and demonstration purposes.
 */
final class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🌱 Starting Comprehensive Admin Seeder...');

        // Create admin user
        $admin = $this->createAdminUser();

        // Create countries and cities
        $countries = $this->createCountries();
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

        // Create locations first
        $locations = $this->createLocations($countries, $cities);

        // Create stock records
        $this->createStockRecords($variants, $locations);

        // Create addresses
        $addresses = $this->createAddresses($admin, $countries, $cities);

        // Create orders and order items
        $orders = $this->createOrders($admin, $addresses);
        $this->createOrderItems($orders, $variants);

        // Create order shipping
        $this->createOrderShipping($orders);

        // Create documents
        // $this->createDocuments($orders); // Temporarily disabled - requires document_template_id

        // Create discount codes
        // $this->createDiscountCodes(); // Temporarily disabled - table doesn't exist

        // Create sliders
        $this->createSliders();

        // Create recommendation blocks
        $this->createRecommendationBlocks();

        // Create SEO data
        $this->createSeoData();

        // Create subscribers
        $this->createSubscribers();

        // Create referral rewards
        // $this->createReferralRewards($admin); // Temporarily disabled - requires referral_id

        // Create product history
        $this->createProductHistory($products);

        // Locations already created above

        $this->command->info('✅ Comprehensive Admin Seeder completed successfully!');
        $this->command->info('👤 Admin user: admin@example.com');
        $this->command->info('🔑 Password: password');
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

    private function createCities(array $countries): array
    {
        $this->command->info('🏙️ Creating cities...');

        $cities = [
            ['name' => 'Vilnius', 'slug' => 'vilnius', 'code' => 'VIL', 'country_id' => $countries[0]->id, 'is_active' => true, 'is_enabled' => true],
            ['name' => 'Riga', 'slug' => 'riga', 'code' => 'RIG', 'country_id' => $countries[1]->id, 'is_active' => true, 'is_enabled' => true],
            ['name' => 'Tallinn', 'slug' => 'tallinn', 'code' => 'TAL', 'country_id' => $countries[2]->id, 'is_active' => true, 'is_enabled' => true],
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
            ['name' => ['lt' => 'Euro', 'en' => 'Euro'], 'code' => 'EUR', 'symbol' => '€', 'exchange_rate' => 1.0, 'is_default' => true, 'is_enabled' => true, 'decimal_places' => 2],
            ['name' => ['lt' => 'US Dollar', 'en' => 'US Dollar'], 'code' => 'USD', 'symbol' => '$', 'exchange_rate' => 0.85, 'is_default' => false, 'is_enabled' => true, 'decimal_places' => 2],
            ['name' => ['lt' => 'British Pound', 'en' => 'British Pound'], 'code' => 'GBP', 'symbol' => '£', 'exchange_rate' => 1.15, 'is_default' => false, 'is_enabled' => true, 'decimal_places' => 2],
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

    private function createCustomerGroups(): array
    {
        $this->command->info('👥 Creating customer groups...');

        $groups = [
            ['name' => ['lt' => 'VIP Customers', 'en' => 'VIP Customers'], 'code' => 'VIP', 'description' => ['lt' => 'High-value customers with special privileges', 'en' => 'High-value customers with special privileges'], 'discount_percentage' => 15.0],
            ['name' => ['lt' => 'Regular Customers', 'en' => 'Regular Customers'], 'code' => 'REGULAR', 'description' => ['lt' => 'Standard customers', 'en' => 'Standard customers'], 'discount_percentage' => 5.0],
            ['name' => ['lt' => 'New Customers', 'en' => 'New Customers'], 'code' => 'NEW', 'description' => ['lt' => 'First-time customers', 'en' => 'First-time customers'], 'discount_percentage' => 10.0],
            ['name' => ['lt' => 'Wholesale', 'en' => 'Wholesale'], 'code' => 'WHOLESALE', 'description' => ['lt' => 'Bulk purchase customers', 'en' => 'Bulk purchase customers'], 'discount_percentage' => 20.0],
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
            // All non-building categories removed - keeping only building-related categories
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
                'is_visible' => true,
                'sku' => 'SP-001',
                'status' => 'published',
                'published_at' => now(),
            ],
            [
                'name' => 'Wireless Headphones',
                'slug' => 'wireless-headphones',
                'description' => 'High-quality wireless headphones with noise cancellation',
                'price' => 199.99,
                'is_visible' => true,
                'sku' => 'WH-002',
                'status' => 'published',
                'published_at' => now(),
            ],
            [
                'name' => 'Cotton T-Shirt',
                'slug' => 'cotton-t-shirt',
                'description' => 'Comfortable cotton t-shirt in various colors',
                'price' => 29.99,
                'is_visible' => true,
                'sku' => 'CT-003',
                'status' => 'published',
                'published_at' => now(),
            ],
            [
                'name' => 'Garden Tools Set',
                'slug' => 'garden-tools-set',
                'description' => 'Complete set of professional garden tools',
                'price' => 149.99,
                'is_visible' => true,
                'sku' => 'GT-004',
                'status' => 'published',
                'published_at' => now(),
            ],
            [
                'name' => 'Yoga Mat',
                'slug' => 'yoga-mat',
                'description' => 'Premium yoga mat for all fitness activities',
                'price' => 49.99,
                'is_visible' => true,
                'sku' => 'YM-005',
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
            if (isset($categories[$index]) && ! $createdProduct->categories()->where('category_id', $categories[$index]->id)->exists()) {
                $createdProduct->categories()->attach($categories[$index]->id);
            }

            $createdProducts[] = $createdProduct;
        }

        return $createdProducts;
    }

    private function createProductVariants(array $products): array
    {
        $this->command->info('🔧 Creating product variants...');

        $variants = [];

        foreach ($products as $product) {
            // Create 2-3 variants per product
            $variantCount = rand(2, 3);

            for ($i = 0; $i < $variantCount; $i++) {
                $variants[] = ProductVariant::firstOrCreate(
                    ['product_id' => $product->id, 'sku' => $product->sku.'-'.($i + 1)],
                    [
                        'product_id' => $product->id,
                        'name' => $product->name.' - Variant '.($i + 1),
                        'sku' => $product->sku.'-'.($i + 1),
                        'price' => $product->price + rand(-50, 50),
                        'is_enabled' => true,
                        'attributes' => json_encode([
                            'color' => ['Red', 'Blue', 'Green', 'Black', 'White'][$i % 5],
                            'size' => ['S', 'M', 'L', 'XL'][$i % 4],
                        ]),
                    ]
                );
            }
        }

        return $variants;
    }

    private function createStockRecords(array $variants, array $locations): void
    {
        $this->command->info('📊 Creating stock records...');

        foreach ($variants as $variant) {
            foreach ($locations as $location) {
                Inventory::firstOrCreate(
                    ['product_id' => $variant->product_id, 'location_id' => $location->id],
                    [
                        'product_id' => $variant->product_id,
                        'location_id' => $location->id,
                        'quantity' => rand(10, 100),
                        'reserved' => rand(0, 5),
                        'threshold' => 10,
                        'is_tracked' => true,
                    ]
                );
            }
        }
    }

    private function createAddresses(User $admin, array $countries, array $cities): array
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
                'phone' => '+37012345679',
                'email' => 'admin@example.com',
                'is_default' => false,
                'is_active' => true,
                'is_billing' => true,
            ],
        ];

        $createdAddresses = [];
        foreach ($addresses as $address) {
            $createdAddresses[] = Address::firstOrCreate(
                [
                    'user_id' => $address['user_id'],
                    'type' => $address['type'],
                    'address_line_1' => $address['address_line_1'],
                ],
                $address
            );
        }

        return $createdAddresses;
    }

    private function createOrders(User $admin, array $addresses): array
    {
        $this->command->info('🛒 Creating orders...');

        $orders = [];

        for ($i = 0; $i < 5; $i++) {
            $orderNumber = 'ORD-'.str_pad((string) ($i + 1), 6, '0', STR_PAD_LEFT);
            $orders[] = Order::firstOrCreate(
                ['number' => $orderNumber],
                [
                    'user_id' => $admin->id,
                    'number' => $orderNumber,
                    'status' => ['pending', 'processing', 'shipped', 'delivered', 'cancelled'][$i % 5],
                    'total' => rand(100, 1000),
                    'subtotal' => rand(80, 800),
                    'tax_amount' => rand(10, 100),
                    'shipping_amount' => rand(5, 50),
                    'currency' => 'EUR',
                    'notes' => 'Sample order '.($i + 1),
                ]
            );
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
                OrderItem::firstOrCreate(
                    [
                        'order_id' => $order->id,
                        'product_variant_id' => $variant->id,
                    ],
                    [
                        'order_id' => $order->id,
                        'product_id' => $variant->product_id,
                        'product_variant_id' => $variant->id,
                        'name' => $variant->name,
                        'sku' => $variant->sku,
                        'quantity' => rand(1, 5),
                        'unit_price' => abs((float) $variant->price),
                        'total' => abs((float) $variant->price) * rand(1, 5),
                    ]
                );
            }
        }
    }

    private function createOrderShipping(array $orders): void
    {
        $this->command->info('🚚 Creating order shipping...');

        foreach ($orders as $order) {
            OrderShipping::firstOrCreate(
                ['order_id' => $order->id],
                [
                    'order_id' => $order->id,
                    'carrier_name' => ['DHL', 'UPS', 'FedEx', 'Post'][rand(0, 3)],
                    'service' => ['standard', 'express', 'overnight'][rand(0, 2)],
                    'tracking_number' => 'TRK'.rand(100000, 999999),
                    // Use existing cost fields
                    'base_cost' => rand(10, 50),
                    'total_cost' => rand(15, 75),
                ]
            );
        }
    }

    private function createDocuments(array $orders): void
    {
        $this->command->info('📄 Creating documents...');

        foreach ($orders as $order) {
            Document::firstOrCreate(
                [
                    'documentable_type' => Order::class,
                    'documentable_id' => $order->id,
                ],
                [
                    'documentable_type' => Order::class,
                    'documentable_id' => $order->id,
                    'title' => 'Invoice for Order '.$order->number,
                    'content' => 'Sample document content',
                    'status' => ['draft', 'approved', 'rejected'][rand(0, 2)],
                    'format' => 'pdf',
                ]
            );
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
            DiscountCode::firstOrCreate(
                ['code' => $code['code']],
                $code
            );
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
                'description' => "Limited time offers - Don't miss out!",
                'button_text' => 'Get Offers',
                'button_url' => '/offers',
                'background_color' => '#F59E0B',
                'text_color' => '#FFFFFF',
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($sliders as $slider) {
            Slider::firstOrCreate(
                ['title' => $slider['title']],
                $slider
            );
        }
    }

    private function createRecommendationBlocks(): void
    {
        $this->command->info('💡 Creating recommendation blocks...');

        $blocks = [
            [
                'name' => 'featured',
                'title' => 'Featured Products',
                'description' => 'Our top-rated products',
                'config_ids' => [],
                'is_active' => true,
                'max_products' => 10,
            ],
            [
                'name' => 'bestsellers',
                'title' => 'Best Sellers',
                'description' => 'Most popular items',
                'config_ids' => [],
                'is_active' => true,
                'max_products' => 10,
            ],
            [
                'name' => 'new_arrivals',
                'title' => 'New Arrivals',
                'description' => 'Latest products in store',
                'config_ids' => [],
                'is_active' => true,
                'max_products' => 10,
            ],
        ];

        foreach ($blocks as $block) {
            RecommendationBlock::firstOrCreate(
                ['title' => $block['title']],
                $block
            );
        }
    }

    private function createSeoData(): void
    {
        $this->command->info('🔍 Creating SEO data...');

        $seoData = [
            [
                'seoable_type' => 'App\Models\Page',
                'seoable_id' => 1,
                'locale' => 'en',
                'title' => 'Home - Your Store',
                'description' => 'Welcome to our amazing store with great products',
                'keywords' => 'store, products, shopping, online',
                'canonical_url' => 'https://example.com',
                'no_index' => false,
                'no_follow' => false,
            ],
            [
                'seoable_type' => 'App\Models\Page',
                'seoable_id' => 2,
                'locale' => 'en',
                'title' => 'Products - Your Store',
                'description' => 'Browse our wide selection of products',
                'keywords' => 'products, items, goods, merchandise',
                'canonical_url' => 'https://example.com/products',
                'no_index' => false,
                'no_follow' => false,
            ],
        ];

        foreach ($seoData as $seo) {
            SeoData::firstOrCreate(
                [
                    'seoable_type' => $seo['seoable_type'],
                    'seoable_id' => $seo['seoable_id'],
                    'locale' => $seo['locale'],
                ],
                $seo
            );
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
            Subscriber::firstOrCreate(
                ['email' => $email],
                [
                    'email' => $email,
                    'status' => 'active',
                    'subscribed_at' => now(),
                ]
            );
        }
    }

    private function createReferralRewards(User $admin): void
    {
        $this->command->info('🎁 Creating referral rewards...');

        ReferralReward::firstOrCreate(
            ['user_id' => $admin->id],
            [
                'user_id' => $admin->id,
                'type' => 'referrer_bonus',
                'amount' => 15.0,
                'currency_code' => 'EUR',
                'status' => 'pending',
                'is_active' => true,
                'expires_at' => now()->addMonths(6),
            ]
        );
    }

    private function createProductHistory(array $products): void
    {
        $this->command->info('📈 Creating product history...');

        foreach ($products as $product) {
            ProductHistory::firstOrCreate(
                [
                    'product_id' => $product->id,
                    'action' => 'created',
                ],
                [
                    'product_id' => $product->id,
                    'action' => 'created',
                    'old_value' => null,
                    'new_value' => $product->toArray(),
                    'user_id' => 1,  // Admin user
                    'causer_type' => 'App\Models\User',
                    'causer_id' => 1,  // Admin user
                ]
            );
        }
    }

    private function createLocations(array $countries, array $cities): array
    {
        $this->command->info('📍 Creating locations...');

        $locations = [
            [
                'name' => 'Main Warehouse',
                'code' => 'MAIN-WH',
                'type' => 'warehouse',
                'address_line_1' => '123 Warehouse Street',
                'city' => 'Vilnius',
                'country_code' => 'LT',
                'is_enabled' => true,
            ],
            [
                'name' => 'Store Location',
                'code' => 'STORE-01',
                'type' => 'store',
                'address_line_1' => '456 Main Street',
                'city' => 'Vilnius',
                'country_code' => 'LT',
                'is_enabled' => true,
            ],
        ];

        $createdLocations = [];
        foreach ($locations as $location) {
            $createdLocations[] = Location::firstOrCreate(
                ['name' => $location['name']],
                $location
            );
        }

        return $createdLocations;
    }
}
