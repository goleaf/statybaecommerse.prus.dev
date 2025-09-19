<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ActivityLogResourceTest extends TestCase
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
            'user_id' => auth()->id(),
            'action' => 'login',
            'description' => 'User logged in successfully',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0 (Test Browser)',
            'properties' => ['test' => 'data'],
        ];

        $activity = ActivityLog::create($activityData);

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => auth()->id(),
            'action' => 'login',
            'description' => 'User logged in successfully',
            'ip_address' => '127.0.0.1',
        ]);

        $this->assertEquals('login', $activity->action);
        $this->assertEquals('User logged in successfully', $activity->description);
        $this->assertEquals('127.0.0.1', $activity->ip_address);
    }

    public function test_can_filter_activity_log_by_action(): void
    {
        ActivityLog::factory()->create(['action' => 'login']);
        ActivityLog::factory()->create(['action' => 'logout']);

        $loginActivities = ActivityLog::where('action', 'login')->get();
        $logoutActivities = ActivityLog::where('action', 'logout')->get();

        $this->assertCount(1, $loginActivities);
        $this->assertCount(1, $logoutActivities);
        $this->assertEquals('login', $loginActivities->first()->action);
        $this->assertEquals('logout', $logoutActivities->first()->action);
    }

    public function test_can_filter_activity_log_by_user(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        ActivityLog::factory()->create(['user_id' => $user1->id]);
        ActivityLog::factory()->create(['user_id' => $user2->id]);

        $user1Activities = ActivityLog::where('user_id', $user1->id)->get();
        $user2Activities = ActivityLog::where('user_id', $user2->id)->get();

        $this->assertCount(1, $user1Activities);
        $this->assertCount(1, $user2Activities);
        $this->assertEquals($user1->id, $user1Activities->first()->user_id);
        $this->assertEquals($user2->id, $user2Activities->first()->user_id);
    }

    public function test_can_filter_activity_log_by_date(): void
    {
        ActivityLog::factory()->create([
            'action' => 'created',
            'created_at' => now(),
        ]);
        
        ActivityLog::factory()->create([
            'action' => 'updated',
            'created_at' => now()->subDays(2),
        ]);

        $todayActivities = ActivityLog::whereDate('created_at', today())->get();
        $oldActivities = ActivityLog::whereDate('created_at', '<', today())->get();

        $this->assertCount(1, $todayActivities);
        $this->assertCount(1, $oldActivities);
        $this->assertEquals('created', $todayActivities->first()->action);
        $this->assertEquals('updated', $oldActivities->first()->action);
    }

    public function test_can_get_activity_log_with_user_relationship(): void
    {
        $user = User::factory()->create();
        
        $activity = ActivityLog::factory()->create([
            'user_id' => $user->id,
            'action' => 'profile_updated',
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
        $this->assertIsArray($activity->properties);
        $this->assertArrayHasKey('old_values', $activity->properties);
        $this->assertArrayHasKey('new_values', $activity->properties);
        $this->assertArrayHasKey('changes', $activity->properties);
    }
}
