<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Widgets;

use App\Filament\Widgets\CalendarWidget;
use App\Models\Campaign;
use App\Models\Channel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(CalendarWidget::class)]
final class CalendarWidgetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolveAdminPanel();

        $user = User::factory()->admin()->create();
        $this->actingAs($user);
    }

    public function test_widget_can_be_instantiated(): void
    {
        $widget = new CalendarWidget;

        $this->assertInstanceOf(CalendarWidget::class, $widget);
    }

    public function test_widget_model_configuration(): void
    {
        $widget = new CalendarWidget;

        $this->assertEquals(Campaign::class, $widget->model);
    }

    public function test_widget_renders_successfully(): void
    {
        Livewire::test(CalendarWidget::class)
            ->assertSuccessful();
    }

    public function test_fetch_events_returns_campaigns_in_date_range(): void
    {
        $channel = Channel::factory()->create(['name' => 'Test Channel']);

        $campaign1 = Campaign::factory()->create([
            'name'       => 'Campaign 1',
            'channel_id' => $channel->id,
            'starts_at'  => Carbon::now()->startOfMonth(),
            'ends_at'    => Carbon::now()->startOfMonth()->addDays(5),
            'status'     => 'active',
        ]);

        $campaign2 = Campaign::factory()->create([
            'name'       => 'Campaign 2',
            'channel_id' => $channel->id,
            'starts_at'  => Carbon::now()->endOfMonth(),
            'ends_at'    => null,
            'status'     => 'scheduled',
        ]);

        // Campaign outside date range
        Campaign::factory()->create([
            'name'      => 'Campaign 3',
            'starts_at' => Carbon::now()->addMonths(2),
            'ends_at'   => Carbon::now()->addMonths(2)->addDays(5),
        ]);

        $widget = new CalendarWidget;

        $fetchInfo = [
            'start' => Carbon::now()->startOfMonth()->toDateTimeString(),
            'end'   => Carbon::now()->endOfMonth()->toDateTimeString(),
        ];

        $events = $widget->fetchEvents($fetchInfo);

        $this->assertCount(2, $events);

        $eventTitles = array_column($events, 'title');
        $this->assertContains('Campaign 1', $eventTitles);
        $this->assertContains('Campaign 2', $eventTitles);
    }

    public function test_fetch_events_handles_campaigns_without_end_date(): void
    {
        $campaign = Campaign::factory()->create([
            'name'      => 'Ongoing Campaign',
            'starts_at' => Carbon::now()->startOfMonth(),
            'ends_at'   => null,
            'status'    => 'active',
        ]);

        $widget = new CalendarWidget;

        $fetchInfo = [
            'start' => Carbon::now()->startOfMonth()->toDateTimeString(),
            'end'   => Carbon::now()->endOfMonth()->toDateTimeString(),
        ];

        $events = $widget->fetchEvents($fetchInfo);

        $this->assertCount(1, $events);
        $this->assertEquals('Ongoing Campaign', $events[0]['title']);
        $this->assertArrayNotHasKey('end', $events[0]);
    }

    public function test_fetch_events_applies_status_colors(): void
    {
        $campaigns = [
            ['status' => 'active', 'expected_color' => '#16a34a'],
            ['status' => 'scheduled', 'expected_color' => '#0ea5e9'],
            ['status' => 'paused', 'expected_color' => '#f59e0b'],
            ['status' => 'completed', 'expected_color' => '#6366f1'],
            ['status' => 'cancelled', 'expected_color' => '#ef4444'],
            ['status' => 'draft', 'expected_color' => null],
        ];

        foreach ($campaigns as $campaignData) {
            Campaign::factory()->create([
                'name'      => "Campaign {$campaignData['status']}",
                'starts_at' => Carbon::now()->startOfMonth(),
                'status'    => $campaignData['status'],
            ]);
        }

        $widget = new CalendarWidget;

        $fetchInfo = [
            'start' => Carbon::now()->startOfMonth()->toDateTimeString(),
            'end'   => Carbon::now()->endOfMonth()->toDateTimeString(),
        ];

        $events = $widget->fetchEvents($fetchInfo);

        foreach ($events as $event) {
            $status = $event['extendedProps']['status'];
            $expectedColor = collect($campaigns)->firstWhere('status', $status)['expected_color'];

            if ($expectedColor) {
                $this->assertEquals($expectedColor, $event['backgroundColor']);
                $this->assertEquals($expectedColor, $event['borderColor']);
            } else {
                $this->assertArrayNotHasKey('backgroundColor', $event);
                $this->assertArrayNotHasKey('borderColor', $event);
            }
        }
    }

    public function test_calendar_configuration(): void
    {
        $widget = new CalendarWidget;
        $config = $widget->config();

        $this->assertEquals('dayGridMonth', $config['initialView']);
        $this->assertEquals(1, $config['firstDay']);
        $this->assertEquals(app()->getLocale(), $config['locale']);
        $this->assertTrue($config['selectable']);
        $this->assertTrue($config['selectMirror']);
        $this->assertTrue($config['editable']);
        $this->assertTrue($config['eventResizableFromStart']);
        $this->assertTrue($config['navLinks']);
        $this->assertTrue($config['dayMaxEvents']);
    }

    public function test_form_schema_includes_required_fields(): void
    {
        $widget = new CalendarWidget;
        $schema = $widget->getFormSchema();

        $fieldNames = collect($schema)->map(fn ($field) => $field->getName())->toArray();

        $expectedFields = [
            'name',
            'slug',
            'channel_id',
            'status',
            'is_active',
            'is_featured',
            'starts_at',
            'ends_at',
        ];

        foreach ($expectedFields as $field) {
            $this->assertContains($field, $fieldNames);
        }
    }

    public function test_widget_extends_full_calendar_widget(): void
    {
        $widget = new CalendarWidget;

        $this->assertInstanceOf(\Saade\FilamentFullCalendar\Widgets\FullCalendarWidget::class, $widget);
    }

    public function test_event_tooltip_includes_campaign_details(): void
    {
        $channel = Channel::factory()->create(['name' => 'Test Channel']);

        $campaign = Campaign::factory()->create([
            'name'       => 'Test Campaign',
            'channel_id' => $channel->id,
            'starts_at'  => Carbon::parse('2024-01-15 10:00:00'),
            'ends_at'    => Carbon::parse('2024-01-20 18:00:00'),
            'status'     => 'active',
        ]);

        $widget = new CalendarWidget;

        $fetchInfo = [
            'start' => Carbon::parse('2024-01-01')->toDateTimeString(),
            'end'   => Carbon::parse('2024-01-31')->toDateTimeString(),
        ];

        $events = $widget->fetchEvents($fetchInfo);

        $this->assertCount(1, $events);

        $tooltip = $events[0]['extendedProps']['tooltip'];
        $this->assertStringContainsString('Test Campaign', $tooltip);
        $this->assertStringContainsString('2024-01-15 10:00', $tooltip);
        $this->assertStringContainsString('2024-01-20 18:00', $tooltip);
    }

    public function test_widget_handles_campaigns_without_channel(): void
    {
        $campaign = Campaign::factory()->create([
            'name'       => 'No Channel Campaign',
            'channel_id' => null,
            'starts_at'  => Carbon::now()->startOfMonth(),
            'status'     => 'active',
        ]);

        $widget = new CalendarWidget;

        $fetchInfo = [
            'start' => Carbon::now()->startOfMonth()->toDateTimeString(),
            'end'   => Carbon::now()->endOfMonth()->toDateTimeString(),
        ];

        $events = $widget->fetchEvents($fetchInfo);

        $this->assertCount(1, $events);
        $this->assertEquals('No Channel Campaign', $events[0]['title']);
        $this->assertNull($events[0]['extendedProps']['channel']);
    }
}
