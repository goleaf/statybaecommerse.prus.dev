<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductComparison;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

final class ProductComparisonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::limit(10)->get();
        $products = Product::limit(20)->get();

        if ($users->isEmpty() || $products->isEmpty()) {
            $this->command->warn('No users or products found. Please run UserSeeder and ProductSeeder first.');

            return;
        }

        $this->createSessionComparisons($products);
        $this->createUserComparisons($users, $products);
        $this->createRecentComparisons($users, $products);
    }

    private function createSessionComparisons(Collection $products): void
    {
        $sessions = [
            'session_12345',
            'session_67890',
            'session_abcde',
            'session_fghij',
            'session_klmno',
        ];

        foreach ($sessions as $sessionId) {
            if (ProductComparison::query()->where('session_id', $sessionId)->exists()) {
                continue;
            }

            $this->pickProducts($products, 3, 7)->each(function (Product $product) use ($sessionId): void {
                ProductComparison::factory()
                    ->forSession($sessionId)
                    ->forProduct($product)
                    ->state(['user_id' => null])
                    ->create();
            });
        }
    }

    private function createUserComparisons(Collection $users, Collection $products): void
    {
        $users->take(5)->each(function (User $user) use ($products): void {
            $userSessions = rand(2, 5);

            for ($i = 0; $i < $userSessions; $i++) {
                $sessionId = 'user_'.$user->id.'_session_'.($i + 1);

                if (ProductComparison::query()->where('session_id', $sessionId)->exists()) {
                    continue;
                }

                $this->pickProducts($products, 2, 6)->each(function (Product $product) use ($user, $sessionId): void {
                    ProductComparison::factory()
                        ->forUser($user)
                        ->forSession($sessionId)
                        ->forProduct($product)
                        ->create();
                });
            }
        });
    }

    private function createRecentComparisons(Collection $users, Collection $products): void
    {
        $recentSessions = [
            'recent_session_1',
            'recent_session_2',
            'recent_session_3',
        ];

        foreach ($recentSessions as $sessionId) {
            $sessionProducts = $this->pickProducts($products, 2, 5);
            if ($sessionProducts->isEmpty()) {
                continue;
            }

            $createdAt = now()->subDays(rand(0, 7));

            $sessionProducts->each(function (Product $product) use ($users, $sessionId, $createdAt): void {
                ProductComparison::factory()
                    ->forSession($sessionId)
                    ->forProduct($product)
                    ->state([
                        'user_id' => $users->random()->id,
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ])
                    ->create();
            });
        }
    }

    private function pickProducts(Collection $products, int $min, int $max): Collection
    {
        if ($products->isEmpty()) {
            return collect();
        }

        $minPick = min($products->count(), $min);
        $maxPick = min($products->count(), $max);

        $requested = max($minPick, rand($min, $max));
        $requested = min($requested, $products->count());

        if ($requested <= 0) {
            return collect();
        }

        $selection = $products->random($requested);

        return Collection::wrap($selection);
    }
}
