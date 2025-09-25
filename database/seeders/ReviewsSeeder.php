<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ReviewsSeeder extends Seeder
{
    public function run(): void
    {
        // Resolve supported locales
        $localesConfig = config('app.supported_locales', 'en');
        $locales = is_array($localesConfig)
            ? array_values(array_filter(array_map('trim', $localesConfig)))
            : array_values(array_filter(array_map('trim', explode(',', (string) $localesConfig))));
        if (empty($locales)) {
            $locales = ['en'];
        }

        // Ensure products exist using factory relationships
        $products = Product::query()->get();
        if ($products->isEmpty()) {
            $products = Product::factory()->count(20)->create();
        }

        // Prefer real customers (non-admin users if column exists), otherwise fall back to all users
        $customerQuery = User::query();
        try {
            $customerQuery->where('is_admin', false);
        } catch (\Throwable $e) {
            // Column may not exist yet in some environments
        }

        $customers = $customerQuery->get();
        if ($customers->isEmpty()) {
            // Minimal fallback if no users exist at all
            $customers = User::factory()->count(100)->create();
        }

        // Target exactly 1000 total reviews across all products/locales
        $targetTotal = (int) env('REVIEWS_SEED_TOTAL', 1000);
        $now = Carbon::now();
        $currentMonthStart = $now->copy()->startOfMonth();
        $previousMonthStart = $now->copy()->subMonth()->startOfMonth();
        $previousMonthEnd = $previousMonthStart->copy()->endOfMonth();

        $createdCount = 0;
        foreach ($locales as $locale) {
            foreach ($products as $product) {
                if ($createdCount >= $targetTotal) {
                    break 2;
                }

                // Aim to distribute reviews roughly evenly across products/locales
                $remaining = $targetTotal - $createdCount;
                $perProduct = max(1, (int) floor($remaining / max($products->count(), 1)));
                $prevCount = (int) floor($perProduct / 2);
                $currCount = max($perProduct - $prevCount, 0);

                // Previous month reviews
                for ($i = 0; $i < $prevCount && $createdCount < $targetTotal; $i++) {
                    $createdAt = Carbon::createFromTimestamp(random_int($previousMonthStart->timestamp, $previousMonthEnd->timestamp));

                    Review::factory()
                        ->for($product)
                        ->for($customers->random())
                        ->create([
                            'locale' => $locale,
                            'created_at' => $createdAt,
                            'updated_at' => $createdAt,
                        ]);

                    $createdCount++;
                }

                // Current month reviews
                for ($i = 0; $i < $currCount && $createdCount < $targetTotal; $i++) {
                    $createdAt = Carbon::createFromTimestamp(random_int($currentMonthStart->timestamp, $now->timestamp));

                    Review::factory()
                        ->for($product)
                        ->for($customers->random())
                        ->create([
                            'locale' => $locale,
                            'created_at' => $createdAt,
                            'updated_at' => $createdAt,
                        ]);

                    $createdCount++;
                }
            }
        }
    }
}
