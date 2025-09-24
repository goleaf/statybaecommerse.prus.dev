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
        $users = User::limit(20)->get();
        $products = Product::limit(50)->get();

        if ($users->isEmpty() || $products->isEmpty()) {
            $this->command->warn('No users or products found. Please run UserSeeder and ProductSeeder first.');

            return;
        }

        $interactionTypes = ['view', 'click', 'add_to_cart', 'purchase', 'review', 'share'];

        foreach ($users as $user) {
            // Create 5-15 interactions per user
            $interactionCount = fake()->numberBetween(5, 15);

            for ($i = 0; $i < $interactionCount; $i++) {
                $product = $products->random();
                $interactionType = fake()->randomElement($interactionTypes);

                // Check if interaction already exists
                $existingInteraction = UserProductInteraction::where('user_id', $user->id)
                    ->where('product_id', $product->id)
                    ->where('interaction_type', $interactionType)
                    ->first();

                if ($existingInteraction) {
                    // Increment existing interaction
                    $existingInteraction->incrementInteraction(
                        $interactionType === 'review' ? fake()->randomFloat(1, 1, 5) : null
                    );
                } else {
                    // Create new interaction
                    UserProductInteraction::create([
                        'user_id' => $user->id,
                        'product_id' => $product->id,
                        'interaction_type' => $interactionType,
                        'rating' => $interactionType === 'review' ? fake()->randomFloat(1, 1, 5) : null,
                        'count' => fake()->numberBetween(1, 10),
                        'first_interaction' => fake()->dateTimeBetween('-6 months', '-1 month'),
                        'last_interaction' => fake()->dateTimeBetween('-1 month', 'now'),
                    ]);
                }
            }
        }

        $this->command->info('UserProductInteractionSeeder completed successfully.');
    }
}
