<?php declare(strict_types=1);

namespace Database\Seeders;

use App\Models\SystemSetting;
use App\Models\SystemSettingCategory;
use Illuminate\Database\Seeder;

final class SystemSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $generalCategory = SystemSettingCategory::where('slug', 'general')->first();
        $securityCategory = SystemSettingCategory::where('slug', 'security')->first();
        $emailCategory = SystemSettingCategory::where('slug', 'email')->first();
        $databaseCategory = SystemSettingCategory::where('slug', 'database')->first();
        $cacheCategory = SystemSettingCategory::where('slug', 'cache')->first();
        $apiCategory = SystemSettingCategory::where('slug', 'api')->first();
        $paymentCategory = SystemSettingCategory::where('slug', 'payment')->first();
        $notificationsCategory = SystemSettingCategory::where('slug', 'notifications')->first();
        $storageCategory = SystemSettingCategory::where('slug', 'storage')->first();
        $analyticsCategory = SystemSettingCategory::where('slug', 'analytics')->first();

        $settings = [
            // General Settings
            [
                'key' => 'app_name',
                'name' => 'Application Name',
                'description' => 'The name of the application',
                'type' => 'string',
                'category_id' => $generalCategory->id,
                'group' => 'general',
                'value' => 'E-commerce Platform',
                'default_value' => 'E-commerce Platform',
                'is_public' => true,
                'is_required' => true,
                'is_encrypted' => false,
                'is_readonly' => false,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'key' => 'app_url',
                'name' => 'Application URL',
                'description' => 'The base URL of the application',
                'type' => 'string',
                'category_id' => $generalCategory->id,
                'group' => 'general',
                'value' => 'https://example.com',
                'default_value' => 'https://example.com',
                'is_public' => true,
                'is_required' => true,
                'is_encrypted' => false,
                'is_readonly' => false,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'key' => 'app_timezone',
                'name' => 'Application Timezone',
                'description' => 'The timezone for the application',
                'type' => 'string',
                'category_id' => $generalCategory->id,
                'group' => 'general',
                'value' => 'Europe/Vilnius',
                'default_value' => 'Europe/Vilnius',
                'is_public' => true,
                'is_required' => true,
                'is_encrypted' => false,
                'is_readonly' => false,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'key' => 'app_locale',
                'name' => 'Application Locale',
                'description' => 'The default locale for the application',
                'type' => 'string',
                'category_id' => $generalCategory->id,
                'group' => 'general',
                'value' => 'lt',
                'default_value' => 'lt',
                'is_public' => true,
                'is_required' => true,
                'is_encrypted' => false,
                'is_readonly' => false,
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'key' => 'app_currency',
                'name' => 'Application Currency',
                'description' => 'The default currency for the application',
                'type' => 'string',
                'category_id' => $generalCategory->id,
                'group' => 'general',
                'value' => 'EUR',
                'default_value' => 'EUR',
                'is_public' => true,
                'is_required' => true,
                'is_encrypted' => false,
                'is_readonly' => false,
                'is_active' => true,
                'sort_order' => 5,
            ],
            // Security Settings
            [
                'key' => 'password_min_length',
                'name' => 'Minimum Password Length',
                'description' => 'Minimum length for user passwords',
                'type' => 'integer',
                'category_id' => $securityCategory->id,
                'group' => 'security',
                'value' => '8',
                'default_value' => '8',
                'is_public' => false,
                'is_required' => true,
                'is_encrypted' => false,
                'is_readonly' => false,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'key' => 'session_timeout',
                'name' => 'Session Timeout',
                'description' => 'Session timeout in minutes',
                'type' => 'integer',
                'category_id' => $securityCategory->id,
                'group' => 'security',
                'value' => '120',
                'default_value' => '120',
                'is_public' => false,
                'is_required' => true,
                'is_encrypted' => false,
                'is_readonly' => false,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'key' => 'max_login_attempts',
                'name' => 'Maximum Login Attempts',
                'description' => 'Maximum number of failed login attempts before lockout',
                'type' => 'integer',
                'category_id' => $securityCategory->id,
                'group' => 'security',
                'value' => '5',
                'default_value' => '5',
                'is_public' => false,
                'is_required' => true,
                'is_encrypted' => false,
                'is_readonly' => false,
                'is_active' => true,
                'sort_order' => 3,
            ],
            // Email Settings
            [
                'key' => 'mail_from_address',
                'name' => 'Mail From Address',
                'description' => 'Default email address for outgoing emails',
                'type' => 'string',
                'category_id' => $emailCategory->id,
                'group' => 'email',
                'value' => 'noreply@example.com',
                'default_value' => 'noreply@example.com',
                'is_public' => false,
                'is_required' => true,
                'is_encrypted' => false,
                'is_readonly' => false,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'key' => 'mail_from_name',
                'name' => 'Mail From Name',
                'description' => 'Default name for outgoing emails',
                'type' => 'string',
                'category_id' => $emailCategory->id,
                'group' => 'email',
                'value' => 'E-commerce Platform',
                'default_value' => 'E-commerce Platform',
                'is_public' => false,
                'is_required' => true,
                'is_encrypted' => false,
                'is_readonly' => false,
                'is_active' => true,
                'sort_order' => 2,
            ],
            // Database Settings
            [
                'key' => 'db_connection_timeout',
                'name' => 'Database Connection Timeout',
                'description' => 'Database connection timeout in seconds',
                'type' => 'integer',
                'category_id' => $databaseCategory->id,
                'group' => 'database',
                'value' => '30',
                'default_value' => '30',
                'is_public' => false,
                'is_required' => true,
                'is_encrypted' => false,
                'is_readonly' => false,
                'is_active' => true,
                'sort_order' => 1,
            ],
            // Cache Settings
            [
                'key' => 'cache_default_ttl',
                'name' => 'Default Cache TTL',
                'description' => 'Default cache time to live in seconds',
                'type' => 'integer',
                'category_id' => $cacheCategory->id,
                'group' => 'cache',
                'value' => '3600',
                'default_value' => '3600',
                'is_public' => false,
                'is_required' => true,
                'is_encrypted' => false,
                'is_readonly' => false,
                'is_active' => true,
                'sort_order' => 1,
            ],
            // API Settings
            [
                'key' => 'api_rate_limit',
                'name' => 'API Rate Limit',
                'description' => 'API rate limit per minute',
                'type' => 'integer',
                'category_id' => $apiCategory->id,
                'group' => 'api',
                'value' => '60',
                'default_value' => '60',
                'is_public' => false,
                'is_required' => true,
                'is_encrypted' => false,
                'is_readonly' => false,
                'is_active' => true,
                'sort_order' => 1,
            ],
            // Payment Settings
            [
                'key' => 'payment_currency',
                'name' => 'Payment Currency',
                'description' => 'Default currency for payments',
                'type' => 'string',
                'category_id' => $paymentCategory->id,
                'group' => 'payment',
                'value' => 'EUR',
                'default_value' => 'EUR',
                'is_public' => true,
                'is_required' => true,
                'is_encrypted' => false,
                'is_readonly' => false,
                'is_active' => true,
                'sort_order' => 1,
            ],
            // Notification Settings
            [
                'key' => 'notification_enabled',
                'name' => 'Notifications Enabled',
                'description' => 'Whether notifications are enabled',
                'type' => 'boolean',
                'category_id' => $notificationsCategory->id,
                'group' => 'notifications',
                'value' => 'true',
                'default_value' => 'true',
                'is_public' => false,
                'is_required' => true,
                'is_encrypted' => false,
                'is_readonly' => false,
                'is_active' => true,
                'sort_order' => 1,
            ],
            // Storage Settings
            [
                'key' => 'storage_disk',
                'name' => 'Storage Disk',
                'description' => 'Default storage disk for files',
                'type' => 'string',
                'category_id' => $storageCategory->id,
                'group' => 'storage',
                'value' => 'local',
                'default_value' => 'local',
                'is_public' => false,
                'is_required' => true,
                'is_encrypted' => false,
                'is_readonly' => false,
                'is_active' => true,
                'sort_order' => 1,
            ],
            // Analytics Settings
            [
                'key' => 'analytics_enabled',
                'name' => 'Analytics Enabled',
                'description' => 'Whether analytics tracking is enabled',
                'type' => 'boolean',
                'category_id' => $analyticsCategory->id,
                'group' => 'analytics',
                'value' => 'true',
                'default_value' => 'true',
                'is_public' => false,
                'is_required' => true,
                'is_encrypted' => false,
                'is_readonly' => false,
                'is_active' => true,
                'sort_order' => 1,
            ],
        ];

        foreach ($settings as $setting) {
            SystemSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
