<?php

declare(strict_types=1);

namespace Tests\Models;

use App\Enums\ScheduleType;
use App\Models\Campaign;
use App\Models\CampaignSchedule;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

/**
 * @internal
 */
final class CampaignScheduleTest extends TestCase
{
    use RefreshDatabase;

    public function test_fillable_fields_allow_mass_assignment(): void
    {
        // Arrange: create a campaign that the schedule can belong to.
        $campaign = Campaign::factory()->create();

        // Act: mass assign the payload so we know the fillable list is correct.
        $schedule = CampaignSchedule::create([
            'campaign_id'     => $campaign->id,
            'schedule_type'   => ScheduleType::DAILY,
            'schedule_config' => ['time' => '08:30', 'timezone' => 'UTC'],
            'next_run_at'     => now()->addDay(),
            'last_run_at'     => now()->subDay(),
            'is_active'       => true,
        ]);

        // Assert: the model persisted and the enum cast remained intact.
        $this->assertInstanceOf(CampaignSchedule::class, $schedule);
        $this->assertTrue($schedule->schedule_type instanceof ScheduleType);
        $this->assertSame($campaign->id, $schedule->campaign_id);
    }

    public function test_casts_provide_expected_types(): void
    {
        // Arrange: create a schedule with explicit values to interrogate casts.
        $schedule = CampaignSchedule::factory()->create([
            'schedule_type'   => ScheduleType::WEEKLY,
            'schedule_config' => ['time' => '12:00', 'timezone' => 'UTC'],
            'next_run_at'     => now()->addHour(),
            'last_run_at'     => now()->subHour(),
        ]);

        // Assert: the JSON field becomes an array and date fields are Carbon instances.
        $this->assertIsArray($schedule->schedule_config);
        $this->assertInstanceOf(CarbonInterface::class, $schedule->next_run_at);
        $this->assertInstanceOf(CarbonInterface::class, $schedule->last_run_at);
        $this->assertSame(ScheduleType::WEEKLY, $schedule->schedule_type);
    }

    public function test_active_scope_returns_only_active_records(): void
    {
        // Arrange: create both active and inactive schedules.
        $activeSchedule = CampaignSchedule::factory()->active()->create();
        $inactiveSchedule = CampaignSchedule::factory()->inactive()->create();

        // Act: run the scope and gather the identifiers for easy assertions.
        $identifiers = CampaignSchedule::active()
            ->pluck('id')
            ->toArray();

        // Assert: the active id appears and the inactive one does not.
        $this->assertContains($activeSchedule->id, $identifiers);
        $this->assertNotContains($inactiveSchedule->id, $identifiers);
    }

    public function test_due_for_execution_scope_filters_records_correctly(): void
    {
        // Arrange: make one due schedule, one future schedule, and one inactive schedule.
        $dueSchedule = CampaignSchedule::factory()->active()->create([
            'next_run_at' => now()->subMinute(),
        ]);
        CampaignSchedule::factory()->active()->create([
            'next_run_at' => now()->addHour(),
        ]);
        CampaignSchedule::factory()->inactive()->create([
            'next_run_at' => now()->subMinute(),
        ]);

        // Act: collect the ids returned by the scope for assertions.
        $results = CampaignSchedule::dueForExecution()->get();

        // Assert: only the due schedule is returned and the collection is countable.
        $this->assertInstanceOf(Collection::class, $results);
        $this->assertCount(1, $results);
        $this->assertTrue($results->first()->is($dueSchedule));
    }

    public function test_for_type_scope_accepts_enum_and_string_values(): void
    {
        // Ensure factories skip cascading relationships so we have deterministic counts.
        config()->set('factory.seed_campaign_relations', false);

        // Arrange: create schedules across multiple schedule types.
        $dailySchedule = CampaignSchedule::factory()->create([
            'schedule_type' => ScheduleType::DAILY->value,
        ]);
        CampaignSchedule::factory()->create([
            'schedule_type' => ScheduleType::MONTHLY->value,
        ]);

        // Act: query twice—once with the enum instance and once with a raw string.
        $fromEnum = CampaignSchedule::forType(ScheduleType::DAILY)->get();
        $fromString = CampaignSchedule::forType('daily')->get();

        // Assert: every returned record has the requested type and includes the known schedule.
        $this->assertTrue($fromEnum->every(fn (CampaignSchedule $schedule) => $schedule->schedule_type === ScheduleType::DAILY));
        $this->assertTrue($fromEnum->contains(fn (CampaignSchedule $schedule) => $schedule->is($dailySchedule)));
        $this->assertTrue($fromString->every(fn (CampaignSchedule $schedule) => $schedule->schedule_type === ScheduleType::DAILY));
        $this->assertTrue($fromString->contains(fn (CampaignSchedule $schedule) => $schedule->is($dailySchedule)));
    }

    public function test_for_type_scope_accepts_multiple_values_at_once(): void
    {
        // Disable cascading relation seeding to focus on the explicit schedules we create.
        config()->set('factory.seed_campaign_relations', false);

        // Arrange: persist one schedule per type so the scope has diverse data.
        $dailySchedule = CampaignSchedule::factory()->create([
            'schedule_type' => ScheduleType::DAILY->value,
        ]);
        $monthlySchedule = CampaignSchedule::factory()->create([
            'schedule_type' => ScheduleType::MONTHLY->value,
        ]);
        CampaignSchedule::factory()->create([
            'schedule_type' => ScheduleType::CUSTOM->value,
        ]);

        // Act: request both enum and string based filters to make sure the scope
        // merges the results correctly without duplicating ids or missing options.
        $results = CampaignSchedule::forType([
            ScheduleType::DAILY,
            'monthly',
        ])->pluck('id')->all();

        // Assert: only the matching schedule ids are returned and order is not significant.
        sort($results);
        $this->assertSame([
            $dailySchedule->id,
            $monthlySchedule->id,
        ], $results);
    }

    public function test_campaign_relationship_returns_parent_campaign(): void
    {
        // Arrange: create a campaign and attach a schedule to it.
        $campaign = Campaign::factory()->create();
        $schedule = CampaignSchedule::factory()->create([
            'campaign_id' => $campaign->id,
        ]);

        // Assert: the relationship resolves the correct parent model.
        $this->assertTrue($schedule->campaign->is($campaign));
    }
}
