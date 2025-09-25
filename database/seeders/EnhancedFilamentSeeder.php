<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class EnhancedFilamentSeeder extends Seeder
{
    public function run(): void
    {
        $this->createAdminUser();
        $this->enhanceProductData();
        $this->createSampleOrders();
        $this->createSampleReviews();
        $this->createAnalyticsData();
    }

    private function createAdminUser(): void
    {
        $adminRole = Role::query()->firstOrCreate(['name' => 'admin']);

        $permissions = [
            'view_admin_panel',
            'manage_products',
            'manage_orders',
            'manage_users',
            'manage_settings',
            'view_analytics',
        ];

        collect($permissions)->each(fn (string $permission) => Permission::query()->firstOrCreate(['name' => $permission]));

        $adminRole->syncPermissions($permissions);

        $admin = User::query()->firstOrCreate(
            ['email' => 'admin@statybaecommerse.prus.dev'],
            [
                'name' => 'System Administrator',
                'password' => bcrypt('admin123'),
                'email_verified_at' => now(),
                'preferred_locale' => 'lt',
                'is_admin' => true,
                'is_active' => true,
            ]
        );

        $admin->assignRole('admin');

        $this->command->info('✅ Admin user created: admin@statybaecommerse.prus.dev / admin123');
    }

    private function enhanceProductData(): void
    {
        // Set some products as featured
        Product::where('is_visible', true)
            ->inRandomOrder()
            ->limit(8)
            ->update(['is_featured' => true, 'sort_order' => rand(1, 100)]);

        // Set some categories as featured
        Category::where('is_visible', true)
            ->inRandomOrder()
            ->limit(6)
            ->update(['is_featured' => true, 'show_in_menu' => true]);

        // Set some brands as featured and premium
        Brand::where('is_enabled', true)
            ->inRandomOrder()
            ->limit(5)
            ->update(['is_featured' => true, 'is_premium' => true]);

        $this->command->info('✅ Enhanced product data with featured items');
    }

    private function createSampleOrders(): void
    {
        $users = User::where('is_admin', false)->get();
        $products = Product::where('is_visible', true)->get();

        if ($users->isEmpty() || $products->isEmpty()) {
            $this->command->warn('⚠️ No users or products found for creating sample orders');

            return;
        }

        Order::factory()
            ->count(20)
            ->recent()
            ->for($users->random())
            ->create();

        $this->command->info('✅ Created 20 sample orders');
    }

    private function createSampleReviews(): void
    {
        $users = User::where('is_admin', false)->get();
        $products = Product::where('is_visible', true)->get();

        if ($users->isEmpty() || $products->isEmpty()) {
            $this->command->warn('⚠️ No users or products found for creating sample reviews');

            return;
        }

        $reviewTexts = [
            'Puikus produktas, labai patenkintas pirkimu!',
            'Kokybė atitinka kainą, rekomenduoju.',
            'Greitas pristatymas, produktas kaip aprašyta.',
            'Excellent quality, exactly as described.',
            'Fast delivery, great customer service.',
            'Good value for money, would buy again.',
            'Labai gera kokybė, viršijo lūkesčius.',
            'Produktas atitiko visus lūkesčius.',
        ];

        Review::factory()
            ->count(50)
            ->state(fn () => [
                'content' => $reviewTexts[rand(0, count($reviewTexts) - 1)],
            ])
            ->for($products->random())
            ->for($users->random())
            ->create();

        $this->command->info('✅ Created 50 sample reviews');
    }

    private function createAnalyticsData(): void
    {
        $products = Product::where('is_visible', true)
            ->limit(20)
            ->get();

        // Placeholder for analytics data seeding once dedicated model/factory exists
    }
}
