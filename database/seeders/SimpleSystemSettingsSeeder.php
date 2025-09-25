<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\SystemSetting;
use App\Models\SystemSettingCategory;
use Illuminate\Database\Seeder;

final class SimpleSystemSettingsSeeder extends Seeder
{
    public function run(): void
    {
        // Create system setting categories using factories
        $generalCategory = SystemSettingCategory::factory()
            ->active()
            ->withColor('primary')
            ->withIcon('heroicon-o-cog-6-tooth')
            ->state([
                'name' => 'General',
                'slug' => 'general',
                'description' => 'General system settings',
                'sort_order' => 1,
            ])
            ->create();

        $ecommerceCategory = SystemSettingCategory::factory()
            ->active()
            ->withColor('success')
            ->withIcon('heroicon-o-shopping-cart')
            ->state([
                'name' => 'E-commerce',
                'slug' => 'ecommerce',
                'description' => 'E-commerce specific settings',
                'sort_order' => 2,
            ])
            ->create();

        $emailCategory = SystemSettingCategory::factory()
            ->active()
            ->withColor('info')
            ->withIcon('heroicon-o-envelope')
            ->state([
                'name' => 'Email',
                'slug' => 'email',
                'description' => 'Email configuration settings',
                'sort_order' => 3,
            ])
            ->create();

        $paymentCategory = SystemSettingCategory::factory()
            ->active()
            ->withColor('warning')
            ->withIcon('heroicon-o-credit-card')
            ->state([
                'name' => 'Payment',
                'slug' => 'payment',
                'description' => 'Payment gateway settings',
                'sort_order' => 4,
            ])
            ->create();

        $shippingCategory = SystemSettingCategory::factory()
            ->active()
            ->withColor('secondary')
            ->withIcon('heroicon-o-truck')
            ->state([
                'name' => 'Shipping',
                'slug' => 'shipping',
                'description' => 'Shipping and delivery settings',
                'sort_order' => 5,
            ])
            ->create();

        $seoCategory = SystemSettingCategory::factory()
            ->active()
            ->withColor('info')
            ->withIcon('heroicon-o-magnifying-glass')
            ->state([
                'name' => 'SEO',
                'slug' => 'seo',
                'description' => 'Search engine optimization settings',
                'sort_order' => 6,
            ])
            ->create();

        $securityCategory = SystemSettingCategory::factory()
            ->active()
            ->withColor('danger')
            ->withIcon('heroicon-o-shield-check')
            ->state([
                'name' => 'Security',
                'slug' => 'security',
                'description' => 'Security and authentication settings',
                'sort_order' => 7,
            ])
            ->create();

        $apiCategory = SystemSettingCategory::factory()
            ->active()
            ->withColor('secondary')
            ->withIcon('heroicon-o-code-bracket')
            ->state([
                'name' => 'API',
                'slug' => 'api',
                'description' => 'API configuration settings',
                'sort_order' => 8,
            ])
            ->create();

        $appearanceCategory = SystemSettingCategory::factory()
            ->active()
            ->withColor('primary')
            ->withIcon('heroicon-o-paint-brush')
            ->state([
                'name' => 'Appearance',
                'slug' => 'appearance',
                'description' => 'Theme and appearance settings',
                'sort_order' => 9,
            ])
            ->create();

        $notificationsCategory = SystemSettingCategory::factory()
            ->active()
            ->withColor('warning')
            ->withIcon('heroicon-o-bell')
            ->state([
                'name' => 'Notifications',
                'slug' => 'notifications',
                'description' => 'Notification system settings',
                'sort_order' => 10,
            ])
            ->create();

        // Create General Settings using factories
        SystemSetting::factory()
            ->inCategory($generalCategory)
            ->active()
            ->public()
            ->required()
            ->ofType('string')
            ->state([
                'key' => 'app.name',
                'name' => 'Application Name',
                'value' => 'Statybos E-commerce',
                'group' => 'general',
                'description' => 'The name of your application',
                'help_text' => 'This will be displayed in the browser title and throughout the application',
                'sort_order' => 1,
            ])
            ->create();

        SystemSetting::factory()
            ->inCategory($generalCategory)
            ->active()
            ->public()
            ->optional()
            ->ofType('text')
            ->state([
                'key' => 'app.description',
                'name' => 'Application Description',
                'value' => 'Professional construction materials e-commerce platform',
                'group' => 'general',
                'description' => 'Brief description of your application',
                'help_text' => 'Used for SEO meta descriptions and social media sharing',
                'sort_order' => 2,
            ])
            ->create();

        // Create essential settings using factories with relationships
        
        // General Settings
        SystemSetting::factory()->inCategory($generalCategory)->active()->public()->required()->ofType('select')->state([
            'key' => 'app.timezone', 'name' => 'Timezone', 'value' => 'Europe/Vilnius', 'group' => 'general',
            'description' => 'Default timezone for the application', 'sort_order' => 3,
        ])->create();

        SystemSetting::factory()->inCategory($generalCategory)->active()->public()->required()->ofType('select')->state([
            'key' => 'app.currency', 'name' => 'Default Currency', 'value' => 'EUR', 'group' => 'general',
            'description' => 'Default currency for the application', 'sort_order' => 4,
        ])->create();

        SystemSetting::factory()->inCategory($generalCategory)->active()->public()->required()->ofType('select')->state([
            'key' => 'app.language', 'name' => 'Default Language', 'value' => 'lt', 'group' => 'general',
            'description' => 'Default language for the application', 'sort_order' => 5,
        ])->create();

        // E-commerce Settings
        SystemSetting::factory()->inCategory($ecommerceCategory)->active()->public()->required()->ofType('number')->state([
            'key' => 'ecommerce.tax_rate', 'name' => 'Default Tax Rate', 'value' => '21.0', 'group' => 'ecommerce',
            'description' => 'Default VAT rate percentage', 'sort_order' => 1,
        ])->create();

        SystemSetting::factory()->inCategory($ecommerceCategory)->active()->public()->optional()->ofType('number')->state([
            'key' => 'ecommerce.min_order_amount', 'name' => 'Minimum Order Amount', 'value' => '50.0', 'group' => 'ecommerce',
            'description' => 'Minimum order amount for checkout', 'sort_order' => 2,
        ])->create();

        SystemSetting::factory()->inCategory($ecommerceCategory)->active()->public()->optional()->ofType('number')->state([
            'key' => 'ecommerce.free_shipping_threshold', 'name' => 'Free Shipping Threshold', 'value' => '100.0', 'group' => 'ecommerce',
            'description' => 'Order amount for free shipping', 'sort_order' => 3,
        ])->create();

        // Email Settings
        SystemSetting::factory()->inCategory($emailCategory)->active()->private()->required()->ofType('string')->state([
            'key' => 'mail.from_address', 'name' => 'From Email Address', 'value' => 'noreply@statybaecommerse.prus.dev', 'group' => 'email',
            'description' => 'Default sender email address', 'sort_order' => 1,
        ])->create();

        SystemSetting::factory()->inCategory($emailCategory)->active()->private()->required()->ofType('string')->state([
            'key' => 'mail.from_name', 'name' => 'From Name', 'value' => 'Statybos E-commerce', 'group' => 'email',
            'description' => 'Default sender name', 'sort_order' => 2,
        ])->create();

        // Payment Settings
        SystemSetting::factory()->inCategory($paymentCategory)->active()->public()->required()->ofType('select')->state([
            'key' => 'payment.default_method', 'name' => 'Default Payment Method', 'value' => 'bank_transfer', 'group' => 'payment',
            'description' => 'Default payment method for orders', 'sort_order' => 1,
        ])->create();

        // Shipping Settings
        SystemSetting::factory()->inCategory($shippingCategory)->active()->public()->required()->ofType('select')->state([
            'key' => 'shipping.default_method', 'name' => 'Default Shipping Method', 'value' => 'standard', 'group' => 'shipping',
            'description' => 'Default shipping method for orders', 'sort_order' => 1,
        ])->create();

        SystemSetting::factory()->inCategory($shippingCategory)->active()->public()->optional()->ofType('number')->state([
            'key' => 'shipping.default_cost', 'name' => 'Default Shipping Cost', 'value' => '5.99', 'group' => 'shipping',
            'description' => 'Default shipping cost in EUR', 'sort_order' => 2,
        ])->create();

        // SEO Settings
        SystemSetting::factory()->inCategory($seoCategory)->active()->public()->optional()->ofType('string')->state([
            'key' => 'seo.meta_title', 'name' => 'Default Meta Title', 'value' => 'Statybos E-commerce - Professional Construction Materials', 'group' => 'seo',
            'description' => 'Default meta title for pages', 'sort_order' => 1,
        ])->create();

        SystemSetting::factory()->inCategory($seoCategory)->active()->public()->optional()->ofType('text')->state([
            'key' => 'seo.meta_description', 'name' => 'Default Meta Description', 'value' => 'Professional construction materials and tools for your building projects. Quality products, competitive prices, fast delivery.', 'group' => 'seo',
            'description' => 'Default meta description for pages', 'sort_order' => 2,
        ])->create();

        // Security Settings
        SystemSetting::factory()->inCategory($securityCategory)->active()->private()->required()->ofType('number')->state([
            'key' => 'security.password_min_length', 'name' => 'Minimum Password Length', 'value' => '8', 'group' => 'security',
            'description' => 'Minimum password length requirement', 'sort_order' => 1,
        ])->create();

        SystemSetting::factory()->inCategory($securityCategory)->active()->private()->optional()->ofType('number')->state([
            'key' => 'security.session_timeout', 'name' => 'Session Timeout (minutes)', 'value' => '120', 'group' => 'security',
            'description' => 'User session timeout in minutes', 'sort_order' => 3,
        ])->create();

        // API Settings
        SystemSetting::factory()->inCategory($apiCategory)->active()->private()->optional()->ofType('number')->state([
            'key' => 'api.rate_limit', 'name' => 'API Rate Limit', 'value' => '1000', 'group' => 'api',
            'description' => 'API requests per hour limit', 'sort_order' => 1,
        ])->create();

        // Appearance Settings
        SystemSetting::factory()->inCategory($appearanceCategory)->active()->public()->optional()->ofType('color')->state([
            'key' => 'appearance.primary_color', 'name' => 'Primary Color', 'value' => '#3B82F6', 'group' => 'appearance',
            'description' => 'Primary brand color', 'sort_order' => 1,
        ])->create();

        SystemSetting::factory()->inCategory($appearanceCategory)->active()->public()->optional()->ofType('color')->state([
            'key' => 'appearance.secondary_color', 'name' => 'Secondary Color', 'value' => '#6B7280', 'group' => 'appearance',
            'description' => 'Secondary brand color', 'sort_order' => 2,
        ])->create();

        // Notification Settings
        SystemSetting::factory()->inCategory($notificationsCategory)->active()->private()->optional()->ofType('boolean')->state([
            'key' => 'notifications.email_notifications', 'name' => 'Enable Email Notifications', 'value' => json_encode(true), 'group' => 'notifications',
            'description' => 'Enable email notifications', 'sort_order' => 1,
        ])->create();

        $this->command->info("System settings seeder completed successfully!");
        $this->command->info('Created ' . SystemSettingCategory::count() . ' categories and ' . SystemSetting::count() . ' settings.');
    }
}
