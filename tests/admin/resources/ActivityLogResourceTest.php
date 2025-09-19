<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_can_create_activity_log(): void
    {
        $activityData = [
            'log_name' => 'auth',
            'description' => 'User logged in successfully',
            'causer_type' => User::class,
            'causer_id' => auth()->id(),
            'properties' => ['test' => 'data'],
        ];

        $activity = ActivityLog::create($activityData);

        $this->assertDatabaseHas('activity_log', [
            'causer_id' => auth()->id(),
            'log_name' => 'auth',
            'description' => 'User logged in successfully',
        ]);

        $this->assertEquals('auth', $activity->log_name);
        $this->assertEquals('User logged in successfully', $activity->description);
    }

    public function test_can_filter_activity_log_by_action(): void
    {
        ActivityLog::factory()->create(['log_name' => 'auth', 'description' => 'User logged in']);
        ActivityLog::factory()->create(['log_name' => 'auth', 'description' => 'User logged out']);

        $loginActivities = ActivityLog::where('description', 'like', '%logged in%')->get();
        $logoutActivities = ActivityLog::where('description', 'like', '%logged out%')->get();

        $this->assertCount(1, $loginActivities);
        $this->assertCount(1, $logoutActivities);
    }

    public function test_can_filter_activity_log_by_user(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        ActivityLog::factory()->create(['causer_id' => $user1->id]);
        ActivityLog::factory()->create(['causer_id' => $user2->id]);

        $user1Activities = ActivityLog::where('causer_id', $user1->id)->get();
        $user2Activities = ActivityLog::where('causer_id', $user2->id)->get();

        $this->assertCount(1, $user1Activities);
        $this->assertCount(1, $user2Activities);
    }

    public function test_can_filter_activity_log_by_date(): void
    {
        $activity = ActivityLog::factory()->create([
            'log_name' => 'default',
            'description' => 'Activity created today',
            'created_at' => now(),
        ]);

        $todayActivities = ActivityLog::whereDate('created_at', today())->get();
        $this->assertGreaterThanOrEqual(1, $todayActivities->count());
        $this->assertTrue($todayActivities->contains($activity));
    }

    public function test_can_get_activity_log_with_user_relationship(): void
    {
        $user = User::factory()->create();

        $activity = ActivityLog::factory()->create([
            'causer_id' => $user->id,
            'causer_type' => User::class,
            'description' => 'Profile updated',
        ]);

        $this->assertInstanceOf(User::class, $activity->user);
        $this->assertEquals($user->id, $activity->user->id);
    }

    public function test_can_store_properties_as_json(): void
    {
        $properties = [
            'old_values' => ['name' => 'Old Name'],
            'new_values' => ['name' => 'New Name'],
            'changes' => ['name' => ['Old Name', 'New Name']],
        ];

        $activity = ActivityLog::factory()->create([
            'properties' => $properties,
        ]);

        $this->assertEquals($properties, $activity->properties);
    }
}
