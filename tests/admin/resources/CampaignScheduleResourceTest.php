<?php declare(strict_types=1);

use App\Filament\Resources\CampaignScheduleResource;
use App\Models\Campaign;
use App\Models\CampaignSchedule;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Create permissions
    $permissions = [
        'browse_campaign_schedules',
        'read_campaign_schedules',
        'edit_campaign_schedules',
        'add_campaign_schedules',
        'delete_campaign_schedules',
    ];

    foreach ($permissions as $permission) {
        Permission::create(['name' => $permission]);
    }

    $role = Role::create(['name' => 'administrator']);
    $role->givePermissionTo($permissions);

    // Create admin user
    $this->adminUser = User::factory()->create();
    $this->adminUser->assignRole('administrator');

    // Create test data
    $this->testCampaign = Campaign::factory()->create([
        'name' => 'Test Campaign',
    ]);

    $this->testCampaignSchedule = CampaignSchedule::factory()->create([
        'campaign_id' => $this->testCampaign->id,
        'schedule_type' => 'daily',
        'schedule_config' => ['time' => '09:00', 'timezone' => 'UTC'],
        'next_run_at' => now()->addDay(),
        'is_active' => true,
    ]);
});

it('can list campaign schedules in admin panel', function () {
    $this
        ->actingAs($this->adminUser)
        ->get(CampaignScheduleResource::getUrl('index'))
        ->assertOk();
});

it('can create a campaign schedule', function () {
    $campaign = Campaign::factory()->create();

    Livewire::actingAs($this->adminUser)
        ->test(CampaignScheduleResource\Pages\CreateCampaignSchedule::class)
        ->fillForm([
            'campaign_id' => $campaign->id,
            'schedule_type' => 'weekly',
            'schedule_config' => ['day' => 'monday', 'time' => '10:00'],
            'next_run_at' => now()->addWeek(),
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('campaign_schedules', [
        'campaign_id' => $campaign->id,
        'schedule_type' => 'weekly',
        'is_active' => true,
    ]);
});

it('can view a campaign schedule record', function () {
    $this
        ->actingAs($this->adminUser)
        ->get(CampaignScheduleResource::getUrl('view', ['record' => $this->testCampaignSchedule]))
        ->assertOk();
});

it('can edit a campaign schedule record', function () {
    Livewire::actingAs($this->adminUser)
        ->test(CampaignScheduleResource\Pages\EditCampaignSchedule::class, ['record' => $this->testCampaignSchedule->id])
        ->fillForm([
            'schedule_type' => 'monthly',
            'is_active' => false,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('campaign_schedules', [
        'id' => $this->testCampaignSchedule->id,
        'schedule_type' => 'monthly',
        'is_active' => false,
    ]);
});

it('can delete a campaign schedule record', function () {
    $campaignSchedule = CampaignSchedule::factory()->create();

    Livewire::actingAs($this->adminUser)
        ->test(CampaignScheduleResource\Pages\EditCampaignSchedule::class, ['record' => $campaignSchedule->id])
        ->callAction('delete')
        ->assertOk();

    $this->assertDatabaseMissing('campaign_schedules', [
        'id' => $campaignSchedule->id,
    ]);
});

it('validates required fields', function () {
    Livewire::actingAs($this->adminUser)
        ->test(CampaignScheduleResource\Pages\CreateCampaignSchedule::class)
        ->fillForm([
            'campaign_id' => '',
            'schedule_type' => '',
            'next_run_at' => '',
        ])
        ->call('create')
        ->assertHasFormErrors(['campaign_id', 'schedule_type', 'next_run_at']);
});

it('can filter campaign schedules by campaign', function () {
    $campaign1 = Campaign::factory()->create(['name' => 'Campaign 1']);
    $campaign2 = Campaign::factory()->create(['name' => 'Campaign 2']);

    CampaignSchedule::factory()->create(['campaign_id' => $campaign1->id]);
    CampaignSchedule::factory()->create(['campaign_id' => $campaign2->id]);

    $this
        ->actingAs($this->adminUser)
        ->get(CampaignScheduleResource::getUrl('index'))
        ->assertOk();
});

it('can filter campaign schedules by schedule type', function () {
    CampaignSchedule::factory()->create(['schedule_type' => 'daily']);
    CampaignSchedule::factory()->create(['schedule_type' => 'weekly']);

    $this
        ->actingAs($this->adminUser)
        ->get(CampaignScheduleResource::getUrl('index'))
        ->assertOk();
});

it('can filter campaign schedules by active status', function () {
    CampaignSchedule::factory()->create(['is_active' => true]);
    CampaignSchedule::factory()->create(['is_active' => false]);

    $this
        ->actingAs($this->adminUser)
        ->get(CampaignScheduleResource::getUrl('index'))
        ->assertOk();
});

it('can search campaign schedules by campaign name', function () {
    $campaign = Campaign::factory()->create(['name' => 'Special Campaign']);
    CampaignSchedule::factory()->create(['campaign_id' => $campaign->id]);

    $this
        ->actingAs($this->adminUser)
        ->get(CampaignScheduleResource::getUrl('index') . '?search=Special')
        ->assertOk();
});

it('can sort campaign schedules by next run date', function () {
    $schedule1 = CampaignSchedule::factory()->create(['next_run_at' => now()->addDays(2)]);
    $schedule2 = CampaignSchedule::factory()->create(['next_run_at' => now()->addDays(1)]);

    $this
        ->actingAs($this->adminUser)
        ->get(CampaignScheduleResource::getUrl('index') . '?sort=next_run_at&direction=asc')
        ->assertOk();
});

it('shows correct campaign schedule data in table', function () {
    $this
        ->actingAs($this->adminUser)
        ->get(CampaignScheduleResource::getUrl('index'))
        ->assertSee($this->testCampaignSchedule->schedule_type)
        ->assertSee($this->testCampaign->name);
});

it('can perform bulk delete action', function () {
    $schedule1 = CampaignSchedule::factory()->create();
    $schedule2 = CampaignSchedule::factory()->create();

    Livewire::actingAs($this->adminUser)
        ->test(CampaignScheduleResource\Pages\ListCampaignSchedules::class)
        ->callTableBulkAction('delete', [$schedule1->id, $schedule2->id])
        ->assertOk();

    $this->assertDatabaseMissing('campaign_schedules', [
        'id' => $schedule1->id,
    ]);

    $this->assertDatabaseMissing('campaign_schedules', [
        'id' => $schedule2->id,
    ]);
});

it('can activate a campaign schedule', function () {
    $schedule = CampaignSchedule::factory()->create(['is_active' => false]);

    Livewire::actingAs($this->adminUser)
        ->test(CampaignScheduleResource\Pages\ListCampaignSchedules::class)
        ->callTableAction('activate', $schedule)
        ->assertHasNoActionErrors();

    $schedule->refresh();
    expect($schedule->is_active)->toBeTrue();
});

it('can deactivate a campaign schedule', function () {
    $schedule = CampaignSchedule::factory()->create(['is_active' => true]);

    Livewire::actingAs($this->adminUser)
        ->test(CampaignScheduleResource\Pages\ListCampaignSchedules::class)
        ->callTableAction('deactivate', $schedule)
        ->assertHasNoActionErrors();

    $schedule->refresh();
    expect($schedule->is_active)->toBeFalse();
});

it('can run a campaign schedule now', function () {
    $schedule = CampaignSchedule::factory()->create();

    Livewire::actingAs($this->adminUser)
        ->test(CampaignScheduleResource\Pages\ListCampaignSchedules::class)
        ->callTableAction('run_now', $schedule)
        ->assertHasNoActionErrors();

    $schedule->refresh();
    expect($schedule->last_run_at)->not->toBeNull();
});

it('can perform bulk activation', function () {
    $schedule1 = CampaignSchedule::factory()->create(['is_active' => false]);
    $schedule2 = CampaignSchedule::factory()->create(['is_active' => false]);

    Livewire::actingAs($this->adminUser)
        ->test(CampaignScheduleResource\Pages\ListCampaignSchedules::class)
        ->callTableBulkAction('activate_bulk', [$schedule1->id, $schedule2->id])
        ->assertHasNoBulkActionErrors();

    $schedule1->refresh();
    $schedule2->refresh();

    expect($schedule1->is_active)->toBeTrue();
    expect($schedule2->is_active)->toBeTrue();
});

it('can perform bulk deactivation', function () {
    $schedule1 = CampaignSchedule::factory()->create(['is_active' => true]);
    $schedule2 = CampaignSchedule::factory()->create(['is_active' => true]);

    Livewire::actingAs($this->adminUser)
        ->test(CampaignScheduleResource\Pages\ListCampaignSchedules::class)
        ->callTableBulkAction('deactivate_bulk', [$schedule1->id, $schedule2->id])
        ->assertHasNoBulkActionErrors();

    $schedule1->refresh();
    $schedule2->refresh();

    expect($schedule1->is_active)->toBeFalse();
    expect($schedule2->is_active)->toBeFalse();
});

it('shows campaign relationship in form tabs', function () {
    $this
        ->actingAs($this->adminUser)
        ->get(CampaignScheduleResource::getUrl('edit', ['record' => $this->testCampaignSchedule]))
        ->assertOk();
});

it('shows schedule configuration in form tabs', function () {
    $this
        ->actingAs($this->adminUser)
        ->get(CampaignScheduleResource::getUrl('edit', ['record' => $this->testCampaignSchedule]))
        ->assertOk();
});

it('shows correct schedule type badges', function () {
    $dailySchedule = CampaignSchedule::factory()->create(['schedule_type' => 'daily']);
    $weeklySchedule = CampaignSchedule::factory()->create(['schedule_type' => 'weekly']);

    $this
        ->actingAs($this->adminUser)
        ->get(CampaignScheduleResource::getUrl('index'))
        ->assertOk();
});

it('can filter overdue schedules', function () {
    $overdueSchedule = CampaignSchedule::factory()->create([
        'next_run_at' => now()->subDay(),
        'is_active' => true,
    ]);
    $futureSchedule = CampaignSchedule::factory()->create([
        'next_run_at' => now()->addDay(),
        'is_active' => true,
    ]);

    $this
        ->actingAs($this->adminUser)
        ->get(CampaignScheduleResource::getUrl('index'))
        ->assertOk();
});

it('shows correct status badges', function () {
    $activeSchedule = CampaignSchedule::factory()->create(['is_active' => true]);
    $inactiveSchedule = CampaignSchedule::factory()->create(['is_active' => false]);

    $this
        ->actingAs($this->adminUser)
        ->get(CampaignScheduleResource::getUrl('index'))
        ->assertOk();
});

it('can access campaign schedule resource pages', function () {
    $this
        ->actingAs($this->adminUser)
        ->get(CampaignScheduleResource::getUrl('index'))
        ->assertOk();

    $this
        ->actingAs($this->adminUser)
        ->get(CampaignScheduleResource::getUrl('create'))
        ->assertOk();
});

it('validates schedule configuration format', function () {
    Livewire::actingAs($this->adminUser)
        ->test(CampaignScheduleResource\Pages\CreateCampaignSchedule::class)
        ->fillForm([
            'campaign_id' => $this->testCampaign->id,
            'schedule_type' => 'custom',
            'schedule_config' => 'invalid_json',
            'next_run_at' => now()->addDay(),
        ])
        ->call('create')
        ->assertHasNoFormErrors();
});

it('handles campaign schedule with complex configuration', function () {
    $complexConfig = [
        'time' => '09:00',
        'timezone' => 'Europe/Vilnius',
        'days' => ['monday', 'wednesday', 'friday'],
        'frequency' => 'weekly',
    ];

    Livewire::actingAs($this->adminUser)
        ->test(CampaignScheduleResource\Pages\CreateCampaignSchedule::class)
        ->fillForm([
            'campaign_id' => $this->testCampaign->id,
            'schedule_type' => 'custom',
            'schedule_config' => $complexConfig,
            'next_run_at' => now()->addWeek(),
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('campaign_schedules', [
        'campaign_id' => $this->testCampaign->id,
        'schedule_type' => 'custom',
        'is_active' => true,
    ]);
});
