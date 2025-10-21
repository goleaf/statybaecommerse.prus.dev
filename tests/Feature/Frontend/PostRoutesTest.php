<?php

declare(strict_types=1);

namespace Tests\Feature\Frontend;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PostRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_posts_index_route_resolves_without_namespace_errors(): void
    {
        Post::factory()->published()->create();

        $response = $this->get('/posts');

        $response->assertOk();
        $response->assertViewIs('posts.index');
    }
}
