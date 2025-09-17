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

    public function test_campaign_schedule_basic_functionality(): void
    {
        $campaign = Campaign::factory()->create();
        $schedule = CampaignSchedule::factory()->create([
            'campaign_id' => $campaign->id,
            'schedule_type' => 'daily',
            'is_active' => true,
        ]);

        // Test basic model functionality
        $this->assertInstanceOf(CampaignSchedule::class, $schedule);
        $this->assertEquals($campaign->id, $schedule->campaign_id);
        $this->assertEquals('daily', $schedule->schedule_type);
        $this->assertTrue($schedule->is_active);
    }

    public function test_campaign_schedule_schedule_config_access(): void
    {
        $schedule = CampaignSchedule::factory()->create([
            'schedule_config' => [
                'time' => '09:00',
                'timezone' => 'UTC',
                'days' => ['monday', 'tuesday'],
            ],
        ]);

        $this->assertIsArray($schedule->schedule_config);
        $this->assertEquals('09:00', $schedule->schedule_config['time']);
        $this->assertEquals('UTC', $schedule->schedule_config['timezone']);
        $this->assertEquals(['monday', 'tuesday'], $schedule->schedule_config['days']);
    }

    public function test_campaign_schedule_datetime_fields(): void
    {
        $nextRun = now()->addDay();
        $lastRun = now()->subHour();
        
        $schedule = CampaignSchedule::factory()->create([
            'next_run_at' => $nextRun,
            'last_run_at' => $lastRun,
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $schedule->next_run_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $schedule->last_run_at);
        $this->assertEquals($nextRun->format('Y-m-d H:i:s'), $schedule->next_run_at->format('Y-m-d H:i:s'));
        $this->assertEquals($lastRun->format('Y-m-d H:i:s'), $schedule->last_run_at->format('Y-m-d H:i:s'));
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
