<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(UserPreference::class)]
final class UserPreferenceModelTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a canonical user record so every test exercises the model relationship consistently.
        $this->user = User::factory()->create();
    }

    private function createPreference(array $overrides = []): UserPreference
    {
        // Delegate to the factory while forcing the user relationship so the helper stays deterministic.
        return UserPreference::factory()
            ->for($this->user)
            ->create($overrides);
    }

    public function test_it_belongs_to_a_user(): void
    {
        // Act: create a preference to exercise the belongsTo relationship loader.
        $preference = $this->createPreference();

        // Assert: the related user matches the seeded record regardless of lazy or eager loading.
        $this->assertTrue($preference->user->is($this->user));
    }

    public function test_it_exposes_expected_fillable_aliases(): void
    {
        // Act: resolve the fillable array so we can guard mass-assignment behaviour.
        $fillable = (new UserPreference)->getFillable();

        // Assert: confirm the streamlined aliases stay in sync with the documented contract.
        $this->assertSame(['user_id', 'name', 'key', 'value', 'meta'], $fillable);
    }

    public function test_it_casts_attributes_and_aliases_consistently(): void
    {
        // Arrange: freeze time so Carbon comparisons remain predictable across assertions.
        Carbon::setTestNow($now = Carbon::parse('2024-01-01 12:00:00'));

        try {
            // Act: store the record using aliases to ensure the accessors bridge to the canonical columns.
            $preference = $this->createPreference([
                'name'         => 'category',
                'key'          => 'power-tools',
                'value'        => '0.85',
                'meta'         => ['source' => 'test-suite'],
                'last_updated' => $now,
            ]);

            // Assert: value alias resolves to a float while preserving precision and metadata casting rules.
            $this->assertSame(0.85, $preference->value);
            $this->assertSame(['source' => 'test-suite'], $preference->meta);
            $this->assertInstanceOf(Carbon::class, $preference->last_updated);
            $this->assertTrue($preference->last_updated->equalTo($now));
        } finally {
            // Always release the mocked time to avoid leaking the state into other tests.
            Carbon::setTestNow();
        }
    }

    public function test_query_scope_filters_by_type(): void
    {
        // Arrange: seed a blend of preference types so the scope has multiple rows to filter.
        $this->createPreference([
            'name' => 'category',
            // Use a distinct key so the composite unique index (user_id, type, key) is never violated.
            'key' => 'workshop-tools',
        ]);
        $this->createPreference([
            'name' => 'brand',
            // Maintain variety in the dataset while preserving uniqueness for the seeded trio.
            'key' => 'artisan-collective',
        ]);
        $this->createPreference([
            'name' => 'category',
            // Provide a second unique key to keep the scenario focused on the type filtering logic.
            'key' => 'finishing-supplies',
        ]);

        // Act: query for a single type using the dedicated scope.
        $categoryPreferences = UserPreference::byType('category')->pluck('preference_type')->all();

        // Assert: only the requested type is returned and the results count matches expectations.
        $this->assertCount(2, $categoryPreferences);
        $this->assertSame(['category', 'category'], $categoryPreferences);
    }

    public function test_query_scope_filters_by_minimum_score(): void
    {
        // Arrange: persist a trio of scores so we can exercise the numeric comparison logic.
        $this->createPreference(['value' => 0.3]);
        $this->createPreference(['value' => 0.7]);
        $this->createPreference(['value' => 0.9]);

        // Act: filter using the minimum score scope boundary.
        $filteredScores = UserPreference::withMinScore(0.7)
            // Explicitly order by the primary key so the assertion remains stable regardless of SQLite iteration quirks.
            ->orderBy('id')
            ->pluck('preference_score');

        // Assert: ensure only scores meeting or exceeding the threshold are returned.
        $this->assertSame([0.7, 0.9], $filteredScores->all());
    }

    public function test_query_scope_orders_by_score_descending(): void
    {
        // Arrange: create values in an unsorted order to confirm the scope applies ordering rules.
        $this->createPreference(['value' => 0.3]);
        $this->createPreference(['value' => 0.9]);
        $this->createPreference(['value' => 0.6]);

        // Act: fetch the ordered results and pluck the score column for easier comparison.
        $orderedScores = UserPreference::orderedByScore()->pluck('preference_score')->all();

        // Assert: the scope should order from highest to lowest score.
        $this->assertSame([0.9, 0.6, 0.3], $orderedScores);
    }

    public function test_recent_scope_uses_default_window(): void
    {
        // Arrange: freeze time and seed records that straddle the default 30-day window.
        Carbon::setTestNow($now = Carbon::parse('2024-03-15 10:00:00'));

        try {
            $this->createPreference(['last_updated' => $now->copy()->subDays(40)]);
            $recent = $this->createPreference(['last_updated' => $now->copy()->subDays(10)]);
            $latest = $this->createPreference(['last_updated' => $now]);

            // Act: execute the scope without parameters to rely on the default threshold.
            $results = UserPreference::recent()->get();

            // Assert: only records within the 30-day window are returned and ordered by recency.
            $this->assertCount(2, $results);
            $this->assertTrue($results->contains($recent));
            $this->assertTrue($results->contains($latest));
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_recent_scope_accepts_custom_window(): void
    {
        // Arrange: freeze time to make the date arithmetic deterministic for the assertions.
        Carbon::setTestNow($now = Carbon::parse('2024-03-15 10:00:00'));

        try {
            $this->createPreference(['last_updated' => $now->copy()->subDays(20)]);
            $recent = $this->createPreference(['last_updated' => $now->copy()->subDays(5)]);
            $latest = $this->createPreference(['last_updated' => $now]);

            // Act: filter by a tighter window so only the newest two records survive.
            $results = UserPreference::recent(10)->get();

            // Assert: verify the expected models are returned and that the older record is excluded.
            $this->assertCount(2, $results);
            $this->assertTrue($results->contains($recent));
            $this->assertTrue($results->contains($latest));
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_metadata_alias_stores_complex_payloads(): void
    {
        // Arrange: prepare a nested structure that mirrors analytics payloads.
        $payload = [
            'source'      => 'purchase_history',
            'frequency'   => 'high',
            'preferences' => ['category' => 'electronics', 'brand' => 'apple'],
            'timestamps'  => ['first_seen' => now()->toISOString()],
        ];

        // Act: store the metadata under the alias key to validate JSON casting behaviour.
        $preference = $this->createPreference(['meta' => $payload]);

        // Assert: both alias and canonical accessors should return the array unchanged.
        $this->assertSame($payload, $preference->meta);
        $this->assertSame($payload, $preference->metadata);
    }

    public function test_score_rounding_is_limited_to_six_decimal_places(): void
    {
        // Act: persist a value that includes more than six decimals to exercise the normalisation helper.
        $preference = $this->createPreference(['value' => 0.987654321]);

        // Assert: the stored score is rounded to six decimals both via the alias and the canonical column.
        $this->assertSame(0.987654, $preference->value);
        $this->assertSame(0.987654, $preference->preference_score);
    }

    public function test_factory_produces_sensible_defaults(): void
    {
        // Act: create a record using the stock factory to ensure it hydrates the expected attributes.
        $preference = $this->createPreference();

        // Assert: confirm the defaults look reasonable for downstream analytics and recommendation engines.
        $this->assertSame($this->user->id, $preference->user_id);
        $this->assertNotNull($preference->name);
        $this->assertNotNull($preference->key);
        $this->assertIsFloat($preference->value);
        $this->assertGreaterThanOrEqual(0.0, $preference->value);
        $this->assertLessThanOrEqual(1.0, $preference->value);
    }

    public function test_fill_translates_legacy_column_names(): void
    {
        // Arrange: instantiate the model manually to exercise the overridden fill logic.
        $preference = new UserPreference;

        // Act: fill using the legacy column names and then persist the model.
        $preference->fill([
            'user_id'          => $this->user->id,
            'preference_type'  => 'brand',
            'preference_key'   => 'color',
            'preference_score' => '0.45',
            'metadata'         => ['hex' => '#ffffff'],
            'last_updated'     => Carbon::parse('2024-02-01 08:00:00'),
        ])->save();

        // Assert: alias accessors should surface the translated values exactly as consumers expect.
        $this->assertSame('brand', $preference->name);
        $this->assertSame('color', $preference->key);
        $this->assertSame(0.45, $preference->value);
        $this->assertSame(['hex' => '#ffffff'], $preference->meta);
    }

    public function test_metadata_can_be_null(): void
    {
        // Act: explicitly store a null payload to verify the accessor does not coerce it to an empty array.
        $preference = $this->createPreference(['meta' => null]);

        // Assert: both metadata access points should reflect the null state faithfully.
        $this->assertNull($preference->meta);
        $this->assertNull($preference->metadata);
    }

    public function test_metadata_can_be_an_empty_array(): void
    {
        // Act: persist an empty array so we can differentiate between missing data and an intentionally empty payload.
        $preference = $this->createPreference(['meta' => []]);

        // Assert: empty arrays are preserved verbatim rather than being cast to null or other types.
        $this->assertSame([], $preference->meta);
        $this->assertSame([], $preference->metadata);
    }
}
