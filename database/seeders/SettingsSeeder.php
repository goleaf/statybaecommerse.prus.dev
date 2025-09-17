<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

final class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // General Settings
            [
                'key' => 'site_name',
                'display_name' => __('Site Name'),
                'value' => 'Statyba E-Commerce',
                'type' => 'string',
                'group' => 'general',
                'description' => __('The name of your website'),
                'is_public' => true,
                'is_required' => true,
            ],
            [
                'key' => 'site_description',
                'display_name' => __('Site Description'),
                'value' => __('Professional construction materials and tools'),
                'type' => 'text',
                'group' => 'general',
                'description' => __('A brief description of your website'),
                'is_public' => true,
                'is_required' => false,
            ],
            [
                'key' => 'site_logo',
                'display_name' => __('Site Logo'),
                'value' => '/images/logo.png',
                'type' => 'url',
                'group' => 'general',
                'description' => __('URL to your site logo'),
                'is_public' => true,
                'is_required' => false,
            ],
            [
                'key' => 'default_language',
                'display_name' => __('Default Language'),
                'value' => 'lt',
                'type' => 'string',
                'group' => 'general',
                'description' => __('Default language for the website'),
                'is_public' => true,
                'is_required' => true,
            ],
            [
                'key' => 'supported_languages',
                'display_name' => __('Supported Languages'),
                'value' => json_encode(['lt' => 'Lietuvių', 'en' => 'English']),
                'type' => 'json',
                'group' => 'general',
                'description' => __('List of supported languages'),
                'is_public' => true,
                'is_required' => true,
            ],
            // Currency Settings
            [
                'key' => 'default_currency',
                'display_name' => __('Default Currency'),
                'value' => 'EUR',
                'type' => 'string',
                'group' => 'general',
                'description' => __('Default currency code'),
                'is_public' => true,
                'is_required' => true,
            ],
            [
                'key' => 'currency_symbol',
                'display_name' => __('Currency Symbol'),
                'value' => '€',
                'type' => 'string',
                'group' => 'general',
                'description' => __('Currency symbol to display'),
                'is_public' => true,
                'is_required' => true,
            ],
            // Email Settings
            [
                'key' => 'admin_email',
                'display_name' => __('Admin Email'),
                'value' => 'admin@statybaecommerse.prus.dev',
                'type' => 'email',
                'group' => 'email',
                'description' => __('Main administrator email address'),
                'is_public' => false,
                'is_required' => true,
            ],
            [
                'key' => 'from_email',
                'display_name' => __('From Email'),
                'value' => 'noreply@statybaecommerse.prus.dev',
                'type' => 'email',
                'group' => 'email',
                'description' => __('Email address used for outgoing emails'),
                'is_public' => false,
                'is_required' => true,
            ],
            [
                'key' => 'email_notifications',
                'display_name' => __('Email Notifications'),
                'value' => true,
                'type' => 'boolean',
                'group' => 'email',
                'description' => __('Enable email notifications'),
                'is_public' => false,
                'is_required' => false,
            ],
            // Payment Settings
            [
                'key' => 'payment_methods',
                'display_name' => __('Payment Methods'),
                'value' => json_encode(['bank_transfer', 'credit_card', 'paypal']),
                'type' => 'json',
                'group' => 'payment',
                'description' => __('Enabled payment methods'),
                'is_public' => true,
                'is_required' => true,
            ],
            [
                'key' => 'tax_rate',
                'display_name' => __('Tax Rate'),
                'value' => 21.0,
                'type' => 'number',
                'group' => 'payment',
                'description' => __('Default tax rate percentage'),
                'is_public' => true,
                'is_required' => true,
            ],
            // Shipping Settings
            [
                'key' => 'free_shipping_threshold',
                'display_name' => __('Free Shipping Threshold'),
                'value' => 100.0,
                'type' => 'number',
                'group' => 'shipping',
                'description' => __('Minimum order amount for free shipping'),
                'is_public' => true,
                'is_required' => false,
            ],
            [
                'key' => 'shipping_cost',
                'display_name' => __('Shipping Cost'),
                'value' => 5.99,
                'type' => 'number',
                'group' => 'shipping',
                'description' => __('Default shipping cost'),
                'is_public' => true,
                'is_required' => true,
            ],
            // SEO Settings
            [
                'key' => 'meta_title',
                'display_name' => __('Meta Title'),
                'value' => __('Statyba E-Commerce - Construction Materials'),
                'type' => 'string',
                'group' => 'seo',
                'description' => __('Default meta title for pages'),
                'is_public' => true,
                'is_required' => false,
            ],
            [
                'key' => 'meta_description',
                'display_name' => __('Meta Description'),
                'value' => __('Professional construction materials and tools for all your building needs'),
                'type' => 'text',
                'group' => 'seo',
                'description' => __('Default meta description for pages'),
                'is_public' => true,
                'is_required' => false,
            ],
            // Social Media Settings
            [
                'key' => 'facebook_url',
                'display_name' => __('Facebook URL'),
                'value' => '',
                'type' => 'url',
                'group' => 'social',
                'description' => __('Facebook page URL'),
                'is_public' => true,
                'is_required' => false,
            ],
            [
                'key' => 'instagram_url',
                'display_name' => __('Instagram URL'),
                'value' => '',
                'type' => 'url',
                'group' => 'social',
                'description' => __('Instagram profile URL'),
                'is_public' => true,
                'is_required' => false,
            ],
            // Appearance Settings
            [
                'key' => 'theme_color',
                'display_name' => __('Theme Color'),
                'value' => '#3b82f6',
                'type' => 'color',
                'group' => 'appearance',
                'description' => __('Primary theme color'),
                'is_public' => true,
                'is_required' => false,
            ],
            [
                'key' => 'items_per_page',
                'display_name' => __('Items Per Page'),
                'value' => 20,
                'type' => 'number',
                'group' => 'appearance',
                'description' => __('Number of items to show per page'),
                'is_public' => true,
                'is_required' => true,
            ],
            // Security Settings
            [
                'key' => 'maintenance_mode',
                'display_name' => __('Maintenance Mode'),
                'value' => false,
                'type' => 'boolean',
                'group' => 'security',
                'description' => __('Enable maintenance mode'),
                'is_public' => false,
                'is_required' => false,
            ],
            [
                'key' => 'api_rate_limit',
                'display_name' => __('API Rate Limit'),
                'value' => 60,
                'type' => 'number',
                'group' => 'security',
                'description' => __('API requests per minute limit'),
                'is_public' => false,
                'is_required' => true,
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }

        $this->command->info('Settings seeded successfully with multilanguage support!');
    }
}
