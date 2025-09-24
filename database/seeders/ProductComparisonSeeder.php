<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductComparison;
use App\Models\User;
use Illuminate\Database\Seeder;

final class ProductComparisonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some users and products for seeding
        $users = User::limit(10)->get();
        $products = Product::limit(20)->get();

        if ($users->isEmpty() || $products->isEmpty()) {
            $this->command->warn('No users or products found. Please run UserSeeder and ProductSeeder first.');

            return;
        }

        // Create product comparisons for different scenarios
        $this->createSessionComparisons($users, $products);
        $this->createUserComparisons($users, $products);
        $this->createRecentComparisons($users, $products);
    }

    /**
     * Create comparisons for different sessions
     */
    private function createSessionComparisons($users, $products): void
    {
        $sessions = [
            'session_12345',
            'session_67890',
            'session_abcde',
            'session_fghij',
            'session_klmno',
        ];

        foreach ($sessions as $sessionId) {
            // Each session has 3-7 products being compared
            $sessionProducts = $products->random(rand(3, 7));

            foreach ($sessionProducts as $product) {
                ProductComparison::create([
                    'session_id' => $sessionId,
                    'user_id' => null, // Anonymous session
                    'product_id' => $product->id,
                ]);
            }
        }
    }

    /**
     * Create comparisons for logged-in users
     */
    private function createUserComparisons($users, $products): void
    {
        foreach ($users->take(5) as $user) {
            // Each user has 2-5 comparison sessions
            $userSessions = rand(2, 5);

            for ($i = 0; $i < $userSessions; $i++) {
                $sessionId = 'user_'.$user->id.'_session_'.($i + 1);
                $sessionProducts = $products->random(rand(2, 6));

                foreach ($sessionProducts as $product) {
                    ProductComparison::create([
                        'session_id' => $sessionId,
                        'user_id' => $user->id,
                        'product_id' => $product->id,
                    ]);
                }
            }
        }
    }

    /**
     * Create recent comparisons (last 7 days)
     */
    private function createRecentComparisons($users, $products): void
    {
        $recentSessions = [
            'recent_session_1',
            'recent_session_2',
            'recent_session_3',
        ];

        foreach ($recentSessions as $sessionId) {
            $sessionProducts = $products->random(rand(2, 5));
            $createdAt = now()->subDays(rand(0, 7));

            foreach ($sessionProducts as $product) {
                ProductComparison::create([
                    'session_id' => $sessionId,
                    'user_id' => $users->random()->id,
                    'product_id' => $product->id,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }
        }
    }
}
