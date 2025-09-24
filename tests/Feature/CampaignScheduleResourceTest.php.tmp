<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ScheduleType;
use App\Filament\Resources\CampaignScheduleResource;
use App\Models\Campaign;
use App\Models\CampaignSchedule;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class CampaignScheduleResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);
    }

    public function test_can_list_campaign_schedules(): void
    {
        $campaign = Campaign::factory()->create();

        CampaignSchedule::factory()->create([
            'campaign_id' => $campaign->id,
            'schedule_type' => ScheduleType::DAILY,
            'next_run_at' => now()->addDay(),
            'is_active' => true,
        ]);

        CampaignSchedule::factory()->create([
            'campaign_id' => $campaign->id,
            'schedule_type' => ScheduleType::WEEKLY,
            'next_run_at' => now()->addWeek(),
            'is_active' => false,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(ListRecords::class, [
            'resource' => CampaignScheduleResource::class,
        ])
            ->assertCanSeeTableRecords(CampaignSchedule::all())
            ->assertCanSeeTableColumns([
                'campaign.name',
                'schedule_type',
                'next_run_at',
                'last_run_at',
                'is_active',
                'status',
            ]);
    }

    public function test_can_create_campaign_schedule(): void
    {
        $campaign = Campaign::factory()->create();

        $this->actingAs($this->adminUser);

        Livewire::test(CreateRecord::class, [
            'resource' => CampaignScheduleResource::class,
        ])
            ->fillForm([
                'campaign_id' => $campaign->id,
                'schedule_type' => ScheduleType::DAILY,
                'next_run_at' => now()->addDay(),
                'is_active' => true,
                'schedule_config' => [
                    'time' => '09:00',
                    'timezone' => 'UTC',
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertRedirect();

        $this->assertDatabaseHas('campaign_schedules', [
            'campaign_id' => $campaign->id,
            'schedule_type' => ScheduleType::DAILY->value,
            'is_active' => true,
        ]);
    }

    public function test_can_edit_campaign_schedule(): void
    {
        $campaign = Campaign::factory()->create();
        $schedule = CampaignSchedule::factory()->create([
            'campaign_id' => $campaign->id,
            'schedule_type' => ScheduleType::DAILY,
            'is_active' => true,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(EditRecord::class, [
            'resource' => CampaignScheduleResource::class,
            'record' => $schedule->id,
        ])
            ->fillForm([
                'schedule_type' => ScheduleType::WEEKLY,
                'next_run_at' => now()->addWeek(),
                'is_active' => false,
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertRedirect();

        $schedule->refresh();
        $this->assertEquals(ScheduleType::WEEKLY, $schedule->schedule_type);
        $this->assertFalse($schedule->is_active);
    }

    public function test_can_view_campaign_schedule(): void
    {
        $campaign = Campaign::factory()->create();
        $schedule = CampaignSchedule::factory()->create([
            'campaign_id' => $campaign->id,
            'schedule_type' => ScheduleType::MONTHLY,
            'is_active' => true,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(ViewRecord::class, [
            'resource' => CampaignScheduleResource::class,
            'record' => $schedule->id,
        ])
            ->assertCanSeeTableRecords([$schedule]);
    }

    public function test_can_filter_by_campaign(): void
    {
        $campaign1 = Campaign::factory()->create(['name' => 'Campaign 1']);
        $campaign2 = Campaign::factory()->create(['name' => 'Campaign 2']);

        $schedule1 = CampaignSchedule::factory()->create([
            'campaign_id' => $campaign1->id,
        ]);

        $schedule2 = CampaignSchedule::factory()->create([
            'campaign_id' => $campaign2->id,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(ListRecords::class, [
            'resource' => CampaignScheduleResource::class,
        ])
            ->filterTable('campaign_id', $campaign1->id)
            ->assertCanSeeTableRecords([$schedule1])
            ->assertCanNotSeeTableRecords([$schedule2]);
    }

    public function test_can_filter_by_schedule_type(): void
    {
        $campaign = Campaign::factory()->create();

        $dailySchedule = CampaignSchedule::factory()->create([
            'campaign_id' => $campaign->id,
            'schedule_type' => ScheduleType::DAILY,
        ]);

        $weeklySchedule = CampaignSchedule::factory()->create([
            'campaign_id' => $campaign->id,
            'schedule_type' => ScheduleType::WEEKLY,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(ListRecords::class, [
            'resource' => CampaignScheduleResource::class,
        ])
            ->filterTable('schedule_type', ScheduleType::DAILY->value)
            ->assertCanSeeTableRecords([$dailySchedule])
            ->assertCanNotSeeTableRecords([$weeklySchedule]);
    }

    public function test_can_filter_by_active_status(): void
    {
        $campaign = Campaign::factory()->create();

        $activeSchedule = CampaignSchedule::factory()->create([
            'campaign_id' => $campaign->id,
            'is_active' => true,
        ]);

        $inactiveSchedule = CampaignSchedule::factory()->create([
            'campaign_id' => $campaign->id,
            'is_active' => false,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(ListRecords::class, [
            'resource' => CampaignScheduleResource::class,
        ])
            ->filterTable('is_active', true)
            ->assertCanSeeTableRecords([$activeSchedule])
            ->assertCanNotSeeTableRecords([$inactiveSchedule]);
    }

    public function test_can_filter_overdue_schedules(): void
    {
        $campaign = Campaign::factory()->create();

        $overdueSchedule = CampaignSchedule::factory()->create([
            'campaign_id' => $campaign->id,
            'next_run_at' => now()->subDay(),
            'is_active' => true,
        ]);

        $futureSchedule = CampaignSchedule::factory()->create([
            'campaign_id' => $campaign->id,
            'next_run_at' => now()->addDay(),
            'is_active' => true,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(ListRecords::class, [
            'resource' => CampaignScheduleResource::class,
        ])
            ->filterTable('overdue')
            ->assertCanSeeTableRecords([$overdueSchedule])
            ->assertCanNotSeeTableRecords([$futureSchedule]);
    }

    public function test_can_activate_schedule(): void
    {
        $campaign = Campaign::factory()->create();
        $schedule = CampaignSchedule::factory()->create([
            'campaign_id' => $campaign->id,
            'is_active' => false,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(ListRecords::class, [
            'resource' => CampaignScheduleResource::class,
        ])
            ->callTableAction('activate', $schedule)
            ->assertNotified();

        $schedule->refresh();
        $this->assertTrue($schedule->is_active);
    }

    public function test_can_deactivate_schedule(): void
    {
        $campaign = Campaign::factory()->create();
        $schedule = CampaignSchedule::factory()->create([
            'campaign_id' => $campaign->id,
            'is_active' => true,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(ListRecords::class, [
            'resource' => CampaignScheduleResource::class,
        ])
            ->callTableAction('deactivate', $schedule)
            ->assertNotified();

        $schedule->refresh();
        $this->assertFalse($schedule->is_active);
    }

    public function test_can_run_schedule_now(): void
    {
        $campaign = Campaign::factory()->create();
        $schedule = CampaignSchedule::factory()->create([
            'campaign_id' => $campaign->id,
            'is_active' => true,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(ListRecords::class, [
            'resource' => CampaignScheduleResource::class,
        ])
            ->callTableAction('run_now', $schedule)
            ->assertNotified();

        $schedule->refresh();
        $this->assertNotNull($schedule->last_run_at);
    }

    public function test_can_bulk_activate_schedules(): void
    {
        $campaign = Campaign::factory()->create();

        $schedule1 = CampaignSchedule::factory()->create([
            'campaign_id' => $campaign->id,
            'is_active' => false,
        ]);

        $schedule2 = CampaignSchedule::factory()->create([
            'campaign_id' => $campaign->id,
            'is_active' => false,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(ListRecords::class, [
            'resource' => CampaignScheduleResource::class,
        ])
            ->callTableBulkAction('activate_bulk', [$schedule1, $schedule2])
            ->assertNotified();

        $schedule1->refresh();
        $schedule2->refresh();
        $this->assertTrue($schedule1->is_active);
        $this->assertTrue($schedule2->is_active);
    }

    public function test_can_bulk_deactivate_schedules(): void
    {
        $campaign = Campaign::factory()->create();

        $schedule1 = CampaignSchedule::factory()->create([
            'campaign_id' => $campaign->id,
            'is_active' => true,
        ]);

        $schedule2 = CampaignSchedule::factory()->create([
            'campaign_id' => $campaign->id,
            'is_active' => true,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(ListRecords::class, [
            'resource' => CampaignScheduleResource::class,
        ])
            ->callTableBulkAction('deactivate_bulk', [$schedule1, $schedule2])
            ->assertNotified();

        $schedule1->refresh();
        $schedule2->refresh();
        $this->assertFalse($schedule1->is_active);
        $this->assertFalse($schedule2->is_active);
    }

    public function test_schedule_status_display(): void
    {
        $campaign = Campaign::factory()->create();

        $activeSchedule = CampaignSchedule::factory()->create([
            'campaign_id' => $campaign->id,
            'is_active' => true,
            'next_run_at' => now()->addDay(),
        ]);

        $inactiveSchedule = CampaignSchedule::factory()->create([
            'campaign_id' => $campaign->id,
            'is_active' => false,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(ListRecords::class, [
            'resource' => CampaignScheduleResource::class,
        ])
            ->assertCanSeeTableRecords([$activeSchedule, $inactiveSchedule]);
    }

    public function test_schedule_type_options(): void
    {
        $campaign = Campaign::factory()->create();

        foreach (ScheduleType::cases() as $type) {
            CampaignSchedule::factory()->create([
                'campaign_id' => $campaign->id,
                'schedule_type' => $type,
            ]);
        }

        $this->actingAs($this->adminUser);

        Livewire::test(ListRecords::class, [
            'resource' => CampaignScheduleResource::class,
        ])
            ->assertCanSeeTableRecords(CampaignSchedule::all());
    }

    public function test_schedule_config_storage(): void
    {
        $campaign = Campaign::factory()->create();
        $config = [
            'time' => '09:00',
            'timezone' => 'UTC',
            'days' => ['monday', 'wednesday', 'friday'],
        ];

        $this->actingAs($this->adminUser);

        Livewire::test(CreateRecord::class, [
            'resource' => CampaignScheduleResource::class,
        ])
            ->fillForm([
                'campaign_id' => $campaign->id,
                'schedule_type' => ScheduleType::WEEKLY,
                'next_run_at' => now()->addWeek(),
                'is_active' => true,
                'schedule_config' => $config,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('campaign_schedules', [
            'campaign_id' => $campaign->id,
            'schedule_type' => ScheduleType::WEEKLY->value,
            'schedule_config' => json_encode($config),
        ]);
    }

    public function test_campaign_relationship_display(): void
    {
        $campaign = Campaign::factory()->create(['name' => 'Test Campaign']);
        $schedule = CampaignSchedule::factory()->create([
            'campaign_id' => $campaign->id,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(ListRecords::class, [
            'resource' => CampaignScheduleResource::class,
        ])
            ->assertCanSeeTableRecords([$schedule]);
    }

    public function test_schedule_validation(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(CreateRecord::class, [
            'resource' => CampaignScheduleResource::class,
        ])
            ->fillForm([
                'campaign_id' => null,
                'schedule_type' => null,
                'next_run_at' => null,
            ])
            ->call('create')
            ->assertHasFormErrors(['campaign_id', 'schedule_type', 'next_run_at']);
    }

    public function test_schedule_creation_with_required_fields(): void
    {
        $campaign = Campaign::factory()->create();

        $this->actingAs($this->adminUser);

        Livewire::test(CreateRecord::class, [
            'resource' => CampaignScheduleResource::class,
        ])
            ->fillForm([
                'campaign_id' => $campaign->id,
                'schedule_type' => ScheduleType::ONCE,
                'next_run_at' => now()->addHour(),
            ])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertRedirect();

        $this->assertDatabaseHas('campaign_schedules', [
            'campaign_id' => $campaign->id,
            'schedule_type' => ScheduleType::ONCE->value,
        ]);
    }
}
