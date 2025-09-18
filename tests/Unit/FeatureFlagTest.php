<?php declare(strict_types=1);

namespace Tests\Unit;

use App\Models\FeatureFlag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeatureFlagTest extends TestCase
{
    use RefreshDatabase;

    public function test_feature_flag_can_be_created(): void
    {
        $featureFlag = FeatureFlag::factory()->create([
            'name' => 'new_checkout_flow',
            'key' => 'new_checkout_flow',
            'description' => 'Enable new checkout flow',
            'is_enabled' => true,
        ]);

        $this->assertDatabaseHas('feature_flags', [
            'name' => 'new_checkout_flow',
            'key' => 'new_checkout_flow',
            'description' => 'Enable new checkout flow',
            'is_enabled' => true,
        ]);
    }

    public function test_feature_flag_casts_work_correctly(): void
    {
        $featureFlag = FeatureFlag::factory()->create([
            'is_enabled' => true,
            'is_global' => false,
            'created_at' => now(),
        ]);

        $this->assertIsBool($featureFlag->is_enabled);
        $this->assertIsBool($featureFlag->is_global);
        $this->assertInstanceOf(\Carbon\Carbon::class, $featureFlag->created_at);
    }

    public function test_feature_flag_fillable_attributes(): void
    {
        $featureFlag = new FeatureFlag();
        $fillable = $featureFlag->getFillable();

        $this->assertContains('name', $fillable);
        $this->assertContains('key', $fillable);
        $this->assertContains('description', $fillable);
        $this->assertContains('is_enabled', $fillable);
    }

    public function test_feature_flag_scope_enabled(): void
    {
        $enabledFlag = FeatureFlag::factory()->create(['is_enabled' => true]);
        $disabledFlag = FeatureFlag::factory()->create(['is_enabled' => false]);

        $enabledFlags = FeatureFlag::enabled()->get();

        $this->assertTrue($enabledFlags->contains($enabledFlag));
        $this->assertFalse($enabledFlags->contains($disabledFlag));
    }

    public function test_feature_flag_scope_disabled(): void
    {
        $enabledFlag = FeatureFlag::factory()->create(['is_enabled' => true]);
        $disabledFlag = FeatureFlag::factory()->create(['is_enabled' => false]);

        // Use withoutGlobalScopes to bypass the EnabledScope that filters out disabled flags
        $disabledFlags = FeatureFlag::withoutGlobalScopes()->disabled()->get();

        $this->assertFalse($disabledFlags->contains($enabledFlag));
        $this->assertTrue($disabledFlags->contains($disabledFlag));
    }

    public function test_feature_flag_scope_global(): void
    {
        $globalFlag = FeatureFlag::factory()->create(['is_global' => true]);
        $localFlag = FeatureFlag::factory()->create(['is_global' => false]);

        $globalFlags = FeatureFlag::global()->get();

        $this->assertTrue($globalFlags->contains($globalFlag));
        $this->assertFalse($globalFlags->contains($localFlag));
    }

    public function test_feature_flag_scope_by_key(): void
    {
        $flag1 = FeatureFlag::factory()->create(['key' => 'feature_1']);
        $flag2 = FeatureFlag::factory()->create(['key' => 'feature_2']);

        $feature1Flags = FeatureFlag::byKey('feature_1')->get();

        $this->assertTrue($feature1Flags->contains($flag1));
        $this->assertFalse($feature1Flags->contains($flag2));
    }

    public function test_feature_flag_can_have_users(): void
    {
        $featureFlag = FeatureFlag::factory()->create();
        $users = User::factory()->count(3)->create();

        // Create the pivot table if it doesn't exist
        if (!\Illuminate\Support\Facades\Schema::hasTable('feature_flag_users')) {
            \Illuminate\Support\Facades\Schema::create('feature_flag_users', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('feature_flag_id');
                $table->unsignedBigInteger('user_id');
                $table->timestamps();
            });
        }

        $featureFlag->users()->attach($users->pluck('id'));

        $this->assertCount(3, $featureFlag->users);
        $this->assertInstanceOf(User::class, $featureFlag->users->first());
    }

    public function test_feature_flag_can_have_rollout_percentage(): void
    {
        $featureFlag = FeatureFlag::factory()->create([
            'rollout_percentage' => 50,
        ]);

        $this->assertEquals(50, $featureFlag->rollout_percentage);
    }

    public function test_feature_flag_can_have_conditions(): void
    {
        $featureFlag = FeatureFlag::factory()->create([
            'conditions' => [
                'user_type' => 'premium',
                'country' => 'LT',
                'browser' => 'chrome',
            ],
        ]);

        $this->assertIsArray($featureFlag->conditions);
        $this->assertEquals('premium', $featureFlag->conditions['user_type']);
        $this->assertEquals('LT', $featureFlag->conditions['country']);
        $this->assertEquals('chrome', $featureFlag->conditions['browser']);
    }

    public function test_feature_flag_can_have_metadata(): void
    {
        $featureFlag = FeatureFlag::factory()->create([
            'metadata' => [
                'owner' => 'development_team',
                'priority' => 'high',
                'tags' => ['checkout', 'payment', 'ux'],
            ],
        ]);

        $this->assertIsArray($featureFlag->metadata);
        $this->assertEquals('development_team', $featureFlag->metadata['owner']);
        $this->assertEquals('high', $featureFlag->metadata['priority']);
        $this->assertIsArray($featureFlag->metadata['tags']);
    }

    public function test_feature_flag_can_have_start_date(): void
    {
        $featureFlag = FeatureFlag::factory()->create([
            'start_date' => now()->addDays(7),
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $featureFlag->start_date);
    }

    public function test_feature_flag_can_have_end_date(): void
    {
        $featureFlag = FeatureFlag::factory()->create([
            'end_date' => now()->addDays(30),
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $featureFlag->end_date);
    }

    public function test_feature_flag_can_have_environment(): void
    {
        $featureFlag = FeatureFlag::factory()->create([
            'environment' => 'production',
        ]);

        $this->assertEquals('production', $featureFlag->environment);
    }

    public function test_feature_flag_can_have_priority(): void
    {
        $featureFlag = FeatureFlag::factory()->create([
            'priority' => 'high',
        ]);

        $this->assertEquals('high', $featureFlag->priority);
    }

    public function test_feature_flag_can_have_category(): void
    {
        $featureFlag = FeatureFlag::factory()->create([
            'category' => 'checkout',
        ]);

        $this->assertEquals('checkout', $featureFlag->category);
    }

    public function test_feature_flag_can_have_impact_level(): void
    {
        $featureFlag = FeatureFlag::factory()->create([
            'impact_level' => 'high',
        ]);

        $this->assertEquals('high', $featureFlag->impact_level);
    }

    public function test_feature_flag_can_have_rollout_strategy(): void
    {
        $featureFlag = FeatureFlag::factory()->create([
            'rollout_strategy' => 'gradual',
        ]);

        $this->assertEquals('gradual', $featureFlag->rollout_strategy);
    }

    public function test_feature_flag_can_have_rollback_plan(): void
    {
        $featureFlag = FeatureFlag::factory()->create([
            'rollback_plan' => 'Disable flag and revert to old checkout flow',
        ]);

        $this->assertEquals('Disable flag and revert to old checkout flow', $featureFlag->rollback_plan);
    }

    public function test_feature_flag_can_have_success_metrics(): void
    {
        $featureFlag = FeatureFlag::factory()->create([
            'success_metrics' => [
                'conversion_rate' => 'increase',
                'checkout_time' => 'decrease',
                'user_satisfaction' => 'increase',
            ],
        ]);

        $this->assertIsArray($featureFlag->success_metrics);
        $this->assertEquals('increase', $featureFlag->success_metrics['conversion_rate']);
        $this->assertEquals('decrease', $featureFlag->success_metrics['checkout_time']);
        $this->assertEquals('increase', $featureFlag->success_metrics['user_satisfaction']);
    }

    public function test_feature_flag_can_have_approval_status(): void
    {
        $featureFlag = FeatureFlag::factory()->create([
            'approval_status' => 'approved',
        ]);

        $this->assertEquals('approved', $featureFlag->approval_status);
    }

    public function test_feature_flag_can_have_approval_notes(): void
    {
        $featureFlag = FeatureFlag::factory()->create([
            'approval_notes' => 'Approved by product team after testing',
        ]);

        $this->assertEquals('Approved by product team after testing', $featureFlag->approval_notes);
    }

    public function test_feature_flag_can_have_created_by(): void
    {
        $featureFlag = FeatureFlag::factory()->create([
            'created_by' => 'admin',
        ]);

        $this->assertEquals('admin', $featureFlag->created_by);
    }

    public function test_feature_flag_can_have_updated_by(): void
    {
        $featureFlag = FeatureFlag::factory()->create([
            'updated_by' => 'developer',
        ]);

        $this->assertEquals('developer', $featureFlag->updated_by);
    }

    public function test_feature_flag_can_have_last_activated(): void
    {
        $featureFlag = FeatureFlag::factory()->create([
            'last_activated' => now(),
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $featureFlag->last_activated);
    }

    public function test_feature_flag_can_have_last_deactivated(): void
    {
        $featureFlag = FeatureFlag::factory()->create([
            'last_deactivated' => now(),
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $featureFlag->last_deactivated);
    }
}
