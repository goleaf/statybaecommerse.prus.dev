<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductHistory;
use App\Models\User;
use Illuminate\Database\Seeder;

final class ProductHistorySeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::query()->with('brand')->limit(20)->get();
        if ($products->isEmpty()) {
            $products = Product::factory()->count(10)->create();
        }

        $users = User::factory()->count(10)->create();

        $products->each(function (Product $product) use ($users): void {
            $userSample = $users->random(fake()->numberBetween(1, $users->count()));
            $productUsers = $userSample instanceof User ? collect([$userSample]) : collect($userSample);

            $this->seedHistoriesForProduct($product, $productUsers->unique('id'));
        });
    }

    private function seedHistoriesForProduct(Product $product, $users): void
    {
        $pivotMetadata = [
            'source' => 'factory_seed',
            'currency' => 'EUR',
        ];

        $product->histories()->delete();
        $product->histories()->saveMany(
            ProductHistory::factory()
                ->count(fake()->numberBetween(8, 12))
                ->for($product)
                ->state(fn () => [
                    'user_id' => $users->random()->id,
                    'metadata' => array_merge($pivotMetadata, ['event_id' => fake()->uuid()]),
                ])
                ->make()
        );
    }
}
