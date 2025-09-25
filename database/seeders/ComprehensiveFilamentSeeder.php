<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class ComprehensiveFilamentSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedSystemSettings();
        $this->seedEnhancedPermissions();
        $this->seedEnhancedRoles();
        $this->seedAdminUsers();
        $this->enhanceExistingData();
    }

    private function seedSystemSettings(): void
    {
        $settings = [
            // General Settings
            [
                'key' => 'store_name',
                'display_name' => 'Store Name',
                'type' => 'string',
                'value' => config('app.name', 'E-Commerce Store'),
                'group' => 'general',
                'description' => 'The name of your store',
                'is_public' => true,
                'is_required' => true,
            ],
            [
                'key' => 'store_description',
                'display_name' => 'Store Description',
                'type' => 'text',
                'value' => 'Your trusted online shopping destination',
                'group' => 'general',
                'description' => 'Brief description of your store',
                'is_public' => true,
                'is_required' => false,
            ],
            [
                'key' => 'store_email',
                'display_name' => 'Store Email',
                'type' => 'email',
                'value' => 'info@example.com',
                'group' => 'general',
                'description' => 'Main contact email for the store',
                'is_public' => true,
                'is_required' => true,
            ],
            [
                'key' => 'store_phone',
                'display_name' => 'Store Phone',
                'type' => 'string',
                'value' => '+370 600 00000',
                'group' => 'general',
                'description' => 'Main contact phone number',
                'is_public' => true,
                'is_required' => false,
            ],

            // Currency Settings
            [
                'key' => 'default_currency',
                'display_name' => 'Default Currency',
                'type' => 'string',
                'value' => 'EUR',
                'group' => 'currency',
                'description' => 'Default currency for the store',
                'is_public' => true,
                'is_required' => true,
            ],
            [
                'key' => 'currency_symbol',
                'display_name' => 'Currency Symbol',
                'type' => 'string',
                'value' => '€',
                'group' => 'currency',
                'description' => 'Symbol for the default currency',
                'is_public' => true,
                'is_required' => true,
            ],

            // Email Settings
            [
                'key' => 'email_from_name',
                'display_name' => 'Email From Name',
                'type' => 'string',
                'value' => config('app.name'),
                'group' => 'email',
                'description' => 'Name used in outgoing emails',
                'is_public' => false,
                'is_required' => true,
            ],
            [
                'key' => 'email_from_address',
                'display_name' => 'Email From Address',
                'type' => 'email',
                'value' => 'noreply@example.com',
                'group' => 'email',
                'description' => 'Email address used for outgoing emails',
                'is_public' => false,
                'is_required' => true,
            ],

            // SEO Settings
            [
                'key' => 'meta_title',
                'display_name' => 'Default Meta Title',
                'type' => 'string',
                'value' => config('app.name').' - Online Store',
                'group' => 'seo',
                'description' => 'Default meta title for pages',
                'is_public' => true,
                'is_required' => false,
            ],
            [
                'key' => 'meta_description',
                'display_name' => 'Default Meta Description',
                'type' => 'text',
                'value' => 'Shop the best products at great prices with fast shipping and excellent customer service.',
                'group' => 'seo',
                'description' => 'Default meta description for pages',
                'is_public' => true,
                'is_required' => false,
            ],

            // Features Settings
            [
                'key' => 'enable_reviews',
                'display_name' => 'Enable Product Reviews',
                'type' => 'boolean',
                'value' => 'true',
                'group' => 'features',
                'description' => 'Allow customers to leave product reviews',
                'is_public' => true,
                'is_required' => false,
            ],
            [
                'key' => 'enable_wishlist',
                'display_name' => 'Enable Wishlist',
                'type' => 'boolean',
                'value' => 'true',
                'group' => 'features',
                'description' => 'Allow customers to save products to wishlist',
                'is_public' => true,
                'is_required' => false,
            ],
        ];

        foreach ($settings as $setting) {
            $existingSetting = Setting::where('key', $setting['key'])->first();
            
            if ($existingSetting) {
                $existingSetting->update($setting);
            } else {
                Setting::factory()
                    ->state($setting)
                    ->create();
            }
        }
    }

    private function seedEnhancedPermissions(): void
    {
        $permissions = [
            // Analytics permissions
            'view_analytics_dashboard',
            'export_analytics',
            'view_customer_analytics',
            'view_product_analytics',
            'view_sales_analytics',

            // Inventory permissions
            'view_inventory',
            'manage_inventory',
            'adjust_stock',
            'bulk_stock_operations',
            'view_inventory_reports',

            // Customer management permissions
            'view_customer_details',
            'edit_customer_preferences',
            'deactivate_customers',
            'export_customer_data',
            'send_customer_emails',
            'impersonate_customers',

            // System permissions
            'view_system_health',
            'manage_system_settings',
            'view_error_logs',
            'clear_system_cache',
            'backup_database',
            'restore_database',
        ];

        foreach ($permissions as $permission) {
            $existingPermission = Permission::where('name', $permission)->first();
            
            if (!$existingPermission) {
                Permission::factory()
                    ->state(['name' => $permission])
                    ->create();
            }
        }
    }

    private function seedEnhancedRoles(): void
    {
        // Inventory Manager role
        $inventoryManager = Role::where('name', 'inventory_manager')->first();
        if (!$inventoryManager) {
            $inventoryManager = Role::factory()
                ->state(['name' => 'inventory_manager'])
                ->create();
        }
        $inventoryPermissions = [
            'view_products', 'edit_products',
            'view_inventory', 'manage_inventory', 'adjust_stock',
            'bulk_stock_operations', 'view_inventory_reports',
            'view_analytics_dashboard', 'view_product_analytics',
        ];
        $inventoryManager->givePermissionTo($inventoryPermissions);

        // Customer Service role
        $customerService = Role::where('name', 'customer_service')->first();
        if (!$customerService) {
            $customerService = Role::factory()
                ->state(['name' => 'customer_service'])
                ->create();
        }
        $customerServicePermissions = [
            'view_customers', 'edit_customers',
            'view_customer_details', 'edit_customer_preferences',
            'view_orders', 'edit_orders',
            'send_customer_emails', 'view_customer_analytics',
        ];
        $customerService->givePermissionTo($customerServicePermissions);

        // Analytics Manager role
        $analyticsManager = Role::where('name', 'analytics_manager')->first();
        if (!$analyticsManager) {
            $analyticsManager = Role::factory()
                ->state(['name' => 'analytics_manager'])
                ->create();
        }
        $analyticsPermissions = [
            'view_analytics_dashboard', 'export_analytics',
            'view_customer_analytics', 'view_product_analytics', 'view_sales_analytics',
            'view_customers', 'view_products', 'view_orders',
        ];
        $analyticsManager->givePermissionTo($analyticsPermissions);
    }

    private function seedAdminUsers(): void
    {
        // Inventory Manager User
        $inventoryManager = User::where('email', 'inventory@example.com')->first();
        if (!$inventoryManager) {
            $inventoryManager = User::factory()
                ->state([
                    'email' => 'inventory@example.com',
                    'name' => 'Inventory Manager',
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'is_admin' => true,
                    'is_active' => true,
                    'timezone' => 'Europe/Vilnius',
                    'preferred_locale' => 'lt',
                ])
                ->create();
        }
        $inventoryManager->assignRole('inventory_manager');

        // Customer Service User
        $customerService = User::where('email', 'support@example.com')->first();
        if (!$customerService) {
            $customerService = User::factory()
                ->state([
                    'email' => 'support@example.com',
                    'name' => 'Customer Service',
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'is_admin' => true,
                    'is_active' => true,
                    'timezone' => 'Europe/Vilnius',
                    'preferred_locale' => 'lt',
                ])
                ->create();
        }
        $customerService->assignRole('customer_service');

        // Analytics Manager User
        $analyticsManager = User::where('email', 'analytics@example.com')->first();
        if (!$analyticsManager) {
            $analyticsManager = User::factory()
                ->state([
                    'email' => 'analytics@example.com',
                    'name' => 'Analytics Manager',
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'is_admin' => true,
                    'is_active' => true,
                    'timezone' => 'Europe/Vilnius',
                    'preferred_locale' => 'lt',
                ])
                ->create();
        }
        $analyticsManager->assignRole('analytics_manager');
    }

    private function enhanceExistingData(): void
    {
        // Update existing products with enhanced fields if they exist with timeout protection
        $timeout = now()->addMinutes(10); // 10 minute timeout for product enhancement

        Product::where('is_visible', true)
            ->cursor()
            ->takeUntilTimeout($timeout)
            ->chunk(50)
            ->each(function ($products): void {
                foreach ($products as $product) {
                    $updateData = [];

                    // Only update columns that exist
                    if (! $product->meta_title && $product->name) {
                        $updateData['meta_title'] = $product->name;
                    }

                    if (! $product->is_featured) {
                        $updateData['is_featured'] = fake()->boolean(15); // 15% featured
                    }

                    if (! $product->sort_order) {
                        $updateData['sort_order'] = fake()->numberBetween(1, 1000);
                    }

                    if (! $product->published_at) {
                        $updateData['published_at'] = fake()->dateTimeBetween('-6 months', 'now');
                    }

                    if (! empty($updateData)) {
                        try {
                            $product->update($updateData);
                        } catch (\Exception $e) {
                            // Skip if columns don't exist yet
                            continue;
                        }
                    }
                }
            });

        // Update existing categories with timeout protection
        $categoryTimeout = now()->addMinutes(5); // 5 minute timeout for category updates

        Category::where('is_visible', true)
            ->cursor()
            ->takeUntilTimeout($categoryTimeout)
            ->chunk(50)
            ->each(function ($categories): void {
                foreach ($categories as $category) {
                    try {
                        $category->update([
                            'is_featured' => $category->is_featured ?? fake()->boolean(25),
                            'sort_order' => $category->sort_order ?? fake()->numberBetween(1, 100),
                        ]);
                    } catch (\Exception $e) {
                        // Skip if columns don't exist yet
                        continue;
                    }
                }
            });

        // Update existing brands with timeout protection
        $brandTimeout = now()->addMinutes(5); // 5 minute timeout for brand updates

        Brand::where('is_visible', true)
            ->cursor()
            ->takeUntilTimeout($brandTimeout)
            ->chunk(50)
            ->each(function ($brands): void {
                foreach ($brands as $brand) {
                    try {
                        $brand->update([
                            'is_featured' => $brand->is_featured ?? fake()->boolean(20),
                            'sort_order' => $brand->sort_order ?? fake()->numberBetween(1, 100),
                        ]);
                    } catch (\Exception $e) {
                        // Skip if columns don't exist yet
                        continue;
                    }
                }
            });
    }
}
