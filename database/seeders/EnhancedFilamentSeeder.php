<?php declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\CustomerGroup;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

final class EnhancedFilamentSeeder extends Seeder
{
    public function run(): void
    {
        // Create enhanced settings
        $this->createSettings();
        
        // Create roles and permissions
        $this->createRolesAndPermissions();
        
        // Create admin user
        $this->createAdminUser();
        
        // Create customer groups
        $this->createCustomerGroups();
        
        // Create product attributes
        $this->createAttributes();
        
        // Create sample customers
        $this->createCustomers();
        
        $this->command->info('Enhanced Filament seeder completed successfully!');
    }

    private function createSettings(): void
    {
        $settings = [
            [
                'key' => 'app_name',
                'value' => 'E-Commerce Store',
                'type' => 'string',
                'description' => 'Application name',
                'is_public' => true,
            ],
            [
                'key' => 'currency_code',
                'value' => 'EUR',
                'type' => 'string',
                'description' => 'Default currency code',
                'is_public' => true,
            ],
            [
                'key' => 'default_locale',
                'value' => 'lt',
                'type' => 'string',
                'description' => 'Default application locale',
                'is_public' => true,
            ],
            [
                'key' => 'tax_rate',
                'value' => '21.00',
                'type' => 'decimal',
                'description' => 'Default tax rate percentage',
                'is_public' => false,
            ],
            [
                'key' => 'free_shipping_threshold',
                'value' => '50.00',
                'type' => 'decimal',
                'description' => 'Minimum order amount for free shipping',
                'is_public' => true,
            ],
            [
                'key' => 'enable_reviews',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Enable product reviews',
                'is_public' => true,
            ],
            [
                'key' => 'enable_wishlist',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Enable wishlist functionality',
                'is_public' => true,
            ],
            [
                'key' => 'enable_comparison',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Enable product comparison',
                'is_public' => true,
            ],
            [
                'key' => 'maintenance_mode',
                'value' => '0',
                'type' => 'boolean',
                'description' => 'Enable maintenance mode',
                'is_public' => false,
            ],
            [
                'key' => 'contact_email',
                'value' => 'info@example.com',
                'type' => 'string',
                'description' => 'Contact email address',
                'is_public' => true,
            ],
            [
                'key' => 'contact_phone',
                'value' => '+370 600 00000',
                'type' => 'string',
                'description' => 'Contact phone number',
                'is_public' => true,
            ],
            [
                'key' => 'company_address',
                'value' => json_encode([
                    'street' => 'Gedimino pr. 1',
                    'city' => 'Vilnius',
                    'postal_code' => '01103',
                    'country' => 'Lithuania',
                ]),
                'type' => 'json',
                'description' => 'Company address',
                'is_public' => true,
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }

        $this->command->info('Settings created successfully!');
    }

    private function createRolesAndPermissions(): void
    {
        // Create permissions
        $permissions = [
            'view_admin_panel',
            'manage_products',
            'manage_orders',
            'manage_customers',
            'manage_reviews',
            'manage_settings',
            'manage_users',
            'manage_roles',
            'manage_brands',
            'manage_categories',
            'manage_collections',
            'manage_attributes',
            'manage_discounts',
            'manage_campaigns',
            'view_analytics',
            'export_data',
            'import_data',
            'impersonate_users',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin']);
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $managerRole = Role::firstOrCreate(['name' => 'manager']);
        $customerRole = Role::firstOrCreate(['name' => 'customer']);

        // Assign permissions to roles
        $superAdminRole->syncPermissions($permissions);
        
        $adminRole->syncPermissions([
            'view_admin_panel',
            'manage_products',
            'manage_orders',
            'manage_customers',
            'manage_reviews',
            'manage_brands',
            'manage_categories',
            'manage_collections',
            'manage_attributes',
            'manage_discounts',
            'view_analytics',
            'export_data',
        ]);
        
        $managerRole->syncPermissions([
            'view_admin_panel',
            'manage_products',
            'manage_orders',
            'manage_reviews',
            'view_analytics',
        ]);

        $this->command->info('Roles and permissions created successfully!');
    }

    private function createAdminUser(): void
    {
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'is_active' => true,
                'preferred_locale' => 'lt',
            ]
        );

        $adminUser->assignRole('super_admin');

        $this->command->info('Admin user created successfully!');
    }

    private function createCustomerGroups(): void
    {
        $groups = [
            [
                'name' => 'VIP Customers',
                'slug' => 'vip-customers',
                'description' => 'High-value customers with special privileges',
                'discount_percentage' => 10.00,
                'is_enabled' => true,
                'conditions' => json_encode([
                    'min_orders' => 10,
                    'min_total_spent' => 1000,
                ]),
            ],
            [
                'name' => 'Regular Customers',
                'slug' => 'regular-customers',
                'description' => 'Standard customer group',
                'discount_percentage' => 0.00,
                'is_enabled' => true,
            ],
            [
                'name' => 'Wholesale Customers',
                'slug' => 'wholesale-customers',
                'description' => 'Bulk buyers with wholesale pricing',
                'discount_percentage' => 15.00,
                'is_enabled' => true,
                'conditions' => json_encode([
                    'min_quantity_per_order' => 50,
                ]),
            ],
        ];

        foreach ($groups as $group) {
            CustomerGroup::firstOrCreate(
                ['slug' => $group['slug']],
                $group
            );
        }

        $this->command->info('Customer groups created successfully!');
    }

    private function createAttributes(): void
    {
        // Color attribute
        $colorAttribute = Attribute::firstOrCreate(
            ['slug' => 'color'],
            [
                'name' => 'Color',
                'type' => 'select',
                'is_required' => false,
                'is_filterable' => true,
                'is_searchable' => false,
                'sort_order' => 1,
                'is_enabled' => true,
            ]
        );

        $colors = [
            ['value' => 'Red', 'color_code' => '#FF0000'],
            ['value' => 'Blue', 'color_code' => '#0000FF'],
            ['value' => 'Green', 'color_code' => '#00FF00'],
            ['value' => 'Black', 'color_code' => '#000000'],
            ['value' => 'White', 'color_code' => '#FFFFFF'],
            ['value' => 'Yellow', 'color_code' => '#FFFF00'],
        ];

        foreach ($colors as $index => $color) {
            AttributeValue::firstOrCreate(
                [
                    'attribute_id' => $colorAttribute->id,
                    'slug' => \Illuminate\Support\Str::slug($color['value']),
                ],
                [
                    'value' => $color['value'],
                    'color_code' => $color['color_code'],
                    'sort_order' => $index + 1,
                    'is_enabled' => true,
                ]
            );
        }

        // Size attribute
        $sizeAttribute = Attribute::firstOrCreate(
            ['slug' => 'size'],
            [
                'name' => 'Size',
                'type' => 'select',
                'is_required' => false,
                'is_filterable' => true,
                'is_searchable' => false,
                'sort_order' => 2,
                'is_enabled' => true,
            ]
        );

        $sizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];
        foreach ($sizes as $index => $size) {
            AttributeValue::firstOrCreate(
                [
                    'attribute_id' => $sizeAttribute->id,
                    'slug' => \Illuminate\Support\Str::slug($size),
                ],
                [
                    'value' => $size,
                    'sort_order' => $index + 1,
                    'is_enabled' => true,
                ]
            );
        }

        // Material attribute
        $materialAttribute = Attribute::firstOrCreate(
            ['slug' => 'material'],
            [
                'name' => 'Material',
                'type' => 'select',
                'is_required' => false,
                'is_filterable' => true,
                'is_searchable' => true,
                'sort_order' => 3,
                'is_enabled' => true,
            ]
        );

        $materials = ['Cotton', 'Polyester', 'Wool', 'Silk', 'Leather', 'Denim'];
        foreach ($materials as $index => $material) {
            AttributeValue::firstOrCreate(
                [
                    'attribute_id' => $materialAttribute->id,
                    'slug' => \Illuminate\Support\Str::slug($material),
                ],
                [
                    'value' => $material,
                    'sort_order' => $index + 1,
                    'is_enabled' => true,
                ]
            );
        }

        $this->command->info('Attributes created successfully!');
    }

    private function createCustomers(): void
    {
        $regularGroup = CustomerGroup::where('slug', 'regular-customers')->first();
        $vipGroup = CustomerGroup::where('slug', 'vip-customers')->first();

        // Create regular customers
        $regularCustomers = User::factory()->count(50)->create([
            'is_active' => true,
            'preferred_locale' => 'lt',
        ]);

        foreach ($regularCustomers as $customer) {
            $customer->assignRole('customer');
            if ($regularGroup) {
                $customer->customerGroups()->attach($regularGroup);
            }
        }

        // Create VIP customers
        $vipCustomers = User::factory()->count(10)->create([
            'is_active' => true,
            'preferred_locale' => 'lt',
            'accepts_marketing' => true,
        ]);

        foreach ($vipCustomers as $customer) {
            $customer->assignRole('customer');
            if ($vipGroup) {
                $customer->customerGroups()->attach($vipGroup);
            }
        }

        $this->command->info('Sample customers created successfully!');
    }
}

