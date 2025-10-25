<?php

declare(strict_types=1);

namespace Tests\Models;

use App\Models\Campaign;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CampaignTest extends TestCase
{
    use RefreshDatabase;

    public function test_scope_ordered_by_name_sorts_campaigns_alphabetically(): void
    {
        // Arrange: seed campaigns with deliberately unordered names.
        $alpha = Campaign::factory()->create(['name' => 'Alpha Launch']);
        $echo = Campaign::factory()->create(['name' => 'Echo Drive']);
        $zulu = Campaign::factory()->create(['name' => 'Zulu Adventures']);

        // Act: run the ordered scope and capture the resulting ordered names.
        $orderedNames = Campaign::query()
            ->orderedByName()
            ->pluck('name')
            ->all();

        // Assert: confirm that campaigns are returned in ascending alphabetical order.
        $this->assertSame([
            $alpha->name,
            $echo->name,
            $zulu->name,
        ], $orderedNames);
    }
}
