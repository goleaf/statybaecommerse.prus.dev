<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use App\Models\UserProductInteraction;
use Illuminate\Database\Seeder;

/**
 * UserProductInteractionSeeder
 *
 * Seeds the database with realistic user product interaction data for testing and demonstration purposes.
 */
final class UserProductInteractionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::factory()->count(20)->create();

        Product::factory()->count(50)->create()->each(static function (Product $product) use ($users): void {
            $users->random(fake()->numberBetween(3, 7))->each(function (User $user) use ($product): void {
                UserProductInteraction::factory()
                    ->count(fake()->numberBetween(2, 6))
                    ->for($user)
                    ->for($product)
                    ->create();
            });
        });

        $this->command->info('UserProductInteractionSeeder completed successfully.');
    }
}
