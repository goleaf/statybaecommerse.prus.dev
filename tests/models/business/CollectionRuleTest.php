<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Collection;
use App\Models\CollectionRule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CollectionRuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_factory_creates_rule(): void
    {
        // Act: create a rule via the factory to ensure seeds stay valid.
        $rule = CollectionRule::factory()->create();

        // Assert: the model exists in the database with a generated primary key.
        $this->assertInstanceOf(CollectionRule::class, $rule);
        $this->assertDatabaseHas('collection_rules', ['id' => $rule->id]);
    }

    public function test_fillable_attributes_allow_mass_assignment(): void
    {
        // Arrange: create a collection to associate the rule with.
        $collection = Collection::factory()->create();

        // Act: persist a rule using mass-assignment to validate fillable fields.
        $rule = CollectionRule::create([
            'collection_id' => $collection->id,
            'field'         => 'brand_id',
            'operator'      => 'equals',
            'value'         => 'premium',
            'position'      => 3,
            'is_active'     => false,
        ]);

        // Assert: verify all persisted attributes are correctly stored.
        $this->assertSame($collection->id, $rule->collection_id);
        $this->assertSame('brand_id', $rule->field);
        $this->assertSame('equals', $rule->operator);
        $this->assertSame('premium', $rule->value);
        $this->assertSame(3, $rule->position);
        $this->assertFalse($rule->is_active);
    }

    public function test_casts_normalise_position_and_active_flags(): void
    {
        // Act: store a rule using string values to exercise the casts.
        $rule = CollectionRule::factory()->create([
            'position'  => '5',
            'is_active' => '0',
        ]);

        // Assert: ensure the attributes are cast to the expected native types.
        $this->assertIsInt($rule->position);
        $this->assertSame(5, $rule->position);
        $this->assertIsBool($rule->is_active);
        $this->assertFalse($rule->is_active);
    }

    public function test_collection_relationship_returns_parent(): void
    {
        // Arrange: create the parent collection and associate it with the rule.
        $collection = Collection::factory()->create();
        $rule = CollectionRule::factory()->create(['collection_id' => $collection->id]);

        // Assert: the relationship resolves to the owning collection model.
        $this->assertTrue($rule->collection->is($collection));
    }

    public function test_ordered_scope_sorts_by_position(): void
    {
        // Arrange: seed two rules out of order for the same collection.
        $collection = Collection::factory()->create();
        $second = CollectionRule::factory()->create([
            'collection_id' => $collection->id,
            'position'      => 2,
        ]);
        $first = CollectionRule::factory()->create([
            'collection_id' => $collection->id,
            'position'      => 1,
        ]);

        // Act: fetch ordered results using the dedicated scope.
        $ordered = CollectionRule::forCollection($collection->id)->ordered()->get();

        // Assert: confirm the ordering respects the position column.
        $this->assertSame([$first->id, $second->id], $ordered->pluck('id')->all());
    }

    public function test_active_scope_filters_by_flag(): void
    {
        // Arrange: create both an active and inactive rule.
        $collection = Collection::factory()->create();
        $active = CollectionRule::factory()->create([
            'collection_id' => $collection->id,
            'is_active'     => true,
        ]);
        $inactive = CollectionRule::factory()->create([
            'collection_id' => $collection->id,
            'is_active'     => false,
        ]);

        // Act: apply the explicit active scope without the global ActiveScope.
        $results = CollectionRule::withoutGlobalScopes()->active()->get();

        // Assert: only the active rule should be returned.
        $this->assertTrue($results->contains($active));
        $this->assertFalse($results->contains($inactive));
    }

    public function test_for_collection_scope_limits_to_parent(): void
    {
        // Arrange: seed rules for two separate collections.
        $firstCollection = Collection::factory()->create();
        $secondCollection = Collection::factory()->create();
        $firstRule = CollectionRule::factory()->create(['collection_id' => $firstCollection->id]);
        CollectionRule::factory()->create(['collection_id' => $secondCollection->id]);

        // Act: load rules for the first collection only.
        $results = CollectionRule::forCollection($firstCollection->id)->get();

        // Assert: verify the query excludes the unrelated rule.
        $this->assertTrue($results->contains($firstRule));
        $this->assertCount(1, $results);
    }

    public function test_missing_position_is_appended_to_sequence(): void
    {
        // Arrange: seed two existing positions to simulate an established sequence.
        $collection = Collection::factory()->create();
        CollectionRule::factory()->create([
            'collection_id' => $collection->id,
            'position'      => 0,
        ]);
        CollectionRule::factory()->create([
            'collection_id' => $collection->id,
            'position'      => 3,
        ]);

        // Act: create a rule without specifying the position.
        $rule = CollectionRule::factory()->create([
            'collection_id' => $collection->id,
            'position'      => null,
        ]);

        // Assert: the position should be appended after the highest existing index.
        $this->assertSame(4, $rule->position);
    }
}
