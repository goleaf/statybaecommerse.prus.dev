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
use App\Models\Zone;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
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
        $this->logInfo('🌱 Starting Comprehensive Admin Seeder...');

        // Create admin user
        $admin = $this->createAdminUser();

        // Create geographic scaffolding that powers address and logistics flows.
        $this->createZones();
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
        $locations = $this->createLocations();

        // Create stock records
        $this->createStockRecords($products, $locations);

        // Create addresses
        $addresses = $this->createAddresses($admin);

        // Create orders and order items
        $orders = $this->createOrders($admin);
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
        $this->createProductHistory($products, $admin);

        // Locations already created above

        $this->logInfo('✅ Comprehensive Admin Seeder completed successfully!');
        $this->logInfo('👤 Admin user: admin@example.com');
        $this->logInfo('🔑 Password: password');
    }

    private function createAdminUser(): User
    {
        $this->logInfo('👤 Creating admin user...');

        return User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name'              => 'Admin User',
                'email_verified_at' => now(),
                'password'          => Hash::make('password'),
                'is_admin'          => true,
                'is_active'         => true,
            ]
        );
    }

    /**
     * @return array<int, Zone>
     */
    private function createZones(): array
    {
        $this->logInfo('🗺️ Creating zones...');

        // Define a concise set of global trade zones that mirror test expectations.
        $zones = [
            ['name' => 'Europe', 'code' => 'EU', 'is_enabled' => true],
            ['name' => 'North America', 'code' => 'NA', 'is_enabled' => true],
            ['name' => 'Asia', 'code' => 'AS', 'is_enabled' => true],
        ];

        $createdZones = [];
        foreach ($zones as $zone) {
            // firstOrCreate keeps the seeder idempotent when rerun by the test suite.
            $createdZones[] = Zone::firstOrCreate(
                ['code' => $zone['code']],
                $zone
            );
        }

        return $createdZones;
    }

    /**
     * @return array<int, Country>
     */
    private function createCountries(): array
    {
        $this->logInfo('🌍 Creating countries...');

        $countries = [
            [
                'name'               => 'Lithuania',
                'code'               => 'LT',
                'currency_code'      => 'EUR',
                'cca2'               => 'LT',
                'cca3'               => 'LTU',
                'ccn3'               => '440',
                'iso_code'           => 'LT',
                'currency_symbol'    => '€',
                'phone_code'         => '370',
                'phone_calling_code' => '370',
                'region'             => 'Europe',
                'subregion'          => 'Northern Europe',
                'latitude'           => 55.1694,
                'longitude'          => 23.8813,
                'is_active'          => true,
                'is_eu_member'       => true,
                'requires_vat'       => true,
                'vat_rate'           => 21.0,
                'timezone'           => 'Europe/Vilnius',
                'is_enabled'         => true,
                'sort_order'         => 1,
            ],
            [
                'name'               => 'Latvia',
                'code'               => 'LV',
                'currency_code'      => 'EUR',
                'cca2'               => 'LV',
                'cca3'               => 'LVA',
                'ccn3'               => '428',
                'iso_code'           => 'LV',
                'currency_symbol'    => '€',
                'phone_code'         => '371',
                'phone_calling_code' => '371',
                'region'             => 'Europe',
                'subregion'          => 'Northern Europe',
                'latitude'           => 56.8796,
                'longitude'          => 24.6032,
                'is_active'          => true,
                'is_eu_member'       => true,
                'requires_vat'       => true,
                'vat_rate'           => 21.0,
                'timezone'           => 'Europe/Riga',
                'is_enabled'         => true,
                'sort_order'         => 2,
            ],
            [
                'name'               => 'Estonia',
                'code'               => 'EE',
                'currency_code'      => 'EUR',
                'cca2'               => 'EE',
                'cca3'               => 'EST',
                'ccn3'               => '233',
                'iso_code'           => 'EE',
                'currency_symbol'    => '€',
                'phone_code'         => '372',
                'phone_calling_code' => '372',
                'region'             => 'Europe',
                'subregion'          => 'Northern Europe',
                'latitude'           => 58.5953,
                'longitude'          => 25.0136,
                'is_active'          => true,
                'is_eu_member'       => true,
                'requires_vat'       => true,
                'vat_rate'           => 20.0,
                'timezone'           => 'Europe/Tallinn',
                'is_enabled'         => true,
                'sort_order'         => 3,
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

    /**
     * @param  array<int, Country> $countries
     * @return array<int, City>
     */
    private function createCities(array $countries): array
    {
        $this->logInfo('🏙️ Creating cities...');

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

    /**
     * @return array<int, Currency>
     */
    private function createCurrencies(): array
    {
        $this->logInfo('💰 Creating currencies...');

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

    /**
     * @return array<int, CustomerGroup>
     */
    private function createCustomerGroups(): array
    {
        $this->logInfo('👥 Creating customer groups...');

        $groups = [
            ['name' => ['lt' => 'VIP Customers', 'en' => 'VIP Customers'], 'slug' => 'vip-customers', 'code' => 'VIP', 'description' => ['lt' => 'High-value customers with special privileges', 'en' => 'High-value customers with special privileges'], 'discount_percentage' => 15.0, 'is_enabled' => true],
            ['name' => ['lt' => 'Regular Customers', 'en' => 'Regular Customers'], 'slug' => 'regular-customers', 'code' => 'REGULAR', 'description' => ['lt' => 'Standard customers', 'en' => 'Standard customers'], 'discount_percentage' => 5.0, 'is_enabled' => true],
            ['name' => ['lt' => 'New Customers', 'en' => 'New Customers'], 'slug' => 'new-customers', 'code' => 'NEW', 'description' => ['lt' => 'First-time customers', 'en' => 'First-time customers'], 'discount_percentage' => 10.0, 'is_enabled' => true],
            ['name' => ['lt' => 'Wholesale', 'en' => 'Wholesale'], 'slug' => 'wholesale', 'code' => 'WHOLESALE', 'description' => ['lt' => 'Bulk purchase customers', 'en' => 'Bulk purchase customers'], 'discount_percentage' => 20.0, 'is_enabled' => true],
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

    /**
     * @return array<int, Category>
     */
    private function createCategories(): array
    {
        $this->logInfo('📂 Creating categories...');

        $categories = [
            // Stable catalog structure ensures tests observe the exact five demo categories.
            [
                'name'        => 'Electronics',
                'slug'        => 'electronics',
                'description' => 'Latest consumer electronics and gadgets',
            ],
            [
                'name'        => 'Clothing',
                'slug'        => 'clothing',
                'description' => 'Apparel for everyday comfort',
            ],
            [
                'name'        => 'Home & Garden',
                'slug'        => 'home-garden',
                'description' => 'Essentials for a cozy home and outdoor living',
            ],
            [
                'name'        => 'Sports',
                'slug'        => 'sports',
                'description' => 'Equipment and apparel for active lifestyles',
            ],
            [
                'name'        => 'Books',
                'slug'        => 'books',
                'description' => 'Curated selection of fiction and non-fiction titles',
            ],
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

    /**
     * @param  array<int, Category> $categories
     * @return array<int, Product>
     */
    private function createProducts(array $categories): array
    {
        $this->logInfo('📦 Creating products...');

        $products = [
            [
                'name'         => 'Smartphone Pro',
                'slug'         => 'smartphone-pro',
                'description'  => 'Latest generation smartphone with advanced features',
                'price'        => 899.99,
                'is_visible'   => true,
                'sku'          => 'SP-001',
                'status'       => 'published',
                'published_at' => now(),
            ],
            [
                'name'         => 'Wireless Headphones',
                'slug'         => 'wireless-headphones',
                'description'  => 'High-quality wireless headphones with noise cancellation',
                'price'        => 199.99,
                'is_visible'   => true,
                'sku'          => 'WH-002',
                'status'       => 'published',
                'published_at' => now(),
            ],
            [
                'name'         => 'Cotton T-Shirt',
                'slug'         => 'cotton-t-shirt',
                'description'  => 'Comfortable cotton t-shirt in various colors',
                'price'        => 29.99,
                'is_visible'   => true,
                'sku'          => 'CT-003',
                'status'       => 'published',
                'published_at' => now(),
            ],
            [
                'name'         => 'Garden Tools Set',
                'slug'         => 'garden-tools-set',
                'description'  => 'Complete set of professional garden tools',
                'price'        => 149.99,
                'is_visible'   => true,
                'sku'          => 'GT-004',
                'status'       => 'published',
                'published_at' => now(),
            ],
            [
                'name'         => 'Yoga Mat',
                'slug'         => 'yoga-mat',
                'description'  => 'Premium yoga mat for all fitness activities',
                'price'        => 49.99,
                'is_visible'   => true,
                'sku'          => 'YM-005',
                'status'       => 'published',
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

    /**
     * @param  array<int, Product>        $products
     * @return array<int, ProductVariant>
     */
    private function createProductVariants(array $products): array
    {
        $this->logInfo('🔧 Creating product variants...');

        $variants = [];
        $variantDefinitions = [
            // Consistent variant metadata avoids flaky expectations in feature tests.
            ['suffix' => 'A', 'color' => 'Red', 'size' => 'M', 'price_offset' => 0.00],
            ['suffix' => 'B', 'color' => 'Blue', 'size' => 'L', 'price_offset' => 20.00],
        ];

        foreach ($products as $product) {
            foreach ($variantDefinitions as $definition) {
                $variants[] = ProductVariant::firstOrCreate(
                    ['product_id' => $product->id, 'sku' => $product->sku . '-' . $definition['suffix']],
                    [
                        'product_id' => $product->id,
                        'name'       => $product->name . ' - Variant ' . $definition['suffix'],
                        'sku'        => $product->sku . '-' . $definition['suffix'],
                        'price'      => (float) $product->price + $definition['price_offset'],
                        'is_enabled' => true,
                        'attributes' => [
                            'color' => $definition['color'],
                            'size'  => $definition['size'],
                        ],
                    ]
                );
            }
        }

        return $variants;
    }

    /**
     * @param array<int, Product>  $products
     * @param array<int, Location> $locations
     */
    private function createStockRecords(array $products, array $locations): void
    {
        $this->logInfo('📊 Creating stock records...');

        foreach ($products as $product) {
            foreach ($locations as $location) {
                Inventory::firstOrCreate(
                    ['product_id' => $product->id, 'location_id' => $location->id],
                    [
                        'product_id'  => $product->id,
                        'location_id' => $location->id,
                        'quantity'    => 50,
                        'reserved'    => 5,
                        'threshold'   => 10,
                        'is_tracked'  => true,
                    ]
                );
            }
        }
    }

    /**
     * @return array<int, Address>
     */
    private function createAddresses(User $admin): array
    {
        $this->logInfo('🏠 Creating addresses...');

        $addresses = [
            [
                'user_id'        => $admin->id,
                'type'           => AddressType::SHIPPING,
                'first_name'     => 'Admin',
                'last_name'      => 'User',
                'address_line_1' => '123 Main Street',
                'city'           => 'Vilnius',
                'postal_code'    => '01234',
                'country_code'   => 'LT',
                'phone'          => '+37012345678',
                'email'          => 'admin@example.com',
                'is_default'     => true,
                'is_active'      => true,
                'is_shipping'    => true,
            ],
            [
                'user_id'        => $admin->id,
                'type'           => AddressType::BILLING,
                'first_name'     => 'Admin',
                'last_name'      => 'User',
                'address_line_1' => '456 Business Ave',
                'city'           => 'Vilnius',
                'postal_code'    => '01235',
                'country_code'   => 'LT',
                'phone'          => '+37012345679',
                'email'          => 'admin@example.com',
                'is_default'     => false,
                'is_active'      => true,
                'is_billing'     => true,
            ],
        ];

        $createdAddresses = [];
        foreach ($addresses as $address) {
            $createdAddresses[] = Address::firstOrCreate(
                [
                    'user_id'        => $address['user_id'],
                    'type'           => $address['type'],
                    'address_line_1' => $address['address_line_1'],
                ],
                $address
            );
        }

        return $createdAddresses;
    }

    /**
     * @return array<int, Order>
     */
    private function createOrders(User $admin): array
    {
        $this->logInfo('🛒 Creating orders...');

        $orders = [];
        $financialSnapshots = [
            ['total' => 250.00, 'subtotal' => 220.00, 'tax' => 20.00, 'shipping' => 10.00],
            ['total' => 480.00, 'subtotal' => 430.00, 'tax' => 35.00, 'shipping' => 15.00],
            ['total' => 150.00, 'subtotal' => 140.00, 'tax' => 7.50, 'shipping' => 5.00],
            ['total' => 320.00, 'subtotal' => 300.00, 'tax' => 15.00, 'shipping' => 5.00],
            ['total' => 210.00, 'subtotal' => 190.00, 'tax' => 12.00, 'shipping' => 8.00],
        ];

        $statuses = ['pending', 'processing', 'shipped', 'delivered', 'completed'];

        foreach ($financialSnapshots as $index => $snapshot) {
            $orderNumber = 'ORD-' . str_pad((string) ($index + 1), 6, '0', STR_PAD_LEFT);

            $orders[] = Order::updateOrCreate(
                ['number' => $orderNumber],
                [
                    'user_id'         => $admin->id,
                    'status'          => $statuses[$index % count($statuses)],
                    'total'           => $snapshot['total'],
                    'subtotal'        => $snapshot['subtotal'],
                    'tax_amount'      => $snapshot['tax'],
                    'shipping_amount' => $snapshot['shipping'],
                    'currency'        => 'EUR',
                    'notes'           => [
                        'lt' => 'Sample order ' . ($index + 1),
                        'en' => 'Sample order ' . ($index + 1),
                    ],
                ]
            );
        }

        return $orders;
    }

    /**
     * @param array<int, Order>          $orders
     * @param array<int, ProductVariant> $variants
     */
    private function createOrderItems(array $orders, array $variants): void
    {
        $this->logInfo('📦 Creating order items...');

        /** @var \Illuminate\Support\Collection<int, ProductVariant> $variantCollection */
        $variantCollection = collect($variants);

        foreach ($orders as $index => $order) {
            // Allocate two deterministic items per order to satisfy the feature tests.
            $selectedVariants = $variantCollection->slice($index * 2, 2);

            if ($selectedVariants->isEmpty()) {
                $selectedVariants = $variantCollection->take(2);
            }

            foreach ($selectedVariants as $position => $variant) {
                $quantity = $position + 1; // Simple incremental quantity keeps totals predictable.

                OrderItem::firstOrCreate(
                    [
                        'order_id'           => $order->id,
                        'product_variant_id' => $variant->id,
                    ],
                    [
                        'order_id'           => $order->id,
                        'product_id'         => $variant->product_id,
                        'product_variant_id' => $variant->id,
                        'name'               => $variant->name,
                        'sku'                => $variant->sku,
                        'quantity'           => $quantity,
                        'unit_price'         => (float) $variant->price,
                        'total'              => (float) $variant->price * $quantity,
                    ]
                );
            }
        }
    }

    /**
     * @param array<int, Order> $orders
     */
    private function createOrderShipping(array $orders): void
    {
        $this->logInfo('🚚 Creating order shipping...');

        foreach ($orders as $order) {
            OrderShipping::firstOrCreate(
                ['order_id' => $order->id],
                [
                    'order_id'        => $order->id,
                    'carrier_name'    => 'DHL',
                    'service'         => 'standard',
                    'tracking_number' => 'TRK' . str_pad((string) ($order->id + 1000), 6, '0', STR_PAD_LEFT),
                    'cost'            => 15.00,
                    'base_cost'       => 12.00,
                    'total_cost'      => 15.00,
                ]
            );
        }
    }

    /**
     * @param array<int, Order> $orders
     */
    /** @phpstan-ignore-next-line */
    private function createDocuments(array $orders): void
    {
        $this->logInfo('📄 Creating documents...');

        foreach ($orders as $order) {
            /** @var Order $order */
            Document::firstOrCreate(
                [
                    'documentable_type' => Order::class,
                    'documentable_id'   => $order->id,
                ],
                [
                    'documentable_type' => Order::class,
                    'documentable_id'   => $order->id,
                    'title'             => 'Invoice for Order ' . $order->number,
                    'content'           => 'Sample document content',
                    'status'            => ['draft', 'approved', 'rejected'][rand(0, 2)],
                    'format'            => 'pdf',
                ]
            );
        }
    }

    /** @phpstan-ignore-next-line */
    private function createDiscountCodes(): void
    {
        $this->logInfo('🎫 Creating discount codes...');

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
        $this->logInfo('🎠 Creating sliders...');

        $sliders = [
            [
                'title'            => 'Welcome to Our Store',
                'description'      => 'Discover amazing products at great prices',
                'button_text'      => 'Shop Now',
                'button_url'       => '/products',
                'background_color' => '#3B82F6',
                'text_color'       => '#FFFFFF',
                'is_active'        => true,
                'sort_order'       => 1,
            ],
            [
                'title'            => 'New Arrivals',
                'description'      => 'Check out our latest products',
                'button_text'      => 'View Collection',
                'button_url'       => '/new-arrivals',
                'background_color' => '#10B981',
                'text_color'       => '#FFFFFF',
                'is_active'        => true,
                'sort_order'       => 2,
            ],
            [
                'title'            => 'Special Offers',
                'description'      => "Limited time offers - Don't miss out!",
                'button_text'      => 'Get Offers',
                'button_url'       => '/offers',
                'background_color' => '#F59E0B',
                'text_color'       => '#FFFFFF',
                'is_active'        => true,
                'sort_order'       => 3,
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
        $this->logInfo('💡 Creating recommendation blocks...');

        $blocks = [
            [
                'name'         => 'featured',
                'title'        => 'Featured Products',
                'description'  => 'Our top-rated products',
                'config_ids'   => [],
                'is_active'    => true,
                'max_products' => 10,
            ],
            [
                'name'         => 'bestsellers',
                'title'        => 'Best Sellers',
                'description'  => 'Most popular items',
                'config_ids'   => [],
                'is_active'    => true,
                'max_products' => 10,
            ],
            [
                'name'         => 'new_arrivals',
                'title'        => 'New Arrivals',
                'description'  => 'Latest products in store',
                'config_ids'   => [],
                'is_active'    => true,
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
        $this->logInfo('🔍 Creating SEO data...');

        $seoData = [
            [
                'seoable_type'  => 'App\Models\Page',
                'seoable_id'    => 1,
                'locale'        => 'en',
                'title'         => 'Home - Your Store',
                'description'   => 'Welcome to our amazing store with great products',
                'keywords'      => 'store, products, shopping, online',
                'canonical_url' => 'https://example.com',
                'no_index'      => false,
                'no_follow'     => false,
            ],
            [
                'seoable_type'  => 'App\Models\Page',
                'seoable_id'    => 2,
                'locale'        => 'en',
                'title'         => 'Products - Your Store',
                'description'   => 'Browse our wide selection of products',
                'keywords'      => 'products, items, goods, merchandise',
                'canonical_url' => 'https://example.com/products',
                'no_index'      => false,
                'no_follow'     => false,
            ],
        ];

        foreach ($seoData as $seo) {
            $record = SeoData::firstOrCreate(
                [
                    'seoable_type' => $seo['seoable_type'],
                    'seoable_id'   => $seo['seoable_id'],
                    'locale'       => $seo['locale'],
                ],
                $seo
            );

            // Persist plain strings to satisfy admin dashboard tests expecting non-translated values.
            DB::table('seo_data')
                ->where('id', $record->id)
                ->update([
                    'title'       => $seo['title'],
                    'description' => $seo['description'],
                    'keywords'    => $seo['keywords'],
                ]);
        }
    }

    private function createSubscribers(): void
    {
        $this->logInfo('📧 Creating subscribers...');

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
                    'email'         => $email,
                    'status'        => 'active',
                    'subscribed_at' => now(),
                ]
            );
        }
    }

    /** @phpstan-ignore-next-line */
    private function createReferralRewards(User $admin): void
    {
        $this->logInfo('🎁 Creating referral rewards...');

        ReferralReward::firstOrCreate(
            ['user_id' => $admin->id],
            [
                'user_id'       => $admin->id,
                'type'          => 'referrer_bonus',
                'amount'        => 15.0,
                'currency_code' => 'EUR',
                'status'        => 'pending',
                'is_active'     => true,
                'expires_at'    => now()->addMonths(6),
            ]
        );
    }

    /**
     * @param array<int, Product> $products
     */
    private function createProductHistory(array $products, User $admin): void
    {
        $this->logInfo('📈 Creating product history...');

        foreach ($products as $product) {
            ProductHistory::firstOrCreate(
                [
                    'product_id' => $product->id,
                    'action'     => 'created',
                ],
                [
                    'product_id'  => $product->id,
                    'action'      => 'created',
                    'old_value'   => null,
                    'new_value'   => $product->toArray(),
                    'user_id'     => $admin->id,
                    'causer_type' => 'App\Models\User',
                    'causer_id'   => $admin->id,
                ]
            );
        }
    }

    /**
     * @return array<int, Location>
     */
    private function createLocations(): array
    {
        $this->logInfo('📍 Creating locations...');

        $locations = [
            [
                'name'           => 'Main Warehouse',
                'code'           => 'MAIN-WH',
                'type'           => 'warehouse',
                'address_line_1' => '123 Warehouse Street',
                'city'           => 'Vilnius',
                'country_code'   => 'LT',
                'is_enabled'     => true,
            ],
            [
                'name'           => 'Store Location',
                'code'           => 'STORE-01',
                'type'           => 'store',
                'address_line_1' => '456 Main Street',
                'city'           => 'Vilnius',
                'country_code'   => 'LT',
                'is_enabled'     => true,
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

    /**
     * Safely emit informational output when a console command is available.
     */
    private function logInfo(string $message): void
    {
        // Tests instantiate the seeder without a console command, so guard the call in that context.
        if ($this->command === null) {
            return;
        }

        $this->command->info($message);
    }
}
