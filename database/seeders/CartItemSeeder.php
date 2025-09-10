<?php declare(strict_types=1);

namespace Database\Seeders;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

final class CartItemSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::query()->inRandomOrder()->limit(10)->get();
        if ($users->isEmpty()) {
            $users = User::factory()->count(10)->create();
        }

        $products = Product::query()->inRandomOrder()->limit(50)->get();
        if ($products->isEmpty()) {
            $products = Product::factory()->count(20)->create();
        }

        $sessionIds = collect(range(1, 10))->map(fn(int $i): string => 'session-' . $i);

        $createItems = function (?User $user, Collection $productsPool, string $sessionId): void {
            $count = random_int(1, 3);
            $picked = $productsPool->random(min($count, $productsPool->count()));

            foreach ($picked as $product) {
                $quantity = random_int(1, 5);
                $unitPrice = (float) ($product->sale_price ?? $product->price ?? 0);
                $totalPrice = round($unitPrice * $quantity, 2);

                CartItem::query()->create([
                    'session_id' => $sessionId,
                    'user_id' => $user?->id,
                    'product_id' => $product->id,
                    'variant_id' => null,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                    'product_snapshot' => [
                        'name' => $product->name,
                        'price' => $unitPrice,
                        'sku' => $product->sku ?? null,
                    ],
                    'created_at' => now()->subDays(random_int(0, 10)),
                    'updated_at' => now(),
                ]);
            }
        };

        // Seed for registered users
        foreach ($users as $index => $user) {
            $sessionId = $sessionIds->get($index % $sessionIds->count());
            $createItems($user, $products, $sessionId);
        }

        // Seed some guest carts
        foreach (range(1, 5) as $i) {
            $createItems(null, $products, 'guest-' . $sessionIds->get($i % $sessionIds->count()));
        }
    }
}
