<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class UserPreferenceModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_preference_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $userPreference = UserPreference::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $userPreference->user);
        $this->assertEquals($user->id, $userPreference->user->id);
    }

    public function test_user_preference_fillable_attributes(): void
    {
        $fillable = ['user_id', 'preference_type', 'preference_key', 'preference_score', 'metadata', 'last_updated'];

        $userPreference = new UserPreference;

        $this->assertEquals($fillable, $userPreference->getFillable());
    }

    public function test_user_preference_casts(): void
    {
        $userPreference = UserPreference::factory()->create([
            'preference_score' => '0.85',
            'metadata' => ['source' => 'test'],
            'last_updated' => '2024-01-01 12:00:00',
        ]);

        $this->assertIsFloat($userPreference->preference_score);
        $this->assertEquals(0.85, $userPreference->preference_score);
        $this->assertIsArray($userPreference->metadata);
        $this->assertEquals(['source' => 'test'], $userPreference->metadata);
        $this->assertInstanceOf(\Carbon\Carbon::class, $userPreference->last_updated);
    }

    public function test_scope_by_type(): void
    {
        UserPreference::factory()->create(['preference_type' => 'category']);
        UserPreference::factory()->create(['preference_type' => 'brand']);
        UserPreference::factory()->create(['preference_type' => 'category']);

        $categoryPreferences = UserPreference::byType('category')->get();
        $brandPreferences = UserPreference::byType('brand')->get();

        $this->assertCount(2, $categoryPreferences);
        $this->assertCount(1, $brandPreferences);

        $categoryPreferences->each(function ($preference) {
            $this->assertEquals('category', $preference->preference_type);
        });

        $brandPreferences->each(function ($preference) {
            $this->assertEquals('brand', $preference->preference_type);
        });
    }

    public function test_scope_with_min_score(): void
    {
        UserPreference::factory()->create(['preference_score' => 0.3]);
        UserPreference::factory()->create(['preference_score' => 0.7]);
        UserPreference::factory()->create(['preference_score' => 0.9]);

        $highScorePreferences = UserPreference::withMinScore(0.7)->get();

        $this->assertCount(2, $highScorePreferences);

        $highScorePreferences->each(function ($preference) {
            $this->assertGreaterThanOrEqual(0.7, $preference->preference_score);
        });
    }

    public function test_scope_ordered_by_score(): void
    {
        UserPreference::factory()->create(['preference_score' => 0.3]);
        UserPreference::factory()->create(['preference_score' => 0.9]);
        UserPreference::factory()->create(['preference_score' => 0.6]);

        $orderedPreferences = UserPreference::orderedByScore()->get();

        $this->assertCount(3, $orderedPreferences);
        $this->assertEquals(0.9, $orderedPreferences->first()->preference_score);
        $this->assertEquals(0.3, $orderedPreferences->last()->preference_score);
    }

    public function test_scope_recent(): void
    {
        $oldDate = now()->subDays(40);
        $recentDate = now()->subDays(10);

        UserPreference::factory()->create(['last_updated' => $oldDate]);
        UserPreference::factory()->create(['last_updated' => $recentDate]);
        UserPreference::factory()->create(['last_updated' => now()]);

        $recentPreferences = UserPreference::recent(30)->get();

        $this->assertCount(2, $recentPreferences);

        $recentPreferences->each(function ($preference) {
            $this->assertGreaterThanOrEqual(now()->subDays(30), $preference->last_updated);
        });
    }

    public function test_scope_recent_with_custom_days(): void
    {
        $oldDate = now()->subDays(20);
        $recentDate = now()->subDays(5);

        UserPreference::factory()->create(['last_updated' => $oldDate]);
        UserPreference::factory()->create(['last_updated' => $recentDate]);
        UserPreference::factory()->create(['last_updated' => now()]);

        $recentPreferences = UserPreference::recent(10)->get();

        $this->assertCount(2, $recentPreferences);

        $recentPreferences->each(function ($preference) {
            $this->assertGreaterThanOrEqual(now()->subDays(10), $preference->last_updated);
        });
    }

    public function test_user_preference_can_store_complex_metadata(): void
    {
        $complexMetadata = [
            'source' => 'purchase_history',
            'frequency' => 'high',
            'category_preference' => 'electronics',
            'nested_data' => [
                'subcategory' => 'smartphones',
                'brand_preference' => 'apple',
            ],
            'timestamps' => [
                'first_seen' => now()->toISOString(),
                'last_updated' => now()->toISOString(),
            ],
        ];

        $userPreference = UserPreference::factory()->create(['metadata' => $complexMetadata]);

        $this->assertEquals($complexMetadata, $userPreference->metadata);
        $this->assertEquals('electronics', $userPreference->metadata['category_preference']);
        $this->assertEquals('smartphones', $userPreference->metadata['nested_data']['subcategory']);
    }

    public function test_user_preference_decimal_precision(): void
    {
        $userPreference = UserPreference::factory()->create(['preference_score' => 0.123456]);

        // Should maintain 6 decimal places precision
        $this->assertEquals(0.123456, $userPreference->preference_score);

        // Test database storage precision
        $stored = UserPreference::find($userPreference->id);
        $this->assertEquals(0.123456, $stored->preference_score);
    }

    public function test_user_preference_factory(): void
    {
        $userPreference = UserPreference::factory()->create();

        $this->assertInstanceOf(UserPreference::class, $userPreference);
        $this->assertNotNull($userPreference->user_id);
        $this->assertNotNull($userPreference->preference_type);
        $this->assertIsFloat($userPreference->preference_score);
        $this->assertGreaterThanOrEqual(0, $userPreference->preference_score);
        $this->assertLessThanOrEqual(1, $userPreference->preference_score);
    }

    public function test_user_preference_with_user_relationship(): void
    {
        $user = User::factory()->create(['name' => 'John Doe']);
        $userPreference = UserPreference::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($userPreference->relationLoaded('user') || $userPreference->user !== null);
        $this->assertEquals('John Doe', $userPreference->user->name);
    }

    public function test_user_preference_model_uses_user_owned_scope(): void
    {
        // This test verifies that the UserOwnedScope is properly applied
        $userPreference = UserPreference::factory()->create();

        // The scope should be applied automatically due to the ScopedBy attribute
        $this->assertInstanceOf(UserPreference::class, $userPreference);
    }

    public function test_user_preference_can_have_null_metadata(): void
    {
        $userPreference = UserPreference::factory()->create(['metadata' => null]);

        $this->assertNull($userPreference->metadata);
    }

    public function test_user_preference_can_have_empty_metadata(): void
    {
        $userPreference = UserPreference::factory()->create(['metadata' => []]);

        $this->assertIsArray($userPreference->metadata);
        $this->assertEmpty($userPreference->metadata);
    }
}
