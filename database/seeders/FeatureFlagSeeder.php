<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\FeatureFlag;
use Illuminate\Database\Seeder;

final class FeatureFlagSeeder extends Seeder
{
    public function run(): void
    {
        // UI Category Feature Flags
        FeatureFlag::factory()->create([
            'name' => 'New Product Search',
            'key' => 'new_product_search',
            'description' => 'Enable the new advanced product search functionality',
            'is_active' => true,
            'is_enabled' => true,
            'is_global' => false,
            'environment' => 'production',
            'category' => 'ui',
            'priority' => 80,
            'conditions' => [
                'user_role' => ['admin', 'manager'],
            ],
            'metadata' => [
                'version' => '1.0',
                'author' => 'Development Team',
            ],
        ]);

        FeatureFlag::factory()->create([
            'name' => 'Mobile App Features',
            'key' => 'mobile_app_features',
            'description' => 'Enable mobile app specific features',
            'is_active' => true,
            'is_enabled' => true,
            'is_global' => true,
            'environment' => 'production',
            'category' => 'ui',
            'priority' => 60,
            'conditions' => [],
            'metadata' => [
                'version' => '1.5',
                'author' => 'Mobile Team',
            ],
        ]);

        FeatureFlag::factory()->create([
            'name' => 'Dark Mode',
            'key' => 'dark_mode',
            'description' => 'Enable dark mode theme for the application',
            'is_active' => true,
            'is_enabled' => true,
            'is_global' => true,
            'environment' => 'production',
            'category' => 'ui',
            'priority' => 30,
            'conditions' => [],
            'metadata' => [
                'version' => '1.0',
                'author' => 'UI Team',
            ],
        ]);

        FeatureFlag::factory()->create([
            'name' => 'Multi-language Support',
            'key' => 'multi_language_support',
            'description' => 'Enable multi-language support for the application',
            'is_active' => true,
            'is_enabled' => true,
            'is_global' => true,
            'environment' => 'production',
            'category' => 'ui',
            'priority' => 20,
            'conditions' => [],
            'metadata' => [
                'version' => '1.0',
                'author' => 'Localization Team',
            ],
        ]);

        FeatureFlag::factory()->create([
            'name' => 'Beta Features',
            'key' => 'beta_features',
            'description' => 'Enable beta features for testing',
            'is_active' => true,
            'is_enabled' => false,
            'is_global' => false,
            'environment' => 'staging',
            'category' => 'ui',
            'priority' => 10,
            'conditions' => [
                'user_role' => ['admin'],
            ],
            'metadata' => [
                'version' => '0.9',
                'author' => 'Development Team',
            ],
        ]);

        // Analytics Category Feature Flags
        FeatureFlag::factory()->create([
            'name' => 'Advanced Analytics',
            'key' => 'advanced_analytics',
            'description' => 'Enable advanced analytics dashboard with real-time data',
            'is_active' => true,
            'is_enabled' => false,
            'is_global' => false,
            'environment' => 'production',
            'category' => 'analytics',
            'priority' => 70,
            'conditions' => [
                'user_role' => ['admin'],
            ],
            'metadata' => [
                'version' => '2.0',
                'author' => 'Analytics Team',
            ],
        ]);

        // Payment Category Feature Flags
        FeatureFlag::factory()->create([
            'name' => 'Payment Gateway V2',
            'key' => 'payment_gateway_v2',
            'description' => 'Enable the new payment gateway integration',
            'is_active' => true,
            'is_enabled' => false,
            'is_global' => false,
            'environment' => 'staging',
            'category' => 'payment',
            'priority' => 90,
            'conditions' => [
                'user_group' => ['beta_testers'],
            ],
            'metadata' => [
                'version' => '2.1',
                'author' => 'Payment Team',
            ],
        ]);

        // Performance Category Feature Flags
        FeatureFlag::factory()->create([
            'name' => 'AI Recommendations',
            'key' => 'ai_recommendations',
            'description' => 'Enable AI-powered product recommendations',
            'is_active' => true,
            'is_enabled' => true,
            'is_global' => false,
            'environment' => 'production',
            'category' => 'performance',
            'priority' => 50,
            'conditions' => [
                'user_role' => ['customer'],
            ],
            'metadata' => [
                'version' => '1.0',
                'author' => 'AI Team',
            ],
        ]);

        FeatureFlag::factory()->create([
            'name' => 'Real-time Notifications',
            'key' => 'real_time_notifications',
            'description' => 'Enable real-time push notifications',
            'is_active' => true,
            'is_enabled' => false,
            'is_global' => false,
            'environment' => 'staging',
            'category' => 'performance',
            'priority' => 40,
            'conditions' => [
                'user_role' => ['admin', 'manager'],
            ],
            'metadata' => [
                'version' => '1.0',
                'author' => 'Notification Team',
            ],
        ]);

        // Security Category Feature Flags
        FeatureFlag::factory()->create([
            'name' => 'Advanced Security',
            'key' => 'advanced_security',
            'description' => 'Enable advanced security features',
            'is_active' => true,
            'is_enabled' => true,
            'is_global' => true,
            'environment' => 'production',
            'category' => 'security',
            'priority' => 95,
            'conditions' => [],
            'metadata' => [
                'version' => '1.0',
                'author' => 'Security Team',
            ],
        ]);
    }
}
