<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Post;
use App\Models\PostApproval;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PostApproval>
 */
final class PostApprovalFactory extends Factory
{
    /**
     * @var class-string<PostApproval>
     */
    protected $model = PostApproval::class;

    public function definition(): array
    {
        // Generate a consistent approval payload tied to related post and moderator fixtures.
        return [
            'post_id'    => Post::factory(),
            'user_id'    => User::factory(),
            'decision'   => 'approved',
            'notes'      => fake()->sentence(),
            'decided_at' => now(),
        ];
    }
}
