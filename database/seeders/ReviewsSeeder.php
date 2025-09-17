<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

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

        // Ensure products exist
        $productIds = \App\Models\Product::query()->pluck('id')->all();
        if (empty($productIds)) {
            \App\Models\Product::factory()->count(20)->create();
            $productIds = \App\Models\Product::query()->pluck('id')->all();
        }

        // Prefer real customers (non-admin users if column exists), otherwise fall back to all users
        $customerQuery = \App\Models\User::query();
        try {
            $customerQuery->where('is_admin', false);
        } catch (\Throwable $e) {
            // Column may not exist yet in some environments
        }

        $customerIds = $customerQuery->pluck('id')->all();
        if (empty($customerIds)) {
            // Minimal fallback if no users exist at all
            \App\Models\User::factory()->count(100)->create();
            $customerIds = \App\Models\User::query()->pluck('id')->all();
        }

        // Target exactly 1000 total reviews across all products/locales
        $targetTotal = (int) env('REVIEWS_SEED_TOTAL', 1000);
        $batchSize = 1000;

        DB::transaction(function () use ($locales, $productIds, $customerIds, $targetTotal, $batchSize): void {
            $now = Carbon::now();
            $currentMonthStart = $now->copy()->startOfMonth();
            $previousMonthStart = $now->copy()->subMonth()->startOfMonth();
            $previousMonthEnd = $previousMonthStart->copy()->endOfMonth();

            // Preload id => [name, email] to fill reviewer fields consistently
            $customerMeta = \App\Models\User::query()
                ->whereIn('id', $customerIds)
                ->select('id', 'name', 'email')
                ->get()
                ->keyBy('id')
                ->map(fn ($u) => ['name' => $u->name ?? 'Customer', 'email' => $u->email ?? 'customer@example.com'])
                ->all();

            $buffer = [];
            $createdCount = 0;
            foreach ($locales as $locale) {
                $faker = \Faker\Factory::create(match ($locale) {
                    'lt' => 'lt_LT',
                    'de' => 'de_DE',
                    'en' => 'en_GB',
                    'pl' => 'pl_PL',
                    'lv' => 'lv_LV',
                    'et' => 'et_EE',
                    default => 'en_GB',
                });

                foreach ($productIds as $productId) {
                    if ($createdCount >= $targetTotal) {
                        break 2;
                    }
                    // Aim to distribute reviews roughly evenly across products/locales
                    $remaining = $targetTotal - $createdCount;
                    $perProduct = max(1, (int) floor($remaining / max(count($productIds), 1)));
                    $prevCount = (int) floor($perProduct / 2);
                    $currCount = max($perProduct - $prevCount, 0);

                    // Helper to push a review row
                    $push = function (\DateTimeInterface $createdAt) use (&$buffer, $faker, $productId, $locale, $customerIds, $customerMeta): void {
                        $userId = $customerIds[array_rand($customerIds)];
                        $meta = $customerMeta[$userId] ?? ['name' => 'Customer', 'email' => 'customer@example.com'];
                        $buffer[] = [
                            'product_id' => $productId,
                            'user_id' => $userId,
                            'reviewer_name' => $meta['name'],
                            'reviewer_email' => $meta['email'],
                            'title' => $faker->realTextBetween(20, 45),
                            'content' => $faker->realTextBetween(120, 320),
                            'rating' => $faker->numberBetween(2, 5),
                            'is_approved' => $faker->boolean(85),
                            'locale' => $locale,
                            'created_at' => $createdAt,
                            'updated_at' => $createdAt,
                            'approved_at' => null,
                            'rejected_at' => null,
                            'deleted_at' => null,
                        ];
                    };

                    // Previous month
                    for ($i = 0; $i < $prevCount && $createdCount < $targetTotal; $i++) {
                        $createdAt = Carbon::createFromTimestamp(random_int($previousMonthStart->timestamp, $previousMonthEnd->timestamp));
                        $push($createdAt);
                        $createdCount++;
                    }
                    // Current month
                    for ($i = 0; $i < $currCount && $createdCount < $targetTotal; $i++) {
                        $createdAt = Carbon::createFromTimestamp(random_int($currentMonthStart->timestamp, $now->timestamp));
                        $push($createdAt);
                        $createdCount++;
                    }

                    // Flush when buffer is large
                    if (count($buffer) >= $batchSize) {
                        DB::table('reviews')->insert($buffer);
                        $buffer = [];
                    }
                }
            }

            if (! empty($buffer)) {
                DB::table('reviews')->insert($buffer);
            }
        });
    }
}
