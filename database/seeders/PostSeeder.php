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

        Post::factory()
            ->for($user)
            ->count(20)
            ->create();

        Post::factory()
            ->for($user)
            ->featured()
            ->published()
            ->count(5)
            ->create();

        Post::factory()
            ->for($user)
            ->pinned()
            ->published()
            ->count(3)
            ->create();

        Post::factory()
            ->for($user)
            ->draft()
            ->count(3)
            ->create();

        Post::factory()
            ->for($user)
            ->archived()
            ->count(2)
            ->create();
    }
}
