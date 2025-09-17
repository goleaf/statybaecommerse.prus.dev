<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

final class PostSeeder extends Seeder
{
    public function run(): void
    {
        // Get or create a user for posts
        $user = User::first() ?? User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);

        // Create sample posts
        Post::factory()->count(20)->create([
            'user_id' => $user->id,
        ]);

        // Create some featured posts
        Post::factory()->featured()->published()->count(5)->create([
            'user_id' => $user->id,
        ]);

        // Create some pinned posts
        Post::factory()->pinned()->published()->count(3)->create([
            'user_id' => $user->id,
        ]);

        // Create some draft posts
        Post::factory()->draft()->count(3)->create([
            'user_id' => $user->id,
        ]);

        // Create some archived posts
        Post::factory()->archived()->count(2)->create([
            'user_id' => $user->id,
        ]);
    }
}
