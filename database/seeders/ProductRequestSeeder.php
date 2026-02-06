<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductRequest;
use App\Models\User;

final class ProductRequestSeeder extends BaseSeeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure we have some products and users to relate to.
        if (Product::count() === 0) {
            Product::factory()->count(10)->create();
        }

        if (User::count() === 0) {
            User::factory()->count(5)->create();
        }

        // Create a variety of product requests.
        ProductRequest::factory()
            ->count(20)
            ->create();

        // Create some specifically pending ones.
        ProductRequest::factory()
            ->count(5)
            ->pending()
            ->create();

        // Create some specifically completed ones.
        ProductRequest::factory()
            ->count(5)
            ->completed()
            ->create();
    }
}
