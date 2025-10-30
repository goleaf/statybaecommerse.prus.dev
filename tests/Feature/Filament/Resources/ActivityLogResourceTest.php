<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\ActivityLogResource;
use App\Filament\Resources\ActivityLogResource\Pages\ListActivityLogs;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class ActivityLogResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Resolve the Filament admin panel so Livewire components boot with the correct context.
        $this->resolveAdminPanel();

        // Create and authenticate a simple administrator to bypass panel authorization gates.
        $this->admin = User::factory()->create([
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]);

        $this->actingAs($this->admin);
    }

    public function test_list_page_displays_recent_activity_logs(): void
    {
        // Seed a deterministic activity log entry so the table has a visible record to render.
        $activityLog = ActivityLog::factory()->create([
            'log_name'    => 'system',
            'description' => 'Coverage event recorded',
        ]);

        Livewire::test(ListActivityLogs::class)
            ->call('loadTable')
            ->assertCanSeeTableRecords([$activityLog])
            ->assertSee('Coverage event recorded');
    }

    public function test_log_name_filter_limits_visible_records(): void
    {
        // Create separate activity logs so the filter has clear targets to include and exclude.
        $systemLog = ActivityLog::factory()->create([
            'log_name'    => 'system',
            'description' => 'System level activity',
        ]);

        $authLog = ActivityLog::factory()->create([
            'log_name'    => 'auth',
            'description' => 'Authentication activity',
        ]);

        Livewire::test(ListActivityLogs::class)
            ->call('loadTable')
            ->filterTable('log_name', 'system')
            ->assertCanSeeTableRecords([$systemLog])
            ->assertCanNotSeeTableRecords([$authLog]);
    }

    public function test_index_route_is_accessible(): void
    {
        // Hitting the resource index ensures the route registration for Filament v4 remains intact.
        $this
            ->get(ActivityLogResource::getUrl('index'))
            ->assertOk();
    }
}
