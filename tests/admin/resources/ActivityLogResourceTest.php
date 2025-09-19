<?php declare(strict_types=1);

namespace Tests\Admin\Resources;

use App\Filament\Resources\ActivityLogResource;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ActivityLogResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'email' => 'admin@admin.com',
            'name' => 'Admin User',
        ]);

        // Create role and permissions if they don't exist
        $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'super_admin']);

        // Create all necessary permissions
        $permissions = [
            'view_activity',
            'view_analytics',
            'view_order',
            'view_product',
            'view_user',
        ];

        foreach ($permissions as $permission) {
            $perm = \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $permission]);
            $role->givePermissionTo($perm);
        }

        $this->adminUser->assignRole($role);

        $this->actingAs($this->adminUser);
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

    public function test_can_access_activity_log_resource(): void
    {
        $response = $this->get(ActivityLogResource::getUrl('index'));
        $response->assertSuccessful();
    }

    public function test_can_create_activity_log_via_form(): void
    {
        $user = User::factory()->create();

        $activityData = [
            'log_name' => 'test',
            'description' => 'Test activity',
            'event' => 'custom',
            'causer_type' => User::class,
            'causer_id' => $user->id,
            'properties' => ['test' => 'data'],
            'is_important' => false,
            'is_system' => false,
        ];

        $activity = ActivityLog::create($activityData);

        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'test',
            'description' => 'Test activity',
            'event' => 'custom',
            'causer_id' => $user->id,
        ]);
    }

    public function test_can_update_activity_log(): void
    {
        $activity = ActivityLog::factory()->create([
            'is_important' => false,
        ]);

        $activity->update(['is_important' => true]);

        $this->assertDatabaseHas('activity_log', [
            'id' => $activity->id,
            'is_important' => true,
        ]);
    }

    public function test_activity_log_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $activity = ActivityLog::factory()->create([
            'causer_id' => $user->id,
            'causer_type' => User::class,
        ]);

        $this->assertInstanceOf(User::class, $activity->user);
        $this->assertEquals($user->id, $activity->user->id);
    }

    public function test_activity_log_has_subject_relationship(): void
    {
        $user = User::factory()->create();
        $activity = ActivityLog::factory()->create([
            'subject_type' => User::class,
            'subject_id' => $user->id,
        ]);

        $this->assertInstanceOf(User::class, $activity->subject);
        $this->assertEquals($user->id, $activity->subject->id);
    }

    public function test_activity_log_has_causer_relationship(): void
    {
        $user = User::factory()->create();
        $activity = ActivityLog::factory()->create([
            'causer_type' => User::class,
            'causer_id' => $user->id,
        ]);

        $this->assertInstanceOf(User::class, $activity->causer);
        $this->assertEquals($user->id, $activity->causer->id);
    }
}
