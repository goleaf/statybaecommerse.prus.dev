<?php

declare(strict_types=1);

namespace Tests\Models;

use App\Models\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @internal
 */
final class CollectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_scope_ordered_by_name_sorts_collections_alphabetically(): void
    {
        // Arrange: create collections with deliberately unordered names for the scope to sort.
        $gamma = Collection::factory()->create(['name' => 'Gamma Builders']);
        $alpha = Collection::factory()->create(['name' => 'Alpha Architects']);
        $beta = Collection::factory()->create(['name' => 'Beta Designers']);

        // Act: fetch the collections ordered by the name scope.
        $orderedNames = Collection::query()->orderedByName()->pluck('name')->all();

        // Assert: ensure the names are returned in alphabetical order and match the created records.
        $this->assertSame([
            $alpha->name,
            $beta->name,
            $gamma->name,
        ], $orderedNames);
    }
}
