<?php declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;

class ReviewsSeeder extends Seeder
{
    public function run(): void
    {
        $localesConfig = config('app.supported_locales', 'en');
        $locales = is_array($localesConfig)
            ? array_values(array_filter(array_map('trim', $localesConfig)))
            : array_values(array_filter(array_map('trim', explode(',', (string) $localesConfig))));

        if (empty($locales)) {
            $locales = ['en'];
        }

        $productIds = \App\Models\Product::query()->pluck('id')->all();
        if (empty($productIds)) {
            \App\Models\Product::factory()->count(20)->create();
            $productIds = \App\Models\Product::query()->pluck('id')->all();
        }

        $userIds = \App\Models\User::query()->pluck('id')->all();
        if (empty($userIds)) {
            \App\Models\User::factory()->count(10)->create();
            $userIds = \App\Models\User::query()->pluck('id')->all();
        }

        $reviewsPerProduct = 6;
        $fakerByLocale = [];

        DB::transaction(function () use ($locales, $productIds, $userIds, $reviewsPerProduct, &$fakerByLocale): void {
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
                $fakerByLocale[$locale] = $faker;

                foreach ($productIds as $productId) {
                    for ($i = 0; $i < (int) ceil($reviewsPerProduct / max(count($locales), 1)); $i++) {
                        $userId = Arr::random($userIds);

                        \App\Models\Review::query()->create([
                            'product_id' => $productId,
                            'user_id' => $userId,
                            'title' => $faker->realTextBetween(20, 45),
                            'content' => $faker->realTextBetween(120, 320),
                            'rating' => $faker->numberBetween(1, 5),
                            'is_approved' => $faker->boolean(80),
                            'locale' => $locale,
                        ]);
                    }
                }
            }
        });
    }
}
