<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\ActivityLogResource\Pages\CreateActivityLog;
use App\Filament\Resources\ActivityLogResource\Pages\EditActivityLog;
use App\Filament\Resources\ActivityLogResource\Pages\ListActivityLogs;
use App\Filament\Resources\ActivityLogResource\Pages\ViewActivityLog;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class ActivityLogResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test user for authentication
        $this->adminUser = User::factory()->create([
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);
    }

    public function test_can_list_activity_logs(): void
    {
        // Arrange
        $activityLogs = ActivityLog::factory()->count(5)->create();

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ListActivityLogs::class)
            ->assertCanSeeTableRecords($activityLogs);
    }

    public function test_can_create_activity_log(): void
    {
        // Arrange
        $user = User::factory()->create();
        $activityLogData = [
            'log_name' => 'test_log',
            'description' => 'Test activity log',
            'event' => 'created',
            'user_id' => $user->id,
            'is_important' => true,
            'is_system' => false,
            'severity' => 'medium',
            'category' => 'test',
        ];

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(CreateActivityLog::class)
            ->fillForm($activityLogData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'test_log',
            'description' => 'Test activity log',
            'event' => 'created',
            'causer_id' => $user->id,
        ]);
    }

    public function test_can_edit_activity_log(): void
    {
        // Arrange
        $activityLog = ActivityLog::factory()->create();
        $newDescription = 'Updated description';

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(EditActivityLog::class, ['record' => $activityLog->id])
            ->fillForm(['description' => $newDescription])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('activity_log', [
            'id' => $activityLog->id,
            'description' => $newDescription,
        ]);
    }

    public function test_can_view_activity_log(): void
    {
        // Arrange
        $activityLog = ActivityLog::factory()->create();

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ViewActivityLog::class, ['record' => $activityLog->id])
            ->assertSee($activityLog->description);
    }

    public function test_can_filter_activity_logs_by_event(): void
    {
        // Arrange
        $createdLogs = ActivityLog::factory()->count(3)->create(['event' => 'created']);
        $updatedLogs = ActivityLog::factory()->count(2)->create(['event' => 'updated']);

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ListActivityLogs::class)
            ->filterTable('event', 'created')
            ->assertCanSeeTableRecords($createdLogs)
            ->assertCanNotSeeTableRecords($updatedLogs);
    }

    public function test_can_filter_activity_logs_by_importance(): void
    {
        // Arrange
        $importantLogs = ActivityLog::factory()->count(3)->create(['is_important' => true]);
        $normalLogs = ActivityLog::factory()->count(2)->create(['is_important' => false]);

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ListActivityLogs::class)
            ->filterTable('is_important', '1')
            ->assertCanSeeTableRecords($importantLogs)
            ->assertCanNotSeeTableRecords($normalLogs);
    }

    public function test_can_mark_activity_log_as_important(): void
    {
        // Arrange
        $activityLog = ActivityLog::factory()->create(['is_important' => false]);

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ListActivityLogs::class)
            ->callTableAction('mark_important', $activityLog)
            ->assertNotified();

        $this->assertDatabaseHas('activity_log', [
            'id' => $activityLog->id,
            'is_important' => true,
        ]);
    }

    public function test_can_bulk_mark_activity_logs_as_important(): void
    {
        // Arrange
        $activityLogs = ActivityLog::factory()->count(3)->create(['is_important' => false]);

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ListActivityLogs::class)
            ->callTableBulkAction('mark_important', $activityLogs)
            ->assertNotified();

        foreach ($activityLogs as $activityLog) {
            $this->assertDatabaseHas('activity_log', [
                'id' => $activityLog->id,
                'is_important' => true,
            ]);
        }
    }

    public function test_can_search_activity_logs(): void
    {
        // Arrange
        $searchableLog = ActivityLog::factory()->create(['description' => 'Unique search term']);
        $otherLogs = ActivityLog::factory()->count(3)->create();

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ListActivityLogs::class)
            ->searchTable('Unique search term')
            ->assertCanSeeTableRecords([$searchableLog])
            ->assertCanNotSeeTableRecords($otherLogs);
    }

    public function test_can_sort_activity_logs_by_created_at(): void
    {
        // Arrange
        $oldLog = ActivityLog::factory()->create(['created_at' => now()->subDay()]);
        $newLog = ActivityLog::factory()->create(['created_at' => now()]);

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ListActivityLogs::class)
            ->sortTable('created_at', 'desc')
            ->assertCanSeeTableRecords([$newLog, $oldLog], inOrder: true);
    }

    public function test_validates_required_fields_on_create(): void
    {
        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(CreateActivityLog::class)
            ->fillForm([])
            ->call('create')
            ->assertHasFormErrors(['description']);
    }

    public function test_can_export_activity_logs(): void
    {
        // Arrange
        ActivityLog::factory()->count(5)->create();

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ListActivityLogs::class)
            ->callTableBulkAction('export')
            ->assertNotified();
    }

    public function test_can_delete_activity_log(): void
    {
        // Arrange
        $activityLog = ActivityLog::factory()->create();

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ListActivityLogs::class)
            ->callTableAction('delete', $activityLog)
            ->assertNotified();

        $this->assertDatabaseMissing('activity_log', ['id' => $activityLog->id]);
    }

    public function test_can_bulk_delete_activity_logs(): void
    {
        // Arrange
        $activityLogs = ActivityLog::factory()->count(3)->create();

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ListActivityLogs::class)
            ->callTableBulkAction('delete', $activityLogs)
            ->assertNotified();

        foreach ($activityLogs as $activityLog) {
            $this->assertDatabaseMissing('activity_log', ['id' => $activityLog->id]);
        }
    }
}
