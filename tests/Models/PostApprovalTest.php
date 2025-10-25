<?php declare(strict_types=1);

namespace Tests\Models;

use App\Models\Post;
use App\Models\PostApproval;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

final class PostApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_approval_has_expected_fillable_and_casts(): void
    {
        // Instantiate the model so that we can inspect its configuration.
        $model = new PostApproval();

        // Guarding against mass assignment is handled through explicit fillable declarations.
        self::assertSame([
            'post_id',
            'user_id',
            'decision',
            'notes',
            'decided_at',
        ], $model->getFillable());

        // Casting definitions should coerce foreign keys and timestamps to native types.
        $casts = $model->getCasts();
        self::assertSame('integer', $casts['post_id'] ?? null);
        self::assertSame('integer', $casts['user_id'] ?? null);
        self::assertSame('datetime', $casts['decided_at'] ?? null);
    }

    public function test_post_and_user_relationships_resolve_models(): void
    {
        // Create the related post and moderator so we have targets for the foreign keys.
        $post = Post::factory()->create();
        $user = User::factory()->create();

        // Persist an approval record referencing the freshly created models.
        $approval = PostApproval::query()->create([
            'post_id' => $post->getKey(),
            'user_id' => $user->getKey(),
            'decision' => 'approved',
            'notes' => 'Looks good to publish.',
            'decided_at' => now(),
        ])->fresh();

        // Relationship accessors should hydrate the expected Eloquent models.
        self::assertTrue($post->is($approval->post));
        self::assertTrue($user->is($approval->user));

        // The decided_at attribute should be cast to a Carbon instance for convenience.
        self::assertInstanceOf(Carbon::class, $approval->decided_at);
    }
}
