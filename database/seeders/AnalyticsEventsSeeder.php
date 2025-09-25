<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AnalyticsEvent;
use App\Models\Brand;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

final class AnalyticsEventsSeeder extends Seeder
{
    public function run(): void
    {
        $products = $this->ensurePublishedProducts();

        if ($products->isEmpty()) {
            return;
        }

        $users = $this->ensureSeedUsers();

        $products->each(function (Product $product) use ($users): void {
            AnalyticsEvent::query()->where('session_id', 'like', "seed-{$product->getKey()}-%")->delete();

            foreach (range(1, 15) as $index) {
                $user = $users[$index % $users->count()];

                AnalyticsEvent::factory()
                    ->productView()
                    ->for($user, 'user')
                    ->state([
                        'session_id' => "seed-{$product->getKey()}-view-{$index}",
                        'properties' => [
                            'product_id' => $product->getKey(),
                            'product_name' => $product->name,
                            'product_sku' => $product->sku,
                            'brand' => $product->brand?->name,
                        ],
                        'trackable_type' => Product::class,
                        'trackable_id' => $product->getKey(),
                        'created_at' => now()->subDays($index % 30),
                    ])
                    ->create();
            }

            foreach (range(1, 5) as $index) {
                $user = $users[$index % $users->count()];

                AnalyticsEvent::factory()
                    ->addToCart()
                    ->for($user, 'user')
                    ->state([
                        'session_id' => "seed-{$product->getKey()}-cart-{$index}",
                        'properties' => [
                            'product_id' => $product->getKey(),
                            'product_name' => $product->name,
                            'quantity' => 1,
                            'price' => $product->price,
                        ],
                        'trackable_type' => Product::class,
                        'trackable_id' => $product->getKey(),
                        'created_at' => now()->subDays($index % 7),
                    ])
                    ->create();
            }
        });
    }

    /**
     * Ensure we have a consistent set of published products to track.
     */
    private function ensurePublishedProducts(): Collection
    {
        $products = Product::query()->where('status', 'published')->where('is_visible', true)->limit(10)->get();

        if ($products->count() >= 10) {
            return $products;
        }

        $missing = 10 - $products->count();

        Product::factory()
            ->count($missing)
            ->state([
                'status' => 'published',
                'is_visible' => true,
                'published_at' => now(),
            ])
            ->for(Brand::factory(), 'brand')
            ->create();

        return Product::query()->where('status', 'published')->where('is_visible', true)->limit(10)->get();
    }

    /**
     * Ensure we have a stable collection of users for seeding events.
     */
    private function ensureSeedUsers(): Collection
    {
        $users = User::query()->limit(5)->get();

        if ($users->count() < 5) {
            $additional = User::factory()
                ->count(5 - $users->count())
                ->state([
                    'preferred_locale' => 'lt',
                ])
                ->create();

            $users = $users->merge($additional);
        }

        return $users->values();
    }
}
