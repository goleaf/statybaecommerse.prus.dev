<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\SystemSetting;
use App\Models\SystemSettingCategory;
use App\Models\User;
use Illuminate\Database\Seeder;

final class SystemSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure admin user exists using factory
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            User::factory()->make(['name' => 'Admin User'])->toArray()
        );

        // Ensure categories exist using factory relationships
        $generalCategory = SystemSettingCategory::firstOrCreate(
            ['slug' => 'general'],
            SystemSettingCategory::factory()->make([
                'name' => 'General',
                'description' => 'General system settings',
                'icon' => 'heroicon-o-cog-6-tooth',
                'color' => 'primary',
                'sort_order' => 1,
            ])->toArray()
        );

        $securityCategory = SystemSettingCategory::firstOrCreate(
            ['slug' => 'security'],
            SystemSettingCategory::factory()->make([
                'name' => 'Security',
                'description' => 'Security and authentication settings',
                'icon' => 'heroicon-o-shield-check',
                'color' => 'danger',
                'sort_order' => 2,
            ])->toArray()
        );

        $performanceCategory = SystemSettingCategory::firstOrCreate(
            ['slug' => 'performance'],
            SystemSettingCategory::factory()->make([
                'name' => 'Performance',
                'description' => 'Performance optimization settings',
                'icon' => 'heroicon-o-bolt',
                'color' => 'success',
                'sort_order' => 3,
            ])->toArray()
        );

        $uiCategory = SystemSettingCategory::firstOrCreate(
            ['slug' => 'ui-ux'],
            SystemSettingCategory::factory()->make([
                'name' => 'UI/UX',
                'description' => 'User interface and experience settings',
                'icon' => 'heroicon-o-paint-brush',
                'color' => 'info',
                'sort_order' => 4,
            ])->toArray()
        );

        $apiCategory = SystemSettingCategory::firstOrCreate(
            ['slug' => 'api'],
            SystemSettingCategory::factory()->make([
                'name' => 'API',
                'description' => 'API configuration settings',
                'icon' => 'heroicon-o-code-bracket',
                'color' => 'secondary',
                'sort_order' => 5,
            ])->toArray()
        );

        // Create settings using factory with relationships
        $settingsData = [
            // General Settings
            [
                'category' => $generalCategory,
                'key' => 'app_name',
                'name' => 'Application Name',
                'description' => 'The name of the application',
                'help_text' => 'This name will be displayed in the browser title and throughout the application',
                'type' => 'string',
                'value' => 'E-commerce Platform',
                'group' => 'general',
                'sort_order' => 1,
                'is_active' => true,
                'is_public' => true,
                'is_required' => true,
                'is_readonly' => false,
                'is_encrypted' => false,
                'is_cacheable' => true,
                'default_value' => 'E-commerce Platform',
            ],
            [
                'category' => $generalCategory,
                'key' => 'app_description',
                'name' => 'Application Description',
                'description' => 'Description of the application',
                'help_text' => 'This description will be used in meta tags and SEO',
                'type' => 'text',
                'value' => 'A modern e-commerce platform built with Laravel and Filament',
                'group' => 'general',
                'sort_order' => 2,
                'is_active' => true,
                'is_public' => true,
                'is_required' => false,
                'default_value' => 'A modern e-commerce platform',
            ],
            [
                'category' => $generalCategory,
                'key' => 'app_version',
                'name' => 'Application Version',
                'description' => 'Current version of the application',
                'help_text' => 'Used for version tracking and updates',
                'type' => 'string',
                'value' => '1.0.0',
                'group' => 'general',
                'sort_order' => 3,
                'is_active' => true,
                'is_public' => true,
                'is_required' => true,
                'default_value' => '1.0.0',
            ],
            [
                'category' => $generalCategory,
                'key' => 'maintenance_mode',
                'name' => 'Maintenance Mode',
                'description' => 'Enable or disable maintenance mode',
                'help_text' => 'When enabled, the application will show a maintenance page',
                'type' => 'boolean',
                'value' => false,
                'group' => 'general',
                'sort_order' => 4,
                'is_active' => true,
                'is_public' => false,
                'is_required' => false,
                'default_value' => false,
            ],

            // Security Settings
            [
                'category' => $securityCategory,
                'key' => 'password_min_length',
                'name' => 'Minimum Password Length',
                'description' => 'Minimum length for user passwords',
                'help_text' => 'Users must create passwords with at least this many characters',
                'type' => 'integer',
                'value' => 8,
                'group' => 'security',
                'sort_order' => 1,
                'is_active' => true,
                'is_public' => false,
                'is_required' => true,
                'default_value' => 8,
            ],
            [
                'category' => $securityCategory,
                'key' => 'max_login_attempts',
                'name' => 'Maximum Login Attempts',
                'description' => 'Maximum number of failed login attempts before account lockout',
                'help_text' => 'After this many failed attempts, the account will be temporarily locked',
                'type' => 'integer',
                'value' => 5,
                'group' => 'security',
                'sort_order' => 2,
                'is_active' => true,
                'is_public' => false,
                'is_required' => true,
                'default_value' => 5,
            ],
            [
                'category' => $securityCategory,
                'key' => 'session_timeout',
                'name' => 'Session Timeout (minutes)',
                'description' => 'How long user sessions remain active',
                'help_text' => 'Users will be logged out after this many minutes of inactivity',
                'type' => 'integer',
                'value' => 120,
                'group' => 'security',
                'sort_order' => 3,
                'is_active' => true,
                'is_public' => false,
                'is_required' => true,
                'default_value' => 120,
            ],
            [
                'category' => $securityCategory,
                'key' => 'enable_two_factor',
                'name' => 'Enable Two-Factor Authentication',
                'description' => 'Enable two-factor authentication for enhanced security',
                'help_text' => 'Users will be required to set up 2FA for their accounts',
                'type' => 'boolean',
                'value' => false,
                'group' => 'security',
                'sort_order' => 4,
                'is_active' => true,
                'is_public' => false,
                'is_required' => false,
                'default_value' => false,
            ],

            // Performance Settings
            [
                'category' => $performanceCategory,
                'key' => 'cache_driver',
                'name' => 'Cache Driver',
                'description' => 'The cache driver to use for application caching',
                'help_text' => 'Choose between file, redis, memcached, or other cache drivers',
                'type' => 'select',
                'value' => 'file',
                'group' => 'performance',
                'sort_order' => 1,
                'is_active' => true,
                'is_public' => false,
                'is_required' => true,
                'options' => json_encode([
                    'file' => 'File',
                    'redis' => 'Redis',
                    'memcached' => 'Memcached',
                    'database' => 'Database',
                ]),
                'default_value' => 'file',
            ],
            [
                'category' => $performanceCategory,
                'key' => 'cache_ttl',
                'name' => 'Default Cache TTL (seconds)',
                'description' => 'Default time-to-live for cached items',
                'help_text' => 'How long cached items should remain in cache by default',
                'type' => 'integer',
                'value' => 3600,
                'group' => 'performance',
                'sort_order' => 2,
                'is_active' => true,
                'is_public' => false,
                'is_required' => true,
                'default_value' => 3600,
            ],
            [
                'category' => $performanceCategory,
                'key' => 'enable_query_cache',
                'name' => 'Enable Query Cache',
                'description' => 'Enable caching of database queries',
                'help_text' => 'Caching queries can improve performance but may use more memory',
                'type' => 'boolean',
                'value' => true,
                'group' => 'performance',
                'sort_order' => 3,
                'is_active' => true,
                'is_public' => false,
                'is_required' => false,
                'default_value' => true,
            ],

            // UI/UX Settings
            [
                'category' => $uiCategory,
                'key' => 'theme_color',
                'name' => 'Theme Color',
                'description' => 'Primary color theme for the application',
                'help_text' => 'This color will be used throughout the application interface',
                'type' => 'color',
                'value' => '#3B82F6',
                'group' => 'ui_ux',
                'sort_order' => 1,
                'is_active' => true,
                'is_public' => true,
                'is_required' => false,
                'default_value' => '#3B82F6',
            ],
            [
                'category' => $uiCategory,
                'key' => 'items_per_page',
                'name' => 'Items Per Page',
                'description' => 'Default number of items to display per page',
                'help_text' => 'This affects pagination throughout the application',
                'type' => 'integer',
                'value' => 15,
                'group' => 'ui_ux',
                'sort_order' => 2,
                'is_active' => true,
                'is_public' => false,
                'is_required' => true,
                'default_value' => 15,
            ],
            [
                'category' => $uiCategory,
                'key' => 'enable_dark_mode',
                'name' => 'Enable Dark Mode',
                'description' => 'Allow users to switch to dark mode',
                'help_text' => 'Users will see a toggle to switch between light and dark themes',
                'type' => 'boolean',
                'value' => true,
                'group' => 'ui_ux',
                'sort_order' => 3,
                'is_active' => true,
                'is_public' => true,
                'is_required' => false,
                'default_value' => true,
            ],

            // API Settings
            [
                'category' => $apiCategory,
                'key' => 'api_rate_limit',
                'name' => 'API Rate Limit (requests per minute)',
                'description' => 'Maximum number of API requests per minute per user',
                'help_text' => 'This helps prevent abuse and ensures fair usage of the API',
                'type' => 'integer',
                'value' => 60,
                'group' => 'api',
                'sort_order' => 1,
                'is_active' => true,
                'is_public' => false,
                'is_required' => true,
                'default_value' => 60,
            ],
            [
                'category' => $apiCategory,
                'key' => 'api_version',
                'name' => 'API Version',
                'description' => 'Current version of the API',
                'help_text' => 'This is used for API versioning and backward compatibility',
                'type' => 'string',
                'value' => 'v1',
                'group' => 'api',
                'sort_order' => 2,
                'is_active' => true,
                'is_public' => true,
                'is_required' => true,
                'default_value' => 'v1',
            ],
            [
                'category' => $apiCategory,
                'key' => 'enable_api_docs',
                'name' => 'Enable API Documentation',
                'description' => 'Make API documentation publicly accessible',
                'help_text' => 'When enabled, API documentation will be available at /api/docs',
                'type' => 'boolean',
                'value' => true,
                'group' => 'api',
                'sort_order' => 3,
                'is_active' => true,
                'is_public' => true,
                'is_required' => false,
                'default_value' => true,
            ],
        ];

        // Create settings using factory with relationships
        foreach ($settingsData as $settingData) {
            $category = $settingData['category'];
            unset($settingData['category']);
            
            SystemSetting::firstOrCreate(
                ['key' => $settingData['key']],
                SystemSetting::factory()
                    ->for($category, 'category')
                    ->for($adminUser, 'updatedBy')
                    ->make($settingData)
                    ->toArray()
            );
        }
    }
}
