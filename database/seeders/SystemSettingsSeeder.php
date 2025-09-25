<?php declare(strict_types=1);

namespace Database\Seeders;

use App\Models\SystemSetting;
use App\Models\SystemSettingCategory;
use App\Models\SystemSettingCategoryTranslation;
use App\Models\SystemSettingTranslation;
use Illuminate\Database\Seeder;

final class SystemSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $this->createCategories();
        $this->createGeneralSettings();
        $this->createEcommerceSettings();
        $this->createEmailSettings();
        $this->createPaymentSettings();
        $this->createShippingSettings();
        $this->createSeoSettings();
        $this->createSecuritySettings();
        $this->createApiSettings();
        $this->createAppearanceSettings();
        $this->createNotificationSettings();
    }

    private function createCategories(): void
    {
        $categories = [
            [
                'name' => 'General',
                'slug' => 'general',
                'description' => 'General system settings',
                'icon' => 'heroicon-o-cog-6-tooth',
                'color' => 'primary',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'E-commerce',
                'slug' => 'ecommerce',
                'description' => 'E-commerce specific settings',
                'icon' => 'heroicon-o-shopping-cart',
                'color' => 'success',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Email',
                'slug' => 'email',
                'description' => 'Email configuration settings',
                'icon' => 'heroicon-o-envelope',
                'color' => 'info',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Payment',
                'slug' => 'payment',
                'description' => 'Payment gateway settings',
                'icon' => 'heroicon-o-credit-card',
                'color' => 'warning',
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'Shipping',
                'slug' => 'shipping',
                'description' => 'Shipping and delivery settings',
                'icon' => 'heroicon-o-truck',
                'color' => 'secondary',
                'sort_order' => 5,
                'is_active' => true,
            ],
            [
                'name' => 'SEO',
                'slug' => 'seo',
                'description' => 'Search engine optimization settings',
                'icon' => 'heroicon-o-magnifying-glass',
                'color' => 'info',
                'sort_order' => 6,
                'is_active' => true,
            ],
            [
                'name' => 'Security',
                'slug' => 'security',
                'description' => 'Security and authentication settings',
                'icon' => 'heroicon-o-shield-check',
                'color' => 'danger',
                'sort_order' => 7,
                'is_active' => true,
            ],
            [
                'name' => 'API',
                'slug' => 'api',
                'description' => 'API configuration settings',
                'icon' => 'heroicon-o-code-bracket',
                'color' => 'secondary',
                'sort_order' => 8,
                'is_active' => true,
            ],
            [
                'name' => 'Appearance',
                'slug' => 'appearance',
                'description' => 'Theme and appearance settings',
                'icon' => 'heroicon-o-paint-brush',
                'color' => 'primary',
                'sort_order' => 9,
                'is_active' => true,
            ],
            [
                'name' => 'Notifications',
                'slug' => 'notifications',
                'description' => 'Notification system settings',
                'icon' => 'heroicon-o-bell',
                'color' => 'warning',
                'sort_order' => 10,
                'is_active' => true,
            ],
        ];

        foreach ($categories as $categoryData) {
            $category = SystemSettingCategory::firstOrCreate(
                ['slug' => $categoryData['slug']],
                $categoryData
            );

            // Create translations directly to avoid factory uniqueness constraints
            SystemSettingCategoryTranslation::firstOrCreate(
                [
                    'system_setting_category_id' => $category->id,
                    'locale' => 'lt',
                ],
                [
                    'name' => $this->getLithuanianTranslation($categoryData['name']),
                    'description' => $this->getLithuanianTranslation($categoryData['description']),
                ]
            );
        }
    }

    private function createGeneralSettings(): void
    {
        $generalCategory = SystemSettingCategory::where('slug', 'general')->first();

        if (!$generalCategory) {
            $this->command->warn('General category not found, skipping general settings');
            return;
        }

        $settings = [
            [
                'category_id' => $generalCategory->id,
                'key' => 'app.name',
                'name' => 'Application Name',
                'value' => 'Statybos E-commerce',
                'type' => 'string',
                'group' => 'general',
                'description' => 'The name of your application',
                'help_text' => 'This will be displayed in the browser title and throughout the application',
                'is_public' => true,
                'is_required' => true,
                'sort_order' => 1,
            ],
            [
                'category_id' => $generalCategory->id,
                'key' => 'app.description',
                'name' => 'Application Description',
                'value' => 'Professional construction materials e-commerce platform',
                'type' => 'text',
                'group' => 'general',
                'description' => 'Brief description of your application',
                'help_text' => 'Used for SEO meta descriptions and social media sharing',
                'is_public' => true,
                'sort_order' => 2,
            ],
            [
                'category_id' => $generalCategory->id,
                'key' => 'app.timezone',
                'name' => 'Timezone',
                'value' => 'Europe/Vilnius',
                'type' => 'select',
                'group' => 'general',
                'description' => 'Default timezone for the application',
                'help_text' => 'All dates and times will be displayed in this timezone',
                'is_public' => false,
                'is_required' => true,
                'options' => [
                    'Europe/Vilnius' => 'Vilnius (GMT+2)',
                    'Europe/London' => 'London (GMT+0)',
                    'Europe/Berlin' => 'Berlin (GMT+1)',
                    'America/New_York' => 'New York (GMT-5)',
                ],
                'sort_order' => 3,
            ],
            [
                'category_id' => $generalCategory->id,
                'key' => 'app.currency',
                'name' => 'Default Currency',
                'value' => 'EUR',
                'type' => 'select',
                'group' => 'general',
                'description' => 'Default currency for the application',
                'help_text' => 'All prices will be displayed in this currency by default',
                'is_public' => true,
                'is_required' => true,
                'options' => [
                    'EUR' => 'Euro (€)',
                    'USD' => 'US Dollar ($)',
                    'GBP' => 'British Pound (£)',
                    'PLN' => 'Polish Zloty (zł)',
                ],
                'sort_order' => 4,
            ],
            [
                'category_id' => $generalCategory->id,
                'key' => 'app.language',
                'name' => 'Default Language',
                'value' => 'lt',
                'type' => 'select',
                'group' => 'general',
                'description' => 'Default language for the application',
                'help_text' => 'This will be the default language for new users',
                'is_public' => true,
                'is_required' => true,
                'options' => [
                    'lt' => 'Lietuvių',
                    'en' => 'English',
                    'de' => 'Deutsch',
                ],
                'sort_order' => 5,
            ],
            [
                'category_id' => $generalCategory->id,
                'key' => 'app.maintenance_mode',
                'name' => 'Maintenance Mode',
                'value' => false,
                'type' => 'boolean',
                'group' => 'general',
                'description' => 'Enable maintenance mode',
                'help_text' => 'When enabled, only administrators can access the site',
                'is_public' => false,
                'sort_order' => 6,
            ],
            [
                'category_id' => $generalCategory->id,
                'key' => 'app.debug_mode',
                'name' => 'Debug Mode',
                'value' => false,
                'type' => 'boolean',
                'group' => 'general',
                'description' => 'Enable debug mode',
                'help_text' => 'Shows detailed error information (disable in production)',
                'is_public' => false,
                'sort_order' => 7,
            ],
        ];

        $this->createSettingsWithTranslations($settings);
    }

    private function createEcommerceSettings(): void
    {
        $ecommerceCategory = SystemSettingCategory::where('slug', 'ecommerce')->first();

        if (!$ecommerceCategory) {
            $this->command->warn('E-commerce category not found, skipping e-commerce settings');
            return;
        }

        $settings = [
            [
                'category_id' => $ecommerceCategory->id,
                'key' => 'ecommerce.tax_rate',
                'name' => 'Default Tax Rate',
                'value' => 21.0,
                'type' => 'number',
                'group' => 'ecommerce',
                'description' => 'Default VAT rate percentage',
                'help_text' => 'This rate will be applied to products without specific tax rates',
                'is_public' => true,
                'is_required' => true,
                'sort_order' => 1,
            ],
            [
                'category_id' => $ecommerceCategory->id,
                'key' => 'ecommerce.min_order_amount',
                'name' => 'Minimum Order Amount',
                'value' => 50.0,
                'type' => 'number',
                'group' => 'ecommerce',
                'description' => 'Minimum order amount for checkout',
                'help_text' => 'Customers must reach this amount to complete their order',
                'is_public' => true,
                'sort_order' => 2,
            ],
            [
                'category_id' => $ecommerceCategory->id,
                'key' => 'ecommerce.free_shipping_threshold',
                'name' => 'Free Shipping Threshold',
                'value' => 100.0,
                'type' => 'number',
                'group' => 'ecommerce',
                'description' => 'Order amount for free shipping',
                'help_text' => 'Orders above this amount will have free shipping',
                'is_public' => true,
                'sort_order' => 3,
            ],
            [
                'category_id' => $ecommerceCategory->id,
                'key' => 'ecommerce.inventory_tracking',
                'name' => 'Inventory Tracking',
                'value' => true,
                'type' => 'boolean',
                'group' => 'ecommerce',
                'description' => 'Enable inventory tracking',
                'help_text' => 'Track product stock levels and prevent overselling',
                'is_public' => false,
                'sort_order' => 4,
            ],
            [
                'category_id' => $ecommerceCategory->id,
                'key' => 'ecommerce.low_stock_threshold',
                'name' => 'Low Stock Threshold',
                'value' => 10,
                'type' => 'number',
                'group' => 'ecommerce',
                'description' => 'Stock level to trigger low stock alerts',
                'help_text' => 'Products with stock below this number will trigger alerts',
                'is_public' => false,
                'sort_order' => 5,
            ],
            [
                'category_id' => $ecommerceCategory->id,
                'key' => 'ecommerce.allow_guest_checkout',
                'name' => 'Allow Guest Checkout',
                'value' => true,
                'type' => 'boolean',
                'group' => 'ecommerce',
                'description' => 'Allow customers to checkout without registration',
                'help_text' => 'When disabled, customers must create an account to purchase',
                'is_public' => false,
                'sort_order' => 6,
            ],
            [
                'category_id' => $ecommerceCategory->id,
                'key' => 'ecommerce.product_reviews',
                'name' => 'Enable Product Reviews',
                'value' => true,
                'type' => 'boolean',
                'group' => 'ecommerce',
                'description' => 'Allow customers to review products',
                'help_text' => 'Enable or disable the product review system',
                'is_public' => false,
                'sort_order' => 7,
            ],
            [
                'category_id' => $ecommerceCategory->id,
                'key' => 'ecommerce.wishlist_enabled',
                'name' => 'Enable Wishlist',
                'value' => true,
                'type' => 'boolean',
                'group' => 'ecommerce',
                'description' => 'Enable customer wishlist functionality',
                'help_text' => 'Allow customers to save products to their wishlist',
                'is_public' => false,
                'sort_order' => 8,
            ],
        ];

        $this->createSettingsWithTranslations($settings);
    }

    private function createEmailSettings(): void
    {
        $emailCategory = SystemSettingCategory::where('slug', 'email')->first();

        if (!$emailCategory) {
            $this->command->warn('Email category not found, skipping email settings');
            return;
        }

        $settings = [
            [
                'category_id' => $emailCategory->id,
                'key' => 'mail.from_address',
                'name' => 'From Email Address',
                'value' => 'noreply@statybaecommerse.prus.dev',
                'type' => 'string',
                'group' => 'email',
                'description' => 'Default sender email address',
                'help_text' => 'This email will be used as the sender for all system emails',
                'is_public' => false,
                'is_required' => true,
                'sort_order' => 1,
            ],
            [
                'category_id' => $emailCategory->id,
                'key' => 'mail.from_name',
                'name' => 'From Name',
                'value' => 'Statybos E-commerce',
                'type' => 'string',
                'group' => 'email',
                'description' => 'Default sender name',
                'help_text' => 'This name will be displayed as the sender for all system emails',
                'is_public' => false,
                'is_required' => true,
                'sort_order' => 2,
            ],
            [
                'category_id' => $emailCategory->id,
                'key' => 'mail.support_email',
                'name' => 'Support Email',
                'value' => 'support@statybaecommerse.prus.dev',
                'type' => 'string',
                'group' => 'email',
                'description' => 'Customer support email address',
                'help_text' => 'This email will be used for customer support inquiries',
                'is_public' => true,
                'is_required' => true,
                'sort_order' => 3,
            ],
            [
                'category_id' => $emailCategory->id,
                'key' => 'mail.admin_email',
                'name' => 'Admin Email',
                'value' => 'admin@statybaecommerse.prus.dev',
                'type' => 'string',
                'group' => 'email',
                'description' => 'Administrator email address',
                'help_text' => 'This email will receive system notifications and alerts',
                'is_public' => false,
                'is_required' => true,
                'sort_order' => 4,
            ],
            [
                'category_id' => $emailCategory->id,
                'key' => 'mail.queue_emails',
                'name' => 'Queue Emails',
                'value' => true,
                'type' => 'boolean',
                'group' => 'email',
                'description' => 'Queue emails for background processing',
                'help_text' => 'When enabled, emails will be queued for better performance',
                'is_public' => false,
                'sort_order' => 5,
            ],
            [
                'category_id' => $emailCategory->id,
                'key' => 'mail.rate_limit',
                'name' => 'Email Rate Limit',
                'value' => 100,
                'type' => 'number',
                'group' => 'email',
                'description' => 'Maximum emails per hour',
                'help_text' => 'Limit the number of emails sent per hour to prevent spam',
                'is_public' => false,
                'sort_order' => 6,
            ],
        ];

        $this->createSettingsWithTranslations($settings);
    }

    private function createPaymentSettings(): void
    {
        $paymentCategory = SystemSettingCategory::where('slug', 'payment')->first();

        if (!$paymentCategory) {
            $this->command->warn('Payment category not found, skipping payment settings');
            return;
        }

        $settings = [
            [
                'category_id' => $paymentCategory->id,
                'key' => 'payment.default_method',
                'name' => 'Default Payment Method',
                'value' => 'bank_transfer',
                'type' => 'select',
                'group' => 'payment',
                'description' => 'Default payment method for orders',
                'help_text' => 'This will be pre-selected during checkout',
                'is_public' => true,
                'is_required' => true,
                'options' => [
                    'bank_transfer' => 'Bank Transfer',
                    'credit_card' => 'Credit Card',
                    'paypal' => 'PayPal',
                    'cash_on_delivery' => 'Cash on Delivery',
                ],
                'sort_order' => 1,
            ],
            [
                'category_id' => $paymentCategory->id,
                'key' => 'payment.auto_approve_orders',
                'name' => 'Auto Approve Orders',
                'value' => false,
                'type' => 'boolean',
                'group' => 'payment',
                'description' => 'Automatically approve orders after payment',
                'help_text' => 'When enabled, orders will be automatically approved after successful payment',
                'is_public' => false,
                'sort_order' => 2,
            ],
            [
                'category_id' => $paymentCategory->id,
                'key' => 'payment.payment_timeout',
                'name' => 'Payment Timeout (minutes)',
                'value' => 30,
                'type' => 'number',
                'group' => 'payment',
                'description' => 'Time limit for payment completion',
                'help_text' => 'Orders will be cancelled if payment is not completed within this time',
                'is_public' => false,
                'sort_order' => 3,
            ],
        ];

        $this->createSettingsWithTranslations($settings);
    }

    private function createShippingSettings(): void
    {
        $shippingCategory = SystemSettingCategory::where('slug', 'shipping')->first();

        if (!$shippingCategory) {
            $this->command->warn('Shipping category not found, skipping shipping settings');
            return;
        }

        $settings = [
            [
                'category_id' => $shippingCategory->id,
                'key' => 'shipping.default_method',
                'name' => 'Default Shipping Method',
                'value' => 'standard',
                'type' => 'select',
                'group' => 'shipping',
                'description' => 'Default shipping method for orders',
                'help_text' => 'This will be pre-selected during checkout',
                'is_public' => true,
                'is_required' => true,
                'options' => [
                    'standard' => 'Standard Shipping',
                    'express' => 'Express Shipping',
                    'pickup' => 'Store Pickup',
                ],
                'sort_order' => 1,
            ],
            [
                'category_id' => $shippingCategory->id,
                'key' => 'shipping.default_cost',
                'name' => 'Default Shipping Cost',
                'value' => 5.99,
                'type' => 'number',
                'group' => 'shipping',
                'description' => 'Default shipping cost in EUR',
                'help_text' => 'This cost will be applied when no specific shipping method is selected',
                'is_public' => true,
                'sort_order' => 2,
            ],
            [
                'category_id' => $shippingCategory->id,
                'key' => 'shipping.estimated_delivery_days',
                'name' => 'Estimated Delivery Days',
                'value' => 3,
                'type' => 'number',
                'group' => 'shipping',
                'description' => 'Estimated delivery time in days',
                'help_text' => 'This will be shown to customers during checkout',
                'is_public' => true,
                'sort_order' => 3,
            ],
        ];

        $this->createSettingsWithTranslations($settings);
    }

    private function createSeoSettings(): void
    {
        $seoCategory = SystemSettingCategory::where('slug', 'seo')->first();

        if (!$seoCategory) {
            $this->command->warn('SEO category not found, skipping SEO settings');
            return;
        }

        $settings = [
            [
                'category_id' => $seoCategory->id,
                'key' => 'seo.meta_title',
                'name' => 'Default Meta Title',
                'value' => 'Statybos E-commerce - Professional Construction Materials',
                'type' => 'string',
                'group' => 'seo',
                'description' => 'Default meta title for pages',
                'help_text' => 'This will be used when no specific meta title is set',
                'is_public' => true,
                'sort_order' => 1,
            ],
            [
                'category_id' => $seoCategory->id,
                'key' => 'seo.meta_description',
                'name' => 'Default Meta Description',
                'value' => 'Professional construction materials and tools for your building projects. Quality products, competitive prices, fast delivery.',
                'type' => 'text',
                'group' => 'seo',
                'description' => 'Default meta description for pages',
                'help_text' => 'This will be used when no specific meta description is set',
                'is_public' => true,
                'sort_order' => 2,
            ],
            [
                'category_id' => $seoCategory->id,
                'key' => 'seo.meta_keywords',
                'name' => 'Default Meta Keywords',
                'value' => 'construction materials, building supplies, tools, statyba, statybos medžiagos',
                'type' => 'string',
                'group' => 'seo',
                'description' => 'Default meta keywords for pages',
                'help_text' => 'Comma-separated keywords for SEO',
                'is_public' => true,
                'sort_order' => 3,
            ],
            [
                'category_id' => $seoCategory->id,
                'key' => 'seo.google_analytics_id',
                'name' => 'Google Analytics ID',
                'value' => '',
                'type' => 'string',
                'group' => 'seo',
                'description' => 'Google Analytics tracking ID',
                'help_text' => 'Enter your Google Analytics tracking ID (e.g., GA-XXXXXXXXX)',
                'is_public' => false,
                'sort_order' => 4,
            ],
            [
                'category_id' => $seoCategory->id,
                'key' => 'seo.google_search_console',
                'name' => 'Google Search Console Verification',
                'value' => '',
                'type' => 'string',
                'group' => 'seo',
                'description' => 'Google Search Console verification code',
                'help_text' => 'Enter your Google Search Console verification meta tag content',
                'is_public' => false,
                'sort_order' => 5,
            ],
        ];

        $this->createSettingsWithTranslations($settings);
    }

    private function createSecuritySettings(): void
    {
        $securityCategory = SystemSettingCategory::where('slug', 'security')->first();

        $settings = [
            [
                'category_id' => $securityCategory->id,
                'key' => 'security.password_min_length',
                'name' => 'Minimum Password Length',
                'value' => 8,
                'type' => 'number',
                'group' => 'security',
                'description' => 'Minimum password length requirement',
                'help_text' => 'Users must create passwords with at least this many characters',
                'is_public' => false,
                'is_required' => true,
                'sort_order' => 1,
            ],
            [
                'category_id' => $securityCategory->id,
                'key' => 'security.password_require_special',
                'name' => 'Require Special Characters',
                'value' => true,
                'type' => 'boolean',
                'group' => 'security',
                'description' => 'Require special characters in passwords',
                'help_text' => 'Passwords must contain at least one special character',
                'is_public' => false,
                'sort_order' => 2,
            ],
            [
                'category_id' => $securityCategory->id,
                'key' => 'security.session_timeout',
                'name' => 'Session Timeout (minutes)',
                'value' => 120,
                'type' => 'number',
                'group' => 'security',
                'description' => 'User session timeout in minutes',
                'help_text' => 'Users will be logged out after this period of inactivity',
                'is_public' => false,
                'sort_order' => 3,
            ],
            [
                'category_id' => $securityCategory->id,
                'key' => 'security.max_login_attempts',
                'name' => 'Max Login Attempts',
                'value' => 5,
                'type' => 'number',
                'group' => 'security',
                'description' => 'Maximum failed login attempts before lockout',
                'help_text' => 'Account will be locked after this many failed attempts',
                'is_public' => false,
                'sort_order' => 4,
            ],
            [
                'category_id' => $securityCategory->id,
                'key' => 'security.two_factor_auth',
                'name' => 'Enable Two-Factor Authentication',
                'value' => false,
                'type' => 'boolean',
                'group' => 'security',
                'description' => 'Enable two-factor authentication for admin users',
                'help_text' => 'Admin users will be required to use 2FA',
                'is_public' => false,
                'sort_order' => 5,
            ],
        ];

        $this->createSettingsWithTranslations($settings);
    }

    private function createApiSettings(): void
    {
        $apiCategory = SystemSettingCategory::where('slug', 'api')->first();

        if (!$apiCategory) {
            $this->command->warn('API category not found, skipping API settings');
            return;
        }

        $settings = [
            [
                'category_id' => $apiCategory->id,
                'key' => 'api.rate_limit',
                'name' => 'API Rate Limit',
                'value' => 1000,
                'type' => 'number',
                'group' => 'api',
                'description' => 'API requests per hour limit',
                'help_text' => 'Maximum number of API requests allowed per hour',
                'is_public' => false,
                'sort_order' => 1,
            ],
            [
                'category_id' => $apiCategory->id,
                'key' => 'api.api_key_length',
                'name' => 'API Key Length',
                'value' => 32,
                'type' => 'number',
                'group' => 'api',
                'description' => 'Length of generated API keys',
                'help_text' => 'API keys will be generated with this length',
                'is_public' => false,
                'sort_order' => 2,
            ],
            [
                'category_id' => $apiCategory->id,
                'key' => 'api.enable_api',
                'name' => 'Enable API',
                'value' => true,
                'type' => 'boolean',
                'group' => 'api',
                'description' => 'Enable API access',
                'help_text' => 'Allow external applications to access the API',
                'is_public' => false,
                'sort_order' => 3,
            ],
        ];

        $this->createSettingsWithTranslations($settings);
    }

    private function createAppearanceSettings(): void
    {
        $appearanceCategory = SystemSettingCategory::where('slug', 'appearance')->first();

        if (!$appearanceCategory) {
            $this->command->warn('Appearance category not found, skipping appearance settings');
            return;
        }

        $settings = [
            [
                'category_id' => $appearanceCategory->id,
                'key' => 'appearance.primary_color',
                'name' => 'Primary Color',
                'value' => '#3B82F6',
                'type' => 'color',
                'group' => 'appearance',
                'description' => 'Primary brand color',
                'help_text' => 'This color will be used throughout the application',
                'is_public' => true,
                'sort_order' => 1,
            ],
            [
                'category_id' => $appearanceCategory->id,
                'key' => 'appearance.secondary_color',
                'name' => 'Secondary Color',
                'value' => '#6B7280',
                'type' => 'color',
                'group' => 'appearance',
                'description' => 'Secondary brand color',
                'help_text' => 'This color will be used for secondary elements',
                'is_public' => true,
                'sort_order' => 2,
            ],
            [
                'category_id' => $appearanceCategory->id,
                'key' => 'appearance.logo_url',
                'name' => 'Logo URL',
                'value' => '',
                'type' => 'string',
                'group' => 'appearance',
                'description' => 'URL to the application logo',
                'help_text' => 'This logo will be displayed in the header and emails',
                'is_public' => true,
                'sort_order' => 3,
            ],
            [
                'category_id' => $appearanceCategory->id,
                'key' => 'appearance.favicon_url',
                'name' => 'Favicon URL',
                'value' => '',
                'type' => 'string',
                'group' => 'appearance',
                'description' => 'URL to the favicon',
                'help_text' => 'This icon will be displayed in browser tabs',
                'is_public' => true,
                'sort_order' => 4,
            ],
        ];

        $this->createSettingsWithTranslations($settings);
    }

    private function createNotificationSettings(): void
    {
        $notificationCategory = SystemSettingCategory::where('slug', 'notifications')->first();

        if (!$notificationCategory) {
            $this->command->warn('Notifications category not found, skipping notification settings');
            return;
        }

        $settings = [
            [
                'category_id' => $notificationCategory->id,
                'key' => 'notifications.email_notifications',
                'name' => 'Enable Email Notifications',
                'value' => true,
                'type' => 'boolean',
                'group' => 'notifications',
                'description' => 'Enable email notifications',
                'help_text' => 'Send email notifications for important events',
                'is_public' => false,
                'sort_order' => 1,
            ],
            [
                'category_id' => $notificationCategory->id,
                'key' => 'notifications.sms_notifications',
                'name' => 'Enable SMS Notifications',
                'value' => false,
                'type' => 'boolean',
                'group' => 'notifications',
                'description' => 'Enable SMS notifications',
                'help_text' => 'Send SMS notifications for important events',
                'is_public' => false,
                'sort_order' => 2,
            ],
            [
                'category_id' => $notificationCategory->id,
                'key' => 'notifications.push_notifications',
                'name' => 'Enable Push Notifications',
                'value' => true,
                'type' => 'boolean',
                'group' => 'notifications',
                'description' => 'Enable browser push notifications',
                'help_text' => 'Send push notifications to users browsers',
                'is_public' => false,
                'sort_order' => 3,
            ],
        ];

        $this->createSettingsWithTranslations($settings);
    }

    private function createSettingsWithTranslations(array $settings): void
    {
        foreach ($settings as $settingData) {
            $setting = SystemSetting::firstOrCreate(
                ['key' => $settingData['key']],
                $settingData
            );

            // Create Lithuanian translations directly to avoid factory uniqueness constraints
            SystemSettingTranslation::firstOrCreate(
                [
                    'system_setting_id' => $setting->id,
                    'locale' => 'lt',
                ],
                [
                    'name' => $this->getLithuanianTranslation($settingData['name']),
                    'description' => $this->getLithuanianTranslation($settingData['description'] ?? ''),
                    'help_text' => $this->getLithuanianTranslation($settingData['help_text'] ?? ''),
                ]
            );
        }
    }

    private function getLithuanianTranslation(string $english): string
    {
        $translations = [
            // Categories
            'General' => 'Bendri',
            'E-commerce' => 'E-parduotuvė',
            'Email' => 'El. paštas',
            'Payment' => 'Mokėjimai',
            'Shipping' => 'Pristatymas',
            'SEO' => 'SEO',
            'Security' => 'Saugumas',
            'API' => 'API',
            'Appearance' => 'Išvaizda',
            'Notifications' => 'Pranešimai',
            // General Settings
            'Application Name' => 'Programos pavadinimas',
            'Application Description' => 'Programos aprašymas',
            'Timezone' => 'Laiko juosta',
            'Default Currency' => 'Numatytoji valiuta',
            'Default Language' => 'Numatytoji kalba',
            'Maintenance Mode' => 'Priežiūros režimas',
            'Debug Mode' => 'Derinimo režimas',
            // E-commerce Settings
            'Default Tax Rate' => 'Numatytasis PVM tarifas',
            'Minimum Order Amount' => 'Minimalus užsakymo dydis',
            'Free Shipping Threshold' => 'Nemokamo pristatymo riba',
            'Inventory Tracking' => 'Atsargų sekimas',
            'Low Stock Threshold' => 'Mažų atsargų riba',
            'Allow Guest Checkout' => 'Leisti svečių užsakymus',
            'Enable Product Reviews' => 'Įjungti produktų atsiliepimus',
            'Enable Wishlist' => 'Įjungti pageidavimų sąrašą',
            // Email Settings
            'From Email Address' => 'Siuntėjo el. pašto adresas',
            'From Name' => 'Siuntėjo vardas',
            'Support Email' => 'Palaikymo el. paštas',
            'Admin Email' => 'Administratoriaus el. paštas',
            'Queue Emails' => 'El. laiškų eilė',
            'Email Rate Limit' => 'El. laiškų limitas',
            // Payment Settings
            'Default Payment Method' => 'Numatytasis mokėjimo būdas',
            'Auto Approve Orders' => 'Automatinis užsakymų patvirtinimas',
            'Payment Timeout (minutes)' => 'Mokėjimo laiko limitas (minutės)',
            // Shipping Settings
            'Default Shipping Method' => 'Numatytasis pristatymo būdas',
            'Default Shipping Cost' => 'Numatytasis pristatymo kaina',
            'Estimated Delivery Days' => 'Numatomi pristatymo dienos',
            // SEO Settings
            'Default Meta Title' => 'Numatytasis meta pavadinimas',
            'Default Meta Description' => 'Numatytasis meta aprašymas',
            'Default Meta Keywords' => 'Numatytieji meta raktažodžiai',
            'Google Analytics ID' => 'Google Analytics ID',
            'Google Search Console Verification' => 'Google Search Console patvirtinimas',
            // Security Settings
            'Minimum Password Length' => 'Minimalus slaptažodžio ilgis',
            'Require Special Characters' => 'Reikalauti specialių simbolių',
            'Session Timeout (minutes)' => 'Sesijos laiko limitas (minutės)',
            'Max Login Attempts' => 'Maksimalus prisijungimo bandymų skaičius',
            'Enable Two-Factor Authentication' => 'Įjungti dviejų faktorių autentifikavimą',
            // API Settings
            'API Rate Limit' => 'API užklausų limitas',
            'API Key Length' => 'API rakto ilgis',
            'Enable API' => 'Įjungti API',
            // Appearance Settings
            'Primary Color' => 'Pagrindinė spalva',
            'Secondary Color' => 'Antrinė spalva',
            'Logo URL' => 'Logotipo URL',
            'Favicon URL' => 'Favicon URL',
            // Notification Settings
            'Enable Email Notifications' => 'Įjungti el. pašto pranešimus',
            'Enable SMS Notifications' => 'Įjungti SMS pranešimus',
            'Enable Push Notifications' => 'Įjungti push pranešimus',
        ];

        return $translations[$english] ?? $english;
    }
}
