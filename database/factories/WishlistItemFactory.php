<?php declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use App\Models\UserWishlist;
use App\Models\WishlistItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class WishlistItemFactory extends Factory
{
    protected $model = WishlistItem::class;

    public function definition(): array
    {
        return [
            'wishlist_id' => UserWishlist::factory(),
            'product_id' => Product::factory(),
        ];
    }
}
