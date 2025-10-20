<?php

declare(strict_types=1);

namespace Tests\Feature\Frontend;

use App\Models\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CollectionRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_collections_index_route_resolves(): void
    {
        $this
            ->get('/collections')
            ->assertStatus(200);
    }

    public function test_collections_show_route_resolves(): void
    {
        $collection = Collection::factory()->create();

        $this
            ->get(route('frontend.collections.show', $collection))
            ->assertStatus(200);
    }
}
