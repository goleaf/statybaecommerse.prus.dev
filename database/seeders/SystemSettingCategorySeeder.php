<?php declare(strict_types=1);

namespace Database\Seeders;

use App\Models\SystemSettingCategory;
use Illuminate\Database\Seeder;

final class SystemSettingCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'General',
                'slug' => 'general',
                'description' => 'General system settings',
                'icon' => 'heroicon-o-cog-6-tooth',
                'color' => '#3B82F6',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Security',
                'slug' => 'security',
                'description' => 'Security related settings',
                'icon' => 'heroicon-o-shield-check',
                'color' => '#EF4444',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Email',
                'slug' => 'email',
                'description' => 'Email configuration settings',
                'icon' => 'heroicon-o-envelope',
                'color' => '#10B981',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Database',
                'slug' => 'database',
                'description' => 'Database configuration settings',
                'icon' => 'heroicon-o-circle-stack',
                'color' => '#8B5CF6',
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'Cache',
                'slug' => 'cache',
                'description' => 'Cache configuration settings',
                'icon' => 'heroicon-o-bolt',
                'color' => '#F59E0B',
                'sort_order' => 5,
                'is_active' => true,
            ],
            [
                'name' => 'API',
                'slug' => 'api',
                'description' => 'API configuration settings',
                'icon' => 'heroicon-o-code-bracket',
                'color' => '#06B6D4',
                'sort_order' => 6,
                'is_active' => true,
            ],
            [
                'name' => 'Payment',
                'slug' => 'payment',
                'description' => 'Payment gateway settings',
                'icon' => 'heroicon-o-credit-card',
                'color' => '#84CC16',
                'sort_order' => 7,
                'is_active' => true,
            ],
            [
                'name' => 'Notifications',
                'slug' => 'notifications',
                'description' => 'Notification settings',
                'icon' => 'heroicon-o-bell',
                'color' => '#F97316',
                'sort_order' => 8,
                'is_active' => true,
            ],
            [
                'name' => 'Storage',
                'slug' => 'storage',
                'description' => 'File storage settings',
                'icon' => 'heroicon-o-server',
                'color' => '#6366F1',
                'sort_order' => 9,
                'is_active' => true,
            ],
            [
                'name' => 'Analytics',
                'slug' => 'analytics',
                'description' => 'Analytics and tracking settings',
                'icon' => 'heroicon-o-chart-bar',
                'color' => '#EC4899',
                'sort_order' => 10,
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            SystemSettingCategory::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}
