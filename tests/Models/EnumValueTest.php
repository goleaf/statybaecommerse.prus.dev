<?php

declare(strict_types=1);

namespace Tests\Models;

use App\Models\EnumValue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * EnumValueTest
 *
 * Verifies the EnumValue model helpers, scopes and utilities to ensure
 * stable behaviour throughout the admin experience.
 */
final class EnumValueTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_filters_active_records(): void
    {
        // Arrange: create an active and inactive enum value.
        EnumValue::factory()->active()->create();
        EnumValue::factory()->inactive()->create();

        // Act: fetch active enum values via the scope.
        $results = EnumValue::query()->active()->get();

        // Assert: only the active record is returned.
        $this->assertCount(1, $results);
        $this->assertTrue($results->first()->is_active);
    }

    #[Test]
    public function it_filters_default_records(): void
    {
        // Arrange: create a default and a non-default enum value.
        EnumValue::factory()->default()->create();
        EnumValue::factory()->create(['is_default' => false]);

        // Act: fetch default enum values via the scope.
        $results = EnumValue::query()->default()->get();

        // Assert: only the default record is returned.
        $this->assertCount(1, $results);
        $this->assertTrue($results->first()->is_default);
    }

    #[Test]
    public function it_filters_by_type(): void
    {
        // Arrange: create records for two different types.
        EnumValue::factory()->create(['type' => 'first_type']);
        EnumValue::factory()->create(['type' => 'second_type']);

        // Act: fetch records filtered by the first type.
        $results = EnumValue::query()->byType('first_type')->get();

        // Assert: only the expected type is present.
        $this->assertCount(1, $results);
        $this->assertSame('first_type', $results->first()->type);
    }

    #[Test]
    public function it_orders_records_by_sort_order_then_name(): void
    {
        // Arrange: deliberately craft records to assert deterministic ordering.
        $third = EnumValue::factory()->create([
            'name'       => 'Gamma',
            'sort_order' => 2,
        ]);
        $second = EnumValue::factory()->create([
            'name'       => 'Beta',
            'sort_order' => 1,
        ]);
        $first = EnumValue::factory()->create([
            'name'       => 'Alpha',
            'sort_order' => 1,
        ]);

        // Act: obtain the ordered collection.
        $ordered = EnumValue::query()->ordered()->get();

        // Assert: sorting respects sort_order first, then the name.
        $this->assertSame([
            $first->getKey(),
            $second->getKey(),
            $third->getKey(),
        ], $ordered->pluck('id')->all());
    }

    #[Test]
    public function it_orders_records_by_name_only(): void
    {
        // Arrange: craft records with the same sort order to exercise the dedicated scope.
        $third = EnumValue::factory()->create(['name' => 'Charlie', 'sort_order' => 5]);
        $first = EnumValue::factory()->create(['name' => 'Alpha', 'sort_order' => 5]);
        $second = EnumValue::factory()->create(['name' => 'Bravo', 'sort_order' => 5]);

        // Act: order strictly by the name column.
        $ordered = EnumValue::query()->orderedByName()->get();

        // Assert: verify alphabetical ordering by name.
        $this->assertSame([
            $first->getKey(),
            $second->getKey(),
            $third->getKey(),
        ], $ordered->pluck('id')->all());
    }

    #[Test]
    public function it_returns_usage_count_from_metadata(): void
    {
        // Arrange: create a record with a numeric usage count metadata entry.
        $enum = EnumValue::factory()->create([
            'metadata' => ['usage_count' => 42],
        ]);

        // Act: retrieve the computed usage count accessor.
        $usage = $enum->usage_count;

        // Assert: ensure the accessor maps the metadata correctly.
        $this->assertSame(42, $usage);
    }

    #[Test]
    public function it_handles_invalid_metadata_usage_count(): void
    {
        // Arrange: craft a record with malformed metadata data.
        $enum = EnumValue::factory()->create([
            'metadata' => 'invalid-json',
        ]);

        // Act: retrieve the computed usage count accessor.
        $usage = $enum->usage_count;

        // Assert: invalid payloads fall back to zero.
        $this->assertSame(0, $usage);
    }

    #[Test]
    public function it_activates_and_deactivates_values(): void
    {
        // Arrange: create an inactive record.
        $enum = EnumValue::factory()->inactive()->create();

        // Act & Assert: flipping the flags persists the updated state.
        $this->assertTrue($enum->activate());
        $this->assertTrue($enum->fresh()->is_active);

        $this->assertTrue($enum->deactivate());
        $this->assertFalse($enum->fresh()->is_active);
    }

    #[Test]
    public function it_sets_default_and_clears_existing_defaults(): void
    {
        // Arrange: ensure two values of the same type exist with one default already set.
        $type = 'shared-type';
        $first = EnumValue::factory()->create(['type' => $type, 'is_default' => true]);
        $second = EnumValue::factory()->create(['type' => $type, 'is_default' => false]);

        // Act: set the second enum value as the new default.
        $second->setAsDefault();

        // Assert: only the second record should now be marked as default.
        $this->assertFalse($first->fresh()->is_default);
        $this->assertTrue($second->fresh()->is_default);
    }

    #[Test]
    public function it_duplicates_records_with_reset_state(): void
    {
        // Arrange: craft a source enum value with metadata state.
        $enum = EnumValue::factory()->create([
            'key'        => 'original',
            'is_default' => true,
            'metadata'   => ['usage_count' => 9, 'extra' => 'info'],
        ]);

        // Act: duplicate the enum value using the helper.
        $duplicate = $enum->duplicate();

        // Assert: ensure uniqueness, metadata reset and persisted copy.
        $this->assertNotSame($enum->getKey(), $duplicate->getKey());
        $this->assertSame('original_copy', $duplicate->key);
        $this->assertFalse($duplicate->is_default);
        $this->assertSame(0, $duplicate->metadata['usage_count']);
        $this->assertSame('info', $duplicate->metadata['extra']);
    }

    #[Test]
    public function it_returns_types_with_dynamic_entries(): void
    {
        // Arrange: insert a custom type that does not exist in the defaults.
        EnumValue::factory()->create(['type' => 'custom_type']);

        // Act: fetch the types list.
        $types = EnumValue::getTypes();

        // Assert: the list contains both default and dynamic types.
        $this->assertArrayHasKey('custom_type', $types);
        $this->assertSame('Custom Type', $types['custom_type']);
    }

    #[Test]
    public function it_returns_values_by_type_only_for_active_records(): void
    {
        // Arrange: populate active and inactive values for a given type.
        $type = 'order_status';
        EnumValue::factory()->active()->create(['type' => $type, 'key' => 'pending', 'value' => 'Pending']);
        EnumValue::factory()->inactive()->create(['type' => $type, 'key' => 'cancelled', 'value' => 'Cancelled']);

        // Act: retrieve the value map.
        $values = EnumValue::getValuesByType($type);

        // Assert: only the active key/value pair is returned.
        $this->assertSame(['pending' => 'Pending'], $values);
    }

    #[Test]
    public function it_returns_the_default_value_key(): void
    {
        // Arrange: create enum values with only one default.
        $type = 'payment_status';
        EnumValue::factory()->create(['type' => $type, 'key' => 'paid', 'is_default' => true]);
        EnumValue::factory()->create(['type' => $type, 'key' => 'pending', 'is_default' => false]);

        // Act: fetch the default key via the helper.
        $defaultKey = EnumValue::getDefaultValue($type);

        // Assert: confirm the selected key.
        $this->assertSame('paid', $defaultKey);
    }

    #[Test]
    public function it_cleans_up_unused_records(): void
    {
        // Arrange: freeze time so that the retention threshold is deterministic.
        Carbon::setTestNow('2024-01-01 00:00:00');

        // Create candidates across the threshold with varying usage counts.
        $deletable = EnumValue::factory()->create([
            'metadata'   => ['usage_count' => 0],
            'created_at' => Carbon::now()->subMonths(7),
        ]);
        $retainedBecauseUsed = EnumValue::factory()->create([
            'metadata'   => ['usage_count' => 3],
            'created_at' => Carbon::now()->subMonths(7),
        ]);
        $retainedBecauseRecent = EnumValue::factory()->create([
            'metadata'   => ['usage_count' => 0],
            'created_at' => Carbon::now()->subMonths(3),
        ]);

        // Act: run the cleanup and capture the deleted count.
        $deletedCount = EnumValue::cleanupUnused();

        // Assert: only the unused and stale record is deleted.
        $this->assertSame(1, $deletedCount);
        $this->assertDatabaseMissing($deletable->getTable(), ['id' => $deletable->getKey()]);
        $this->assertDatabaseHas($retainedBecauseUsed->getTable(), ['id' => $retainedBecauseUsed->getKey()]);
        $this->assertDatabaseHas($retainedBecauseRecent->getTable(), ['id' => $retainedBecauseRecent->getKey()]);

        // Reset the mocked time to avoid leaking state.
        Carbon::setTestNow();
    }
}
