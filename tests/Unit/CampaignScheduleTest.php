<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Campaign;
use App\Models\CampaignSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CampaignScheduleTest extends TestCase
{
    use RefreshDatabase;

    public function test_campaign_schedule_can_be_created(): void
    {
        $campaign = Campaign::factory()->create();
        
        $schedule = CampaignSchedule::factory()->create([
            'campaign_id' => $campaign->id,
            'schedule_type' => 'daily',
            'schedule_config' => ['time' => '09:00', 'timezone' => 'UTC'],
            'next_run_at' => now()->addDay(),
            'last_run_at' => now()->subDay(),
            'is_active' => true,
        ]);

        $this->assertInstanceOf(CampaignSchedule::class, $schedule);
        $this->assertEquals($campaign->id, $schedule->campaign_id);
        $this->assertEquals('daily', $schedule->schedule_type);
        $this->assertIsArray($schedule->schedule_config);
        $this->assertEquals('09:00', $schedule->schedule_config['time']);
        $this->assertEquals('UTC', $schedule->schedule_config['timezone']);
        $this->assertInstanceOf(\Carbon\Carbon::class, $schedule->next_run_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $schedule->last_run_at);
        $this->assertTrue($schedule->is_active);
    }

    public function test_campaign_schedule_fillable_attributes(): void
    {
        $schedule = new CampaignSchedule();
        $fillable = $schedule->getFillable();

        $expectedFillable = [
            'campaign_id',
            'schedule_type',
            'schedule_config',
            'next_run_at',
            'last_run_at',
            'is_active',
        ];

        foreach ($expectedFillable as $field) {
            $this->assertContains($field, $fillable);
        }
    }

    public function test_campaign_schedule_casts(): void
    {
        $schedule = CampaignSchedule::factory()->create([
            'schedule_config' => ['test' => 'data'],
            'next_run_at' => '2024-01-01 12:00:00',
            'last_run_at' => '2024-01-01 10:00:00',
            'is_active' => true,
        ]);

        $this->assertIsArray($schedule->schedule_config);
        $this->assertInstanceOf(\Carbon\Carbon::class, $schedule->next_run_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $schedule->last_run_at);
        $this->assertTrue($schedule->is_active);
    }

    public function test_campaign_schedule_belongs_to_campaign(): void
    {
        $campaign = Campaign::factory()->create();
        $schedule = CampaignSchedule::factory()->create(['campaign_id' => $campaign->id]);

        $this->assertInstanceOf(Campaign::class, $schedule->campaign);
        $this->assertEquals($campaign->id, $schedule->campaign->id);
    }

    public function test_campaign_schedule_scope_by_schedule_type(): void
    {
        $dailySchedule = CampaignSchedule::factory()->create(['schedule_type' => 'daily']);
        $weeklySchedule = CampaignSchedule::factory()->create(['schedule_type' => 'weekly']);

        $dailySchedules = CampaignSchedule::withoutGlobalScopes()->byScheduleType('daily')->get();
        $this->assertTrue($dailySchedules->contains($dailySchedule));
        $this->assertFalse($dailySchedules->contains($weeklySchedule));
    }

    public function test_campaign_schedule_scope_by_campaign(): void
    {
        $campaign1 = Campaign::factory()->create();
        $campaign2 = Campaign::factory()->create();
        
        $schedule1 = CampaignSchedule::factory()->create(['campaign_id' => $campaign1->id]);
        $schedule2 = CampaignSchedule::factory()->create(['campaign_id' => $campaign2->id]);

        $campaign1Schedules = CampaignSchedule::withoutGlobalScopes()->byCampaign($campaign1->id)->get();
        $this->assertTrue($campaign1Schedules->contains($schedule1));
        $this->assertFalse($campaign1Schedules->contains($schedule2));
    }

    public function test_campaign_schedule_scope_active(): void
    {
        $activeSchedule = CampaignSchedule::factory()->create(['is_active' => true]);
        $inactiveSchedule = CampaignSchedule::factory()->create(['is_active' => false]);

        $activeSchedules = CampaignSchedule::withoutGlobalScopes()->active()->get();
        $this->assertTrue($activeSchedules->contains($activeSchedule));
        $this->assertFalse($activeSchedules->contains($inactiveSchedule));
    }

    public function test_campaign_schedule_scope_inactive(): void
    {
        $activeSchedule = CampaignSchedule::factory()->create(['is_active' => true]);
        $inactiveSchedule = CampaignSchedule::factory()->create(['is_active' => false]);

        $inactiveSchedules = CampaignSchedule::withoutGlobalScopes()->inactive()->get();
        $this->assertFalse($inactiveSchedules->contains($activeSchedule));
        $this->assertTrue($inactiveSchedules->contains($inactiveSchedule));
    }

    public function test_campaign_schedule_scope_due_for_execution(): void
    {
        $dueSchedule = CampaignSchedule::factory()->create(['next_run_at' => now()->subHour()]);
        $notDueSchedule = CampaignSchedule::factory()->create(['next_run_at' => now()->addHour()]);

        $dueSchedules = CampaignSchedule::withoutGlobalScopes()->dueForExecution()->get();
        $this->assertTrue($dueSchedules->contains($dueSchedule));
        $this->assertFalse($dueSchedules->contains($notDueSchedule));
    }

    public function test_campaign_schedule_scope_recently_executed(): void
    {
        $recentSchedule = CampaignSchedule::factory()->create(['last_run_at' => now()->subHour()]);
        $oldSchedule = CampaignSchedule::factory()->create(['last_run_at' => now()->subDays(10)]);

        $recentSchedules = CampaignSchedule::withoutGlobalScopes()->recentlyExecuted()->get();
        $this->assertTrue($recentSchedules->contains($recentSchedule));
        $this->assertFalse($recentSchedules->contains($oldSchedule));
    }

    public function test_campaign_schedule_scope_never_executed(): void
    {
        $neverExecutedSchedule = CampaignSchedule::factory()->create(['last_run_at' => null]);
        $executedSchedule = CampaignSchedule::factory()->create(['last_run_at' => now()->subHour()]);

        $neverExecutedSchedules = CampaignSchedule::withoutGlobalScopes()->neverExecuted()->get();
        $this->assertTrue($neverExecutedSchedules->contains($neverExecutedSchedule));
        $this->assertFalse($neverExecutedSchedules->contains($executedSchedule));
    }

    public function test_campaign_schedule_is_due_method(): void
    {
        $dueSchedule = CampaignSchedule::factory()->create(['next_run_at' => now()->subHour()]);
        $notDueSchedule = CampaignSchedule::factory()->create(['next_run_at' => now()->addHour()]);

        $this->assertTrue($dueSchedule->isDue());
        $this->assertFalse($notDueSchedule->isDue());
    }

    public function test_campaign_schedule_is_active_method(): void
    {
        $activeSchedule = CampaignSchedule::factory()->create(['is_active' => true]);
        $inactiveSchedule = CampaignSchedule::factory()->create(['is_active' => false]);

        $this->assertTrue($activeSchedule->isActive());
        $this->assertFalse($inactiveSchedule->isActive());
    }

    public function test_campaign_schedule_has_been_executed_method(): void
    {
        $executedSchedule = CampaignSchedule::factory()->create(['last_run_at' => now()->subHour()]);
        $neverExecutedSchedule = CampaignSchedule::factory()->create(['last_run_at' => null]);

        $this->assertTrue($executedSchedule->hasBeenExecuted());
        $this->assertFalse($neverExecutedSchedule->hasBeenExecuted());
    }

    public function test_campaign_schedule_get_schedule_type_label_method(): void
    {
        $dailySchedule = CampaignSchedule::factory()->create(['schedule_type' => 'daily']);
        $weeklySchedule = CampaignSchedule::factory()->create(['schedule_type' => 'weekly']);
        $monthlySchedule = CampaignSchedule::factory()->create(['schedule_type' => 'monthly']);

        $this->assertEquals('Daily', $dailySchedule->getScheduleTypeLabel());
        $this->assertEquals('Weekly', $weeklySchedule->getScheduleTypeLabel());
        $this->assertEquals('Monthly', $monthlySchedule->getScheduleTypeLabel());
    }

    public function test_campaign_schedule_get_next_execution_time_method(): void
    {
        $schedule = CampaignSchedule::factory()->create(['next_run_at' => now()->addDay()]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $schedule->getNextExecutionTime());
        $this->assertEquals($schedule->next_run_at, $schedule->getNextExecutionTime());
    }

    public function test_campaign_schedule_get_last_execution_time_method(): void
    {
        $schedule = CampaignSchedule::factory()->create(['last_run_at' => now()->subHour()]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $schedule->getLastExecutionTime());
        $this->assertEquals($schedule->last_run_at, $schedule->getLastExecutionTime());
    }

    public function test_campaign_schedule_mark_as_executed_method(): void
    {
        $schedule = CampaignSchedule::factory()->create([
            'last_run_at' => null,
            'next_run_at' => now()->addDay(),
        ]);

        $schedule->markAsExecuted();

        $this->assertNotNull($schedule->last_run_at);
        $this->assertTrue($schedule->hasBeenExecuted());
    }

    public function test_campaign_schedule_table_name(): void
    {
        $schedule = new CampaignSchedule();
        $this->assertEquals('campaign_schedules', $schedule->getTable());
    }

    public function test_campaign_schedule_factory(): void
    {
        $schedule = CampaignSchedule::factory()->create();

        $this->assertInstanceOf(CampaignSchedule::class, $schedule);
        $this->assertNotEmpty($schedule->campaign_id);
        $this->assertNotEmpty($schedule->schedule_type);
        $this->assertIsArray($schedule->schedule_config);
        $this->assertInstanceOf(\Carbon\Carbon::class, $schedule->next_run_at);
        $this->assertIsBool($schedule->is_active);
    }
}
