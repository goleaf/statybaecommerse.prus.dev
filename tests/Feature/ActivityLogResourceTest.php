<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\ActivityLogResource\Pages\ListActivityLogs;
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

    public function test_activity_log_table_displays_expected_columns(): void
    {
        Livewire::actingAs($this->adminUser)
            ->test(ListActivityLogs::class)
            ->assertTableColumnExists('id')
            ->assertTableColumnExists('log_name')
            ->assertTableColumnExists('description')
            ->assertTableColumnExists('causer.name')
            ->assertTableColumnExists('subject_type')
            ->assertTableColumnExists('event')
            ->assertTableColumnExists('created_at');
    }

    public function test_can_filter_activity_logs_by_log_name(): void
    {
        // Arrange
        $authLog = ActivityLog::factory()->create(['log_name' => 'auth']);
        $systemLog = ActivityLog::factory()->create(['log_name' => 'system']);

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ListActivityLogs::class)
            ->filterTable('log_name', 'auth')
            ->assertCanSeeTableRecords([$authLog])
            ->assertCanNotSeeTableRecords([$systemLog]);
    }

    public function test_can_filter_activity_logs_by_subject_type(): void
    {
        // Arrange
        $userLog = ActivityLog::factory()->create(['subject_type' => User::class]);
        $orderLog = ActivityLog::factory()->create(['subject_type' => 'App\\Models\\Order']);

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ListActivityLogs::class)
            ->filterTable('subject_type', User::class)
            ->assertCanSeeTableRecords([$userLog])
            ->assertCanNotSeeTableRecords([$orderLog]);
    }

    public function test_can_filter_activity_logs_by_created_at_range(): void
    {
        // Arrange
        $oldLog = ActivityLog::factory()->create(['created_at' => now()->subDays(10)]);
        $recentLog = ActivityLog::factory()->create(['created_at' => now()->subDay()]);

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ListActivityLogs::class)
            ->filterTable('created_at', [
                'range' => [
                    'start' => now()->subDays(5)->format('Y-m-d'),
                    'end' => now()->format('Y-m-d'),
                ],
            ])
            ->assertCanSeeTableRecords([$recentLog])
            ->assertCanNotSeeTableRecords([$oldLog]);
    }

    public function test_can_view_activity_log_details_modal(): void
    {
        // Arrange
        $causer = User::factory()->create(['name' => 'Test Admin']);
        $activityLog = ActivityLog::factory()->create([
            'log_name' => 'system',
            'description' => 'System configuration updated',
            'event' => 'updated',
            'causer_id' => $causer->id,
            'causer_type' => User::class,
            'subject_type' => User::class,
            'subject_id' => $causer->id,
            'properties' => ['changes' => ['setting' => 'value']],
        ]);

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ListActivityLogs::class)
            ->callTableAction('view_details', $activityLog)
            ->assertSee('System configuration updated')
            ->assertSee('Test Admin')
            ->assertSee('system');
    }
}
