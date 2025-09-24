<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * SuperAdminSeeder
 *
 * Creates the super admin user with maximum permissions for admin@example.com
 */
final class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🔐 Creating Super Admin User...');

        // Create or get the super admin role
        $superAdminRole = Role::firstOrCreate([
            'name' => 'super-admin',
            'guard_name' => 'web',
        ]);

        // Create all possible permissions
        $permissions = $this->createAllPermissions();

        // Assign all permissions to super admin role
        $superAdminRole->syncPermissions($permissions);

        // Create or update the super admin user
        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Super Administrator',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'is_admin' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Assign super admin role to user
        $admin->assignRole($superAdminRole);

        $this->command->info('✅ Super Admin created successfully!');
        $this->command->info('📧 Email: admin@example.com');
        $this->command->info('🔑 Password: password');
        $this->command->info('🎭 Role: super-admin with '.count($permissions).' permissions');
    }

    private function createAllPermissions(): array
    {
        $permissions = [];

        // Core CRUD permissions for all models
        $models = [
            'products', 'categories', 'brands', 'orders', 'users', 'customers',
            'inventory', 'variants', 'reviews', 'discounts', 'campaigns',
            'analytics', 'reports', 'settings', 'system_settings', 'notifications',
            'documents', 'legal', 'news', 'news_categories', 'news_tags',
            'sliders', 'menus', 'menu_items', 'locations', 'countries', 'cities',
            'currencies', 'price_lists', 'partners', 'referrals', 'wishlists',
            'cart_items', 'addresses', 'shipping_options', 'payment_methods',
            'coupons', 'discount_codes', 'seo_data', 'translations', 'enum_values',
            'attribute_values', 'collections', 'product_features', 'stock_movements',
            'variant_analytics', 'recommendations', 'user_interactions',
            'activity_logs', 'feature_flags', 'email_campaigns', 'subscribers',
        ];

        $actions = ['view', 'view_any', 'create', 'update', 'edit', 'delete', 'delete_any', 'restore', 'restore_any', 'force_delete', 'force_delete_any'];

        foreach ($models as $model) {
            foreach ($actions as $action) {
                $permissionName = "{$action}_{$model}";
                $permissions[] = Permission::firstOrCreate([
                    'name' => $permissionName,
                    'guard_name' => 'web',
                ]);
            }
        }

        // Special admin permissions
        $adminPermissions = [
            'access_admin_panel',
            'manage_all_users',
            'manage_all_orders',
            'manage_all_products',
            'manage_all_settings',
            'manage_system_settings',
            'view_analytics',
            'manage_roles_permissions',
            'manage_file_uploads',
            'export_data',
            'import_data',
            'manage_backups',
            'view_system_logs',
            'manage_email_templates',
            'manage_notifications',
            'manage_seo_settings',
            'manage_translations',
            'manage_enum_values',
            'manage_feature_flags',
            'manage_referral_system',
            'manage_discount_system',
            'manage_campaign_system',
            'manage_inventory_system',
            'manage_recommendation_system',
            'manage_analytics_system',
            'manage_reporting_system',
            'super_admin_access',
            'bypass_all_restrictions',
            'manage_database',
            'manage_migrations',
            'manage_seeders',
            'manage_cache',
            'manage_queue',
            'manage_jobs',
            'manage_events',
            'manage_listeners',
            'manage_middleware',
            'manage_routes',
            'manage_controllers',
            'manage_models',
            'manage_services',
            'manage_repositories',
            'manage_observers',
            'manage_factories',
            'manage_tests',
        ];

        foreach ($adminPermissions as $permission) {
            $permissions[] = Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        // Filament-specific permissions
        $filamentPermissions = [
            'view_filament_dashboard',
            'access_filament_admin',
            'manage_filament_resources',
            'view_filament_widgets',
            'manage_filament_relations',
            'export_filament_data',
            'import_filament_data',
            'manage_filament_settings',
        ];

        foreach ($filamentPermissions as $permission) {
            $permissions[] = Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        return $permissions;
    }
}
