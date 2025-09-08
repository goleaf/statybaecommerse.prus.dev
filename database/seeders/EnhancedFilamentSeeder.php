<?php declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Order;
use App\Models\Review;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

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
        // Create admin role if not exists
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        
        // Create admin permissions
        $permissions = [
            'view_admin_panel',
            'manage_products',
            'manage_orders',
            'manage_users',
            'manage_settings',
            'view_analytics',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $adminRole->syncPermissions($permissions);

        // Create admin user
        $admin = User::firstOrCreate(
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

        foreach (range(1, 20) as $i) {
            $user = $users->random();
            $orderProducts = $products->random(rand(1, 5));
            $total = 0;

            foreach ($orderProducts as $product) {
                $total += $product->price * rand(1, 3);
            }

            Order::create([
                'number' => 'ORD-' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'user_id' => $user->id,
                'status' => ['pending', 'processing', 'shipped', 'delivered'][rand(0, 3)],
                'payment_status' => ['pending', 'paid', 'failed'][rand(0, 2)],
                'payment_method' => ['credit_card', 'bank_transfer', 'paypal'][rand(0, 2)],
                'subtotal' => $total,
                'tax_amount' => $total * 0.21,
                'shipping_amount' => rand(5, 15),
                'total' => $total + ($total * 0.21) + rand(5, 15),
                'currency' => 'EUR',
                'created_at' => now()->subDays(rand(0, 30)),
            ]);
        }

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

        foreach (range(1, 50) as $i) {
            $user = $users->random();
            $product = $products->random();

            Review::create([
                'product_id' => $product->id,
                'user_id' => $user->id,
                'reviewer_name' => $user->name,
                'reviewer_email' => $user->email,
                'rating' => rand(3, 5),
                'title' => 'Puikus produktas',
                'content' => $reviewTexts[rand(0, count($reviewTexts) - 1)],
                'is_approved' => rand(0, 1) === 1,
                'created_at' => now()->subDays(rand(0, 60)),
            ]);
        }

        $this->command->info('✅ Created 50 sample reviews');
    }

    private function createAnalyticsData(): void
    {
        $products = Product::where('is_visible', true)->limit(20)->get();

        foreach ($products as $product) {
            foreach (range(0, 30) as $daysAgo) {
                $date = now()->subDays($daysAgo)->format('Y-m-d');
                
                \DB::table('product_analytics')->insertOrIgnore([
                    'product_id' => $product->id,
                    'date' => $date,
                    'views' => rand(10, 100),
                    'cart_additions' => rand(1, 20),
                    'purchases' => rand(0, 5),
                    'wishlist_additions' => rand(0, 10),
                    'conversion_rate' => rand(100, 500) / 10000, // 1-5%
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->command->info('✅ Created analytics data for products');
    }
}