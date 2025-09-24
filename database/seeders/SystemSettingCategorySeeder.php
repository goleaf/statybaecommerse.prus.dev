<?php

declare(strict_types=1);

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
                'description' => 'General system settings and configurations',
                'icon' => 'heroicon-o-cog-6-tooth',
                'color' => 'primary',
                'sort_order' => 1,
                'is_active' => true,
                'parent_id' => null,
            ],
            [
                'name' => 'Security',
                'slug' => 'security',
                'description' => 'Security-related settings and configurations',
                'icon' => 'heroicon-o-shield-check',
                'color' => 'danger',
                'sort_order' => 2,
                'is_active' => true,
                'parent_id' => null,
            ],
            [
                'name' => 'Performance',
                'slug' => 'performance',
                'description' => 'Performance optimization settings',
                'icon' => 'heroicon-o-bolt',
                'color' => 'warning',
                'sort_order' => 3,
                'is_active' => true,
                'parent_id' => null,
            ],
            [
                'name' => 'UI/UX',
                'slug' => 'ui-ux',
                'description' => 'User interface and user experience settings',
                'icon' => 'heroicon-o-paint-brush',
                'color' => 'info',
                'sort_order' => 4,
                'is_active' => true,
                'parent_id' => null,
            ],
            [
                'name' => 'API',
                'slug' => 'api',
                'description' => 'API configuration and settings',
                'icon' => 'heroicon-o-globe-alt',
                'color' => 'success',
                'sort_order' => 5,
                'is_active' => true,
                'parent_id' => null,
            ],
            [
                'name' => 'Database',
                'slug' => 'database',
                'description' => 'Database configuration and settings',
                'icon' => 'heroicon-o-database',
                'color' => 'secondary',
                'sort_order' => 6,
                'is_active' => true,
                'parent_id' => null,
            ],
            [
                'name' => 'Authentication',
                'slug' => 'authentication',
                'description' => 'Authentication and authorization settings',
                'icon' => 'heroicon-o-key',
                'color' => 'danger',
                'sort_order' => 7,
                'is_active' => true,
                'parent_id' => null,
            ],
            [
                'name' => 'Analytics',
                'slug' => 'analytics',
                'description' => 'Analytics and reporting settings',
                'icon' => 'heroicon-o-chart-bar',
                'color' => 'info',
                'sort_order' => 8,
                'is_active' => true,
                'parent_id' => null,
            ],
        ];

        foreach ($categories as $categoryData) {
            SystemSettingCategory::updateOrCreate(
                ['slug' => $categoryData['slug']],
                $categoryData
            );
        }
    }
}
